<?php
/**
 * Created by PhpStorm.
 * User: 123
 * Date: 2018/11/29
 * Time: 16:59
 */

namespace app\controller;

use app\common\Traits\ShowReturn;
use app\Facade\WebConv;
use app\Logic\Attachment;
use think\exception\DbException;
use think\File;
use think\Response;

class Upload extends AdminBase
{
    use ShowReturn;

    /**
     * @return Response
     * @throws DbException
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
     * User: Johnson
     * @return Response
     * @throws DbException
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
     * User: Johnson
     * @param File $file
     * @return array|Response
     * @throws DbException
     */
    private function uploadImage(File $file)
    {
        $attachment = new Attachment();
        if (false === $annex = $attachment->uploadImage($file, WebConv::getAdminUser())) {
            return self::showMsg(CODE_COM_UNABLE_PROCESS, $attachment->getErrorMessage());
        }
        return [
            'path' => "{$annex->path}#{$annex->driver}",
            'real_path' => $annex->real_path,
        ];
    }
}