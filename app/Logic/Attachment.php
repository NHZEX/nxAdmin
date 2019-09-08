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
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\FileException;
use think\facade\Filesystem;
use think\facade\Lang;
use think\File;
use think\file\UploadedFile;

/**
 * Class Attachment
 * @package app\common\logic
 */
class Attachment extends Base
{
    const PREFIX_IMAGE = 'images';

    private $image_type = [];

    /**
     * @param UploadedFile        $file
     * @param AdminUserModel|null $user
     * @return false|AttachmentModel
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function uploadImage(UploadedFile $file, ?AdminUserModel $user)
    {
        try {
            // 替代 thinkphp file 验证
            if ($file->getSize() > (4 * 1024 * 1024)) {
                throw new BusinessResultSuccess(Lang::get('filesize not match'));
            }
            if (!preg_match('/image\/.*/', $file->getMime())) {
                throw new BusinessResultSuccess(Lang::get('mimetype to upload is not allowed'));
            }
            $this->getFileMime($file->getPathname());
            // 初始化文件存储
            $upload = Filesystem::disk('upload');
            // 生成唯一文件名
            $uniqueFileName = $this->buildUniqueFileName($file);
            // 查找附件记录是否存在
            if (!$annex = AttachmentModel::findFile($uniqueFileName)) {
                $fileExt = substr($uniqueFileName, strrpos($uniqueFileName, '.') + 1);
                // 生成保存名称
                $saveFileName = $this->buildSaveFileName($uniqueFileName);
                // 创建附件记录
                $annex = AttachmentModel::createRecord(
                    $uniqueFileName,
                    $user ? $user->id : 0,
                    self::PREFIX_IMAGE . DIRECTORY_SEPARATOR . $saveFileName,
                    $file->getMime(),
                    $fileExt,
                    $file->getSize(),
                    $file->hash('sha1'),
                    $file->getOriginalName()
                );
            }
            // 查找存档是否有效
            if (false === $upload->has($annex->path)) {
                $fileStream = fopen($file->getRealPath(), 'r');
                if (!is_resource($fileStream)) {
                    $this->errorMessage = "不是有效的文件资源: {$file->getRealPath()}";
                    return false;
                }
                Filesystem::disk('upload')->putStream($annex->path, $fileStream);
                fclose($fileStream);
            }
        } catch (BusinessResultSuccess $success) {
            $this->errorMessage = $success->getMessage();
            return false;
        } catch (FileException $exception) {
            $this->errorMessage = $exception->getMessage();
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
