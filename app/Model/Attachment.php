<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/11/26
 * Time: 10:35
 */

namespace app\Model;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Tp\Model\Traits\ModelUtil;

/**
 * Class Attachment
 *
 * @package app\common\model
 * @property int $id
 * @property int $uid 用户id
 * @property int $status 状态
 * @property string $index 附件索引
 * @property string $real_path 实际路径
 * @property string $path 存储路径
 * @property string $mime 文件mime类型
 * @property mixed $ext 文件类型
 * @property int $size 文件大小
 * @property mixed $sha1 sha1散列值
 * @property string $driver 上传驱动
 * @property string $raw_file_name 原始文件名
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class Attachment extends Base
{
    use ModelUtil;

    protected $table = 'attachment';
    protected $pk = 'id';

    protected $readonly = [
        'create_time',
    ];

    const DRIVER_LOCAL = 'local';
    const DRIVER_DICT = [
        self::DRIVER_LOCAL => '/upload/',
    ];

    /**
     * 设置器 文件名长度限制
     * @param $value
     * @return string
     */
    protected function setRawFileNameAttr($value)
    {
        return mb_strcut_omit($value, 128);
    }

    /**
     * 虚拟列 获取真实访问路径
     * @return mixed
     */
    protected function getRealPathAttr()
    {
        return self::DRIVER_DICT[$this->driver] . $this->path;
    }

    /**
     * 格式化为请求路径
     * @param $pic_path
     * @return mixed|string|null
     */
    public static function formatAccessPath($pic_path)
    {
        if (!$pic_path) {
            return null;
        }
        if (is_array($pic_path)) {
            foreach ($pic_path as &$val) {
                if ($result = self::parseUrl($val)) {
                    $val = $result;
                } else {
                    $val = null;
                }
            }
            return array_filter($pic_path, function ($v) {
                return !empty($v);
            });
        } else {
            if ($result = self::parseUrl($pic_path)) {
                return $result;
            }
        }
        return null;
    }

    /**
     * 格式化为上传组件可用路径
     * @param $pic_path
     * @return array|string|null
     */
    public static function formatForItemPath($pic_path)
    {
        if (!$pic_path) {
            return null;
        }
        if (is_array($pic_path)) {
            foreach ($pic_path as &$val) {
                if ($result = self::parseUrl($val)) {
                    $val = "{$val}:{$result}";
                } else {
                    $val = null;
                }
            }
            return array_filter($pic_path, function ($v) {
                return !empty($v);
            });
        } else {
            if ($result = self::parseUrl($pic_path)) {
                return "{$pic_path}:{$result}";
            }
        }
        return null;
    }

    /**
     * 解析成真实路径
     * @param string $input_path
     * @return null|string
     */
    public static function parseUrl($input_path): ?string
    {
        $path_arr = explode('#', $input_path);
        if (count($path_arr) !== 2) {
            return null;
        }
        [$path, $driver] = $path_arr;

        return self::DRIVER_DICT[$driver].$path;
    }

    /**
     * @param $fileKey
     * @return false|Attachment
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function findFile($fileKey)
    {
        /** @var self|null $file */
        $file = (new self())->where('index', $fileKey)->find();
        if ($file instanceof self) {
            return $file;
        }
        return false;
    }

    /**
     * @param string $index
     * @param int $userId
     * @param string $savePath
     * @param string $fileMime
     * @param string $fileExt
     * @param int $fileSize
     * @param string $fileSha1
     * @param string $rawFileName
     * @return Attachment
     */
    public static function createRecord(
        string $index,
        int $userId,
        string $savePath,
        string $fileMime,
        string $fileExt,
        int $fileSize,
        string $fileSha1,
        string $rawFileName
    ) {
        $that = new self();
        $that->status = 0;
        $that->driver = self::DRIVER_LOCAL;
        $that->index = $index;
        $that->uid = $userId;
        $that->path = $savePath;
        $that->mime = $fileMime;
        $that->ext = $fileExt;
        $that->size = $fileSize;
        $that->sha1 = $fileSha1;
        $that->raw_file_name = $rawFileName;
        $that->save();
        return $that;
    }
}
