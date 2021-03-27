<?php

declare(strict_types=1);

namespace app\Service\Upload;

use finfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function hash;
use function random_bytes;
use function filesize;
use function fseek;
use function stream_copy_to_stream;
use function is_file;
use function rename;
use function unlink;
use function rmdir;
use function fopen;
use function flock;
use function hash_init;
use function hash_update_stream;
use function hash_final;
use function fclose;
use function file_exists;
use function mkdir;
use function substr;
use function str_replace;
use function pathinfo;
use function in_array;
use function explode;
use function rtrim;
use function strlen;
use function ctype_xdigit;
use function array_reduce;

class ChunkUpload
{
    /** @var string  */
    private static $storageDir;
    /** @var string  */
    private static $temporaryDir;
    /**
     * @var string
     */
    private $workdir;

    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string|null
     */
    private $storageName;

    /** @var BlockMeta */
    private $meta;

    /** @var finfo|null  */
    private $__fInfo;
    /** @var string  */
    private $fileMime;
    /** @var string  */
    private $fileExt;
    /** @var bool  */
    private $isMove = false;

    public static function setDirs(string $storageDir, string $temporaryDir)
    {
        self::$storageDir = rtrim($storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        self::$temporaryDir = rtrim($temporaryDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    public static function prepare(string $filename, int $filesize, string $fileHash): ChunkUpload
    {
        $upload = new self(null);
        $upload->build($filename, $filesize, $fileHash);
        $upload->saveMeta();

        return $upload;
    }

    public static function query(string $token): ?ChunkUpload
    {
        $upload = new self($token);
        $upload->loadMeta(false);
        if (null === $upload->meta) {
            return null;
        }

        return $upload;
    }

    public static function block(string $token, int $count, SplFileInfo $fileInfo): ChunkUpload
    {
        $upload = new self($token);
        $upload->append($count, $fileInfo);
        $upload->saveMeta();

        return $upload;
    }

    public function __construct(?string $token)
    {
        if (empty(self::$storageDir) || empty(self::$temporaryDir)) {
            throw new UploadException('dirs is not initialized');
        }
        $this->token = $token;
    }

    protected function build(string $filename, int $filesize, string $filehash): void
    {
        if (40 !== strlen($filehash) || !ctype_xdigit($filehash)) {
            throw new UploadException('文件Hash错误');
        }
        $chunkSize = $this->getChunkSize($filesize);
        $this->token = hash('md5', random_bytes(16));
        $this->meta = BlockMeta::create($filename, $filesize, $filehash, $chunkSize);
    }

    protected function loadMeta($failOrThrow = true): void
    {
        $this->meta = BlockMeta::load($this->getDirname());
        if ($failOrThrow && null === $this->meta) {
            throw new UploadException('无效的Meta内容');
        }
    }

    protected function append(int $count, SplFileInfo $fileInfo): bool
    {
        $this->loadMeta();
        if ($this->meta->isDone()) {
            throw new UploadException('传输已经完成');
        }
        if (!$this->meta->verifyChunk($count)) {
            throw new UploadException("预期块顺序错误：{$count}");
        }
        if ($this->meta->isLastChunk($count)) {
            if ($fileInfo->getSize() !== $this->meta->getSurplusSize()) {
                throw new UploadException("预期块大小错误：{$fileInfo->getSize()} != {$this->meta->getSurplusSize()}");
            }
        } elseif ($fileInfo->getSize() !== $this->meta->getChunkSize()) {
            throw new UploadException("预期块大小错误：{$fileInfo->getSize()} != {$this->meta->getChunkSize()}");
        }
        $filename = $this->getFilename();
        if ($this->meta->isFirstChunk()) {
            if (file_exists($filename)) {
                unlink($filename);
            }
            $fp = fopen($filename, 'w');
            if (false === flock($fp, LOCK_EX)) {
                throw new UploadException('无法锁定文件流');
            }

            $orp = fopen($fileInfo->getPathname(), 'r');
            if (false == stream_copy_to_stream($orp, $fp)) {
                throw new UploadException('复制流预期错误');
            }
            fclose($orp);

            // 预分配空间 有问题
            // fseek($fp, $this->meta->getFilesize() - 1,SEEK_SET);
            // fwrite($fp,"\0");

            fclose($fp);
        } elseif (file_exists($filename)) {
            if ($this->meta->getUploadSize() !== filesize($filename)) {
                throw new UploadException("文件大小与预期不一致: {$this->meta->getUploadSize()} != " . filesize($filename));
            }
            $fp = fopen($filename, 'a');
            if (false === flock($fp, LOCK_EX)) {
                throw new UploadException('无法锁定文件流');
            }

            fseek($fp, $this->meta->getUploadSize());

            $orp = fopen($fileInfo->getPathname(), 'r');
            if (false == stream_copy_to_stream($orp, $fp)) {
                throw new UploadException('复制流预期错误');
            }

            fclose($orp);
            fclose($fp);
        } else {
            return false;
        }
        $this->meta->receiveChunk($count, $fileInfo->getSize());
        return true;
    }

    public function isReady(): bool
    {
        return $this->meta->isDone();
    }

    public function move(): bool
    {
        if (!$this->isReady()) {
            return false;
        }
        $this->checkHash();

        if (!is_file($this->getFilename())) {
            throw new UploadException('源文件不存在：' . $this->getFilename());
        }
        $saveFilename = $this->getStorageFilename();
        if (file_exists($saveFilename)) {
            // throw new UploadException('目标文件已经存在：' . $saveFilename);
            // 暂时静默覆盖
            unlink($saveFilename);
        }
        rename($this->getFilename(), $saveFilename);
        $this->isMove = true;
        $this->destroy();
        return true;
    }

    public function destroy(): bool
    {
        $this->meta->destroy($this->getDirname());
        // 清理全部文件
        $it = new RecursiveDirectoryIterator($this->getDirname(), RecursiveDirectoryIterator::SKIP_DOTS);
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->getDirname());
        return true;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return BlockMeta
     */
    public function getMeta(): BlockMeta
    {
        return $this->meta;
    }

    public function saveMeta(): void
    {
        $this->meta->save($this->getDirname());
    }

    public function getChunkSize(int $filesize): int
    {
        $chunkSizes = [
            512 * 1024 * 1024 => 16 * 1024 * 1024,
            128 * 1024 * 1024 => 8 * 1024 * 1024,
            64 * 1024 * 1024 => 4 * 1024 * 1024,
            32 * 1024 * 1024 => 2 * 1024 * 1024,
            0 * 1024 * 1024 => 1 * 1024 * 1024,
        ];
        $sizeMbytes = $filesize;
        foreach ($chunkSizes as $maxSize => $chunkSize) {
            if ($sizeMbytes > $maxSize) {
                return $chunkSize;
            }
        }
        return 0;
    }

    protected function checkHash(): bool
    {
        $chunkSize = 1024 * 1024 * 4;

        $fp = fopen($this->getFilename(), 'rb');
        try {
            if (!flock($fp, LOCK_EX)) {
                throw new UploadException('无法锁定文件流');
            }
            $hctx = hash_init('sha1');
            while (true) {
                if (hash_update_stream($hctx, $fp, $chunkSize) < $chunkSize) {
                    break;
                }
            }
            $hash = hash_final($hctx);
            if ($this->meta->getFilehash() !== $hash) {
                throw new UploadException("文件效验失败：{$this->meta->getFilehash()} != {$hash}");
            }
        } finally {
            fclose($fp);
        }
        return true;
    }

    protected function getDirname(): string
    {
        if (null === $this->workdir) {
            $this->workdir = ChunkUpload::$temporaryDir
                . substr($this->token, 0, 2)
                . DIRECTORY_SEPARATOR
                . substr($this->token, 2)
                . DIRECTORY_SEPARATOR;
            if (!file_exists($this->workdir)) {
                mkdir($this->workdir, 0755, true);
            }
        }
        return $this->workdir;
    }

    protected function getFilename(): string
    {
        return $this->getDirname() . 'content.bin';
    }

    protected function getStorageDirname($absolute = true): string
    {
        return ($absolute ? ChunkUpload::$storageDir : DIRECTORY_SEPARATOR)
            . 'files'
            . DIRECTORY_SEPARATOR;
    }

    protected function getStorageFilename($absolute = true): string
    {
        if (null === $this->storageName) {
            $hash = $this->meta->getFilehash();
            $dir = substr($hash, 0, 2);
            $ext = $this->getFileExtension();
            if ($absolute && !file_exists($realDir = $this->getStorageDirname($absolute) . $dir)) {
                mkdir($realDir, 0755, true);
            }
            $this->storageName = $dir . DIRECTORY_SEPARATOR . substr($hash, 2) . '.' . $ext;
        }
        return $this->getStorageDirname($absolute) . $this->storageName;
    }

    public function getUrlpath()
    {
        $filename = $this->getStorageFilename(false);
        $filename = str_replace(['/', '\\'], '/', $filename);
        return $filename;
    }

    protected function getAccessibleFilePath(): string
    {
        return $this->isMove ? $this->getStorageFilename() : $this->getFilename();
    }

    protected function getFileInfo(): finfo
    {
        if (empty($this->__fInfo)) {
            $this->__fInfo = new finfo();
        }

        return $this->__fInfo;
    }

    public function verify(?array $allowMimes, ?array $allowExts): bool
    {
        if (null !== $allowMimes) {
            $mime = $this->getFileMime();
            $any = array_reduce($allowMimes, function ($carry, $val) use ($mime) {
                return $carry || str_starts_with($val, $mime);
            }, false);
            if (!$any) {
                throw new UploadException("unsupported file type: {$mime}");
            }
        }
        if (null !== $allowExts) {
            $ext = $this->getFileExtension();
            if (in_array($this->getFileExtension(), $allowExts)) {
                throw new UploadException("unsupported file ext: {$ext}");
            }
        }
        return true;
    }

    public function getFileMime(): string
    {
        if (empty($this->fileMime)) {
            $filepath = $this->getAccessibleFilePath();
            $this->fileMime = $this->getFileInfo()->file($filepath, FILEINFO_MIME) ?: '';
            if ($this->fileMime) {
                $this->fileMime = explode(';', $this->fileMime)[0];
            }
        }
        return $this->fileMime;
    }

    public function getFileExtension()
    {
        if (!empty($this->fileExt)) {
            return $this->fileExt;
        }

        $filepath = $this->getAccessibleFilePath();
        $filename = $this->meta->getFilename();

        $unsafeExtension = pathinfo($filename, PATHINFO_EXTENSION);
        if (empty($unsafeExtension)) {
            $unsafeExtension = 'bin';
        }

        $mime = $this->getFileMime();
        if (!empty($mime)) {
            $extensions = $this->getFileInfo()->file($filepath, FILEINFO_EXTENSION);
            if ('???' !== $extensions && false !== $extensions) {
                if (in_array($unsafeExtension, explode('/', $extensions))) {
                    $extension = $unsafeExtension;
                } else {
                    $extension = 'bin';
                }
            }
        }
        // todo apache mime https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
        return $this->fileExt = $extension ?? $unsafeExtension;
    }
}
