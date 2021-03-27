<?php

namespace app\Service\Upload;

;

use Psr\Http\Message\RequestInterface;
use app\Service\Upload\ChunkUpload as UploadService;

class UploadHelper
{
    /**
     * @var RequestInterface|null
     */
    protected $request;

    /**
     * @var ChunkUpload|null
     */
    protected $upload;

    /** @var int  */
    protected $allowSize = 0;
    /** @var array|null  */
    protected $allowMimes = null;

    /** @var int  */
    protected $dynamicCheckBytes = 1 * 1024 * 1024;

    public function __construct(?RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param int $allowSize
     */
    public function setAllowSize(int $allowSize): void
    {
        $this->allowSize = $allowSize;
    }

    /**
     * @param array|null $allowMimes
     */
    public function setAllowMimes(?array $allowMimes): void
    {
        $this->allowMimes = $allowMimes;
    }

    /**
     * @param string $mode
     * @param array $params
     * @return array|null
     */
    public function process(string $mode, array $params): ?array
    {
        switch ($mode) {
            case 'prepare':
                $upload = UploadService::prepare(
                    $params['filename'],
                    $params['filesize'],
                    $params['filehash'],
                );
                if ($params['filesize'] > $this->allowSize) {
                    throw new UploadException("file size not allowed: {$params['filesize']} > {$this->allowSize}");
                }
                $result = [
                    'token' => $upload->getToken(),
                    'chunkSize' => $upload->getMeta()->getChunkSize(),
                    'chunkTotal' => $upload->getMeta()->getChunkTotal(),
                ];
                break;
            case 'query':
                $upload = UploadService::query(
                    $params['token'],
                );
                if (null === $upload) {
                    return null;
                }
                $result = [
                    'chunkCount' => $upload->getMeta()->getChunkCount(),
                    'chunkTotal' => $upload->getMeta()->getChunkTotal(),
                    'totalSize' => $upload->getMeta()->getFilesize(),
                    'uploadSize' => $upload->getMeta()->getUploadSize(),
                ];
                break;
            case 'chunk':
                $upload = UploadService::block(
                    $params['token'],
                    $params['count'],
                    $params['block']
                );
                if ($upload->getMeta()->getUploadSize() >= $this->dynamicCheckBytes) {
                    $upload->verify($this->allowMimes, null);
                }
                $result = [
                    'chunkCount' => $upload->getMeta()->getChunkCount(),
                    'chunkTotal' => $upload->getMeta()->getChunkTotal(),
                    'totalSize' => $upload->getMeta()->getFilesize(),
                    'uploadSize' => $upload->getMeta()->getUploadSize(),
                ];
                if ($upload->isReady()) {
                    $upload->verify($this->allowMimes, null);

                    if ($upload->move() && null !== $this->request) {
                        $uri = $this->request->getUri();
                        $host = $uri->getHost();
                        if ($uri->getPort()) {
                            $host .= ":{$uri->getPort()}";
                        }
                        $result['fileUrl'] = '//' . $host . $upload->getUrlpath();
                    }
                }
                break;
        }
        if (isset($upload)) {
            $this->upload = $upload;
        }
        return $result ?? null;
    }

    /**
     * @return ChunkUpload|null
     */
    public function getUpload(): ?ChunkUpload
    {
        return $this->upload;
    }
}
