<?php
/**
 * Created by PhpStorm.
 * User: 123
 * Date: 2018/11/29
 * Time: 16:59
 */

namespace app\controller;

use app\Logic\Attachment;
use app\Service\Auth\Facade\Auth;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\File;
use think\file\UploadedFile;
use think\Response;
use function func\reply\reply_bad;
use function func\reply\reply_succeed;

class Upload extends Base
{
    /**
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function image()
    {
        $field = $this->request->param('field');
        $files = $this->request->file($field);
        if (is_array($files)) {
            return reply_bad(CODE_COM_PARAM, '无法处理提交');
        }
        if ($files instanceof File) {
            if (($result = $this->uploadImage($files)) instanceof Response) {
                return $result;
            }
            return reply_succeed($result);
        } else {
            return reply_bad(CODE_COM_PARAM, '无法处理提交');
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
        /** @var File[] $files */
        $files = $this->request->file();
        if (!is_array($files)) {
            return reply_bad(CODE_COM_PARAM, '无法处理提交');
        }
        $returnData = [];
        foreach ($files as $key => $file) {
            if (false === ($file instanceof File)) {
                return reply_bad(CODE_COM_PARAM, '无法处理提交');
            }
            if (($imageInfo = $this->uploadImage($file)) instanceof Response) {
                return $imageInfo;
            }
            $returnData[$key] = $imageInfo;
        }
        return reply_succeed($returnData);
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
        if (false === $annex = $attachment->uploadImage($file, Auth::user())) {
            return reply_bad(CODE_COM_UNABLE_PROCESS, $attachment->getErrorMessage());
        }
        return [
            'path' => "{$annex->path}#{$annex->driver}",
            'real_path' => $annex->real_path,
        ];
    }
}
