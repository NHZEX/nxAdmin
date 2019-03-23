<?php
/**
 * Created by PhpStorm.
 * User: 123
 * Date: 2018/11/29
 * Time: 16:59
 */
namespace app\controller;

use app\common\traits\ShowReturn;
use app\logic\Attachment;
use facade\WebConv;
use think\File;

class Upload extends AdminBase
{
    use ShowReturn;

    /**
     * @throws \think\exception\DbException
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
     * @return \think\Response
     * @throws \think\exception\DbException
     */
    public function images()
    {
        $files = $this->request->file();
        if (!is_array($files)) {
            return self::showMsg(CODE_COM_PARAM, '无法处理提交');
        }
        /**
         * @var  $file File
         */
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
     * @return array|\think\Response
     * @throws \think\exception\DbException
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
