<?php

namespace app\Service\Upload;

use function ceil;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_file;
use function is_readable;
use function serialize;
use function time;
use function unlink;
use function unserialize;

class BlockMeta
{
    /**
     * @var string
     */
    protected $filename;
    /**
     * @var int
     */
    protected $filesize;
    /**
     * @var string
     */
    protected $filehash;
    /**
     * @var int
     */
    protected $chunkSize = 0;
    /**
     * @var int
     */
    protected $chunkTotal = 0;
    /**
     * @var int
     */
    protected $chunkCount = 0;
    /**
     * @var int
     */
    protected $uploadSize = 0;
    /**
     * @var int
     */
    protected $createTime = 0;
    /**
     * @var int
     */
    protected $updateTime = 0;

    public static function create(
        string $filename,
        int $filesize,
        string $filehash,
        int $chunkSize
    ) {
        $meta = new BlockMeta();
        $meta->filename = $filename;
        $meta->filesize = $filesize;
        $meta->filehash = $filehash;
        $meta->chunkSize = $chunkSize;
        $meta->chunkTotal = (int) ceil($filesize / $chunkSize);
        $meta->createTime = time();
        return $meta;
    }

    public static function load(string $dirname): ?BlockMeta
    {
        $filename = $dirname . '_meta.data';
        if (!is_file($filename) || !is_readable($filename)) {
            return null;
        }
        $data = file_get_contents($dirname . '_meta.data');
        if (empty($data)) {
            return null;
        }
        /** @var BlockMeta $meta */
        $meta = unserialize($data);
        if (!$meta instanceof BlockMeta) {
            return null;
        }
        return $meta;
    }

    public function __construct()
    {
    }

    public function save(string $dirname): bool
    {
        $this->updateTime = time();
        return file_put_contents($dirname . '_meta.data', serialize($this)) > 0;
    }

    public function destroy(string $dirname): bool
    {
        $filename = $dirname . '_meta.data';
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    public function isFirstChunk(): bool
    {
        return $this->chunkCount === 0;
    }

    public function isLastChunk(int $count): bool
    {
        return $this->chunkTotal === $count;
    }

    public function isDone(): bool
    {
        return $this->chunkTotal === $this->chunkCount;
    }

    public function verifyChunk(int $count): bool
    {
        return $this->chunkCount + 1 === $count;
    }

    public function receiveChunk(int $count, int $chunkSize): void
    {
        $this->chunkCount = $count;
        $this->uploadSize += $chunkSize;
    }

    public function getSurplusSize(): int
    {
        return $this->filesize - $this->getUploadSize();
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return int
     */
    public function getFilesize(): int
    {
        return $this->filesize;
    }

    /**
     * @return string
     */
    public function getFilehash(): string
    {
        return $this->filehash;
    }

    /**
     * @return int
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * @return int
     */
    public function getChunkTotal(): int
    {
        return $this->chunkTotal;
    }

    /**
     * @return int
     */
    public function getChunkCount(): int
    {
        return $this->chunkCount;
    }

    /**
     * @return int
     */
    public function getUploadSize(): int
    {
        return $this->uploadSize;
    }
}
