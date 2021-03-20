<?php

namespace app\controller;

use app\Logic\Attachment;
use app\Service\Auth\AuthHelper;
use app\Service\Upload\Upload as UploadService;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\file\UploadedFile;
use think\Response;
use Util\Reply;
use function is_array;

class Upload extends Base
{
    public function file()
    {
        $params = $this->request->param();
        switch ($params['mode'] ?? null) {
            case 'prepare':
                $upload = UploadService::prepare(
                    $params['filename'],
                    $params['filesize'],
                    $params['filehash'],
                );
                return Reply::success([
                    'token' => $upload->getToken(),
                    'chunkSize' => $upload->getMeta()->getChunkSize(),
                    'chunkTotal' => $upload->getMeta()->getChunkTotal(),
                ]);
            case 'query':
                $upload = UploadService::query(
                    $params['token'],
                );
                if (null === $upload) {
                    return Reply::notFound();
                }
                return Reply::success([
                    'chunkCount' => $upload->getMeta()->getChunkCount(),
                    'chunkTotal' => $upload->getMeta()->getChunkTotal(),
                    'totalSize' => $upload->getMeta()->getFilesize(),
                    'uploadSize' => $upload->getMeta()->getUploadSize(),
                ]);
            case 'chunk':
                $upload = UploadService::block(
                    $params['token'],
                    $params['count'],
                    $this->request->file('block')
                );
                $result = [
                    'chunkCount' => $upload->getMeta()->getChunkCount(),
                    'chunkTotal' => $upload->getMeta()->getChunkTotal(),
                    'totalSize' => $upload->getMeta()->getFilesize(),
                    'uploadSize' => $upload->getMeta()->getUploadSize(),
                ];
                if ($upload->move()) {
                    $result['fileUrl'] = '//' . $this->request->host() . $upload->getUrlpath();
                }
                return Reply::success($result);
            default:
                return Reply::bad();
        }
    }

    /**
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function image()
    {
        $field = $this->request->param('field', 'file');
        $files = $this->request->file($field);
        if (is_array($files)) {
            return Reply::bad(CODE_COM_PARAM, '无法处理提交');
        }
        if ($files instanceof UploadedFile) {
            if (($result = $this->uploadImage($files)) instanceof Response) {
                return $result;
            }
            return Reply::success($result);
        } else {
            return Reply::bad(CODE_COM_PARAM, '无法处理提交');
        }
    }

    /**
     * 上传多个图片
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function images()
    {
        /** @var UploadedFile[] $files */
        $files = $this->request->file();
        if (!is_array($files)) {
            return Reply::bad(CODE_COM_PARAM, '无法处理提交');
        }
        $returnData = [];
        foreach ($files as $key => $file) {
            if (!($file instanceof UploadedFile)) {
                return Reply::bad(CODE_COM_PARAM, '无法处理提交');
            }
            if (($imageInfo = $this->uploadImage($file)) instanceof Response) {
                return $imageInfo;
            }
            $returnData[$key] = $imageInfo;
        }
        return Reply::success($returnData);
    }

    /**
     * @param UploadedFile $file
     * @return array|Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    private function uploadImage(UploadedFile $file)
    {
        $attachment = new Attachment();
        if (false === $annex = $attachment->uploadImage($file, AuthHelper::user())) {
            return Reply::bad(CODE_COM_UNABLE_PROCESS, $attachment->getErrorMessage());
        }
        return [
            'path' => "{$annex->path}#{$annex->driver}",
            'real_path' => $annex->real_path,
        ];
    }
}
