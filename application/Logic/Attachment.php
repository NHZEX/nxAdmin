<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/11/26
 * Time: 10:39
 */

namespace app\Logic;

use app\Exception\BusinessResult as BusinessResultSuccess;
use app\Model\AdminUser as AdminUserModel;
use app\Model\Attachment as AttachmentModel;
use finfo;
use think\exception\DbException;
use think\facade\Lang;
use think\File;

/**
 * Class Attachment
 * @package app\common\logic
 */
class Attachment extends Base
{
    const IMAGE_DIR = 'images' . DIRECTORY_SEPARATOR;

    private $image_type = [];

    /**
     * @param File $file
     * @param AdminUserModel|null $user
     * @return false|AttachmentModel
     * @throws DbException
     */
    public function uploadImage(File $file, ?AdminUserModel $user)
    {
        try {
            // 替代 thinkphp file 验证
            if (!$file->checkSize(4 * 1024 * 1024)) {
                throw new BusinessResultSuccess(Lang::get('filesize not match'));
            }
            if (!preg_match('/image\/.*/', $file->getMime())) {
                throw new BusinessResultSuccess(Lang::get('mimetype to upload is not allowed'));
            }
            $this->getFileMime($file->getPathname());
            // 生成唯一文件名
            $uniqueFileName = $this->buildUniqueFileName($file);
            // 查找附件是否存在
            if (!$annex = AttachmentModel::findFile($uniqueFileName)) {
                $fileExt = substr($uniqueFileName, strrpos($uniqueFileName, '.') + 1);
                // 生成保存文件名
                $saveFileName = $this->buildSaveFileName($uniqueFileName);
                // 创建附件记录
                $annex = AttachmentModel::createRecord(
                    $uniqueFileName,
                    $user ? $user->id : 0,
                    self::IMAGE_DIR . $saveFileName,
                    $file->getMime(),
                    $fileExt,
                    $file->getSize(),
                    $file->hash('sha1'),
                    $file->getInfo('name')
                );
                // 存储上传文件
                if (false === $newFile = $file->move(UPLOAD_STORAGE_PATH . self::IMAGE_DIR, $saveFileName)) {
                    throw new BusinessResultSuccess($newFile->getError());
                }
            }
        } catch (BusinessResultSuccess $success) {
            $this->errorMessage = $success->getMessage();
            return false;
        }
        return $annex;
    }

    /**
     * 生成唯一文件名
     * @param File $file
     * @return string
     */
    public function buildUniqueFileName(File $file)
    {
        //
        $tmpFileName = $file->getPathname();
        $name = $file->hash('sha1');
        $name .= '.' . str_pad(dechex($file->getSize()), 8, '0', STR_PAD_LEFT);
        $name .= '.' . $this->getImageType($tmpFileName, true);
        return $name;
    }

    /**
     * 生成保存文件名
     * @param string $name
     * @return string
     */
    public function buildSaveFileName(string $name)
    {
        $savePath = substr($name, 0, 2) . DIRECTORY_SEPARATOR . substr($name, 2);
        $savePath = date('Ymd') . DIRECTORY_SEPARATOR . $savePath;
        return $savePath;
    }

    /**
     * 获取文件类型信息
     * @access public
     * @param string $filename
     * @return string
     */
    public function getFileMime(string $filename)
    {
        $finfo = new finfo();
        $result = $finfo->file($filename, FILEINFO_MIME_TYPE);
        return $result;
    }

    /**
     * 获取文件后缀
     * php>=7.2可用
     * @param string $filename
     * @return string
     */
    public function getFileExtrnsion(string $filename)
    {
        $finfo = new finfo();
        $result = $finfo->file($filename, FILEINFO_EXTENSION);
        return $result;
    }

    /**
     * 提取图像类型
     * @access protected
     * @param string $image 图片名称
     * @param bool $to_ext 获取后缀
     * @return false|int|string
     */
    protected function getImageType($image, $to_ext = false)
    {
        if (isset($this->image_type[$image])) {
            $img_type = $this->image_type[$image];
        } else {
            if (function_exists('exif_imagetype_1')) {
                $img_type = exif_imagetype($image);
            } else {
                if (is_array($info = getimagesize($image)) && isset($info[2])) {
                    $img_type = $info[2];
                } else {
                    $img_type = false;
                }
            }
            $this->image_type[$image] = $img_type;
        }

        if ($to_ext) {
            return image_type_to_extension($img_type, false);
        } else {
            return $img_type;
        }
    }
}
