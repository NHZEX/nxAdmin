<?php
/**
 * Created by PhpStorm.
 * User: 123
 * Date: 2018/11/29
 * Time: 16:59
 */

namespace app\controller;

use app\Facade\WebConv;
use app\Logic\Attachment;
use app\Traits\ShowReturn;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\File;
use think\file\UploadedFile;
use think\Response;

class Upload extends AdminBase
{
    use ShowReturn;

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
            return self::showMsg(CODE_COM_PARAM, '无法处理提交');
        }
        if ($files instanceof File) {
            return self::showData(
                CODE_SUCCEED,
                $this->uploadImage($files)
            );
        } else {
            return self::showMsg(CODE_COM_PARAM);
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
            return self::showMsg(CODE_COM_PARAM, '无法处理提交');
        }
        $returnData = [];
        foreach ($files as $key => $file) {
            if (false === ($file instanceof File)) {
                return self::showMsg(CODE_COM_PARAM, '无法处理提交');
            }
            $imageInfo = $this->uploadImage($file);
            $returnData[$key] = $imageInfo;
        }
        return self::showData(CODE_SUCCEED, $returnData);
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
        if (false === $annex = $attachment->uploadImage($file, WebConv::getConvUser())) {
            return self::showMsg(CODE_COM_UNABLE_PROCESS, $attachment->getErrorMessage());
        }
        return [
            'path' => "{$annex->path}#{$annex->driver}",
            'real_path' => $annex->real_path,
        ];
    }
}
