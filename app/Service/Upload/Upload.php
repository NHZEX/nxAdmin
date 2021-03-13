<?php

declare(strict_types=1);

namespace app\Service\Upload;

use SplFileInfo;

class Upload
{
    /**
     * @var string
     */
    private $workdir;

    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string
     */
    private $storageDir;

    /** @var BlockMeta */
    private $meta;

    public static function prepare(string $filename, int $filesize, string $fileHash)
    {
        $upload = new self(null);
        $upload->build($filename, $filesize, $fileHash);
        $upload->saveMeta();

        return $upload;
    }

    public static function block(string $token, int $count, SplFileInfo $fileInfo)
    {
        $upload = new self($token);
        $upload->append($count, $fileInfo);
        $upload->saveMeta();

        return $upload;
    }

    public function __construct(?string $token)
    {
        $this->token = $token;
        $this->workdir = runtime_path('upload');
    }

    protected function build(string $filename, int $filesize, string $filehash)
    {
        // todo 验证hash长度40
        $chunkSize = $this->getChunkSize($filesize);
        $this->token = hash('md5', random_bytes(16));
        $this->meta = BlockMeta::create($filename, $filesize, $filehash, $chunkSize);
    }

    protected function append(int $count, SplFileInfo $fileInfo)
    {
        $this->meta = BlockMeta::load($this->getDirname());
        if (!$this->meta->verifyChunk($count)) {
            return false;
        }
        $filename = $this->getFilename();
        if ($this->meta->isFirstChunk()) {
            if (file_exists($filename)) {
                unlink($filename);
            }
            $fp = fopen($filename, 'w');
            var_dump($fileInfo->getPathname());
            $orp = fopen($fileInfo->getPathname(), 'a');
            var_dump(stream_copy_to_stream($orp, $fp, $this->meta->getChunkSize(), 0));

            fseek($fp, $this->meta->getFilesize() - 1, SEEK_CUR);
            fwrite($fp, "\0");

            fclose($fp);
        } elseif (file_exists($filename)) {
            // todo
        } else {
            return false;
        }
        $this->meta->receiveChunk($count);
        return true;
    }

    public function done()
    {
    }

    public function destroy()
    {
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

    public function saveMeta()
    {
        $this->meta->save($this->getDirname());
    }

    public function getChunkSize(int $filesize): int
    {
        $chunkSizes = [
            1024 * 1024 * 1024 => 16 * 1024 * 1024,
            128 * 1024 * 1024 => 8 * 1024 * 1024,
            0 * 1024 * 1024 => 4 * 1024 * 1024,
        ];
        $sizeMbytes = $filesize;
        foreach ($chunkSizes as $maxSize => $chunkSize) {
            if ($sizeMbytes > $maxSize) {
                return $chunkSize;
            }
        }
        return 0;
    }

    protected function getDirname(): string
    {
        if ($this->storageDir === null) {
            $this->storageDir = $this->workdir
                . substr($this->token, 0, 2)
                . DIRECTORY_SEPARATOR
                . substr($this->token, 2)
                . DIRECTORY_SEPARATOR;
            if (!file_exists($this->storageDir)) {
                mkdir($this->storageDir, 0755, true);
            }
        }
        return $this->storageDir;
    }

    protected function getFilename(): string
    {
        return $this->getDirname() . 'content.bin';
    }
}
