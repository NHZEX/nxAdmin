<?php
declare(strict_types=1);

namespace Tp\Response;

use think\Exception;
use function file_get_contents;
use function filesize;
use function gmdate;
use function is_file;
use function ob_end_clean;
use function pathinfo;
use function strlen;
use function time;

class File extends \think\response\File
{
    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return mixed
     * @throws \Exception
     */
    protected function output($data)
    {
        if (!$this->isContent && !is_file($data)) {
            throw new Exception('file not exists:' . $data);
        }

        @ob_end_clean();

        if (!empty($this->name)) {
            $name = $this->name;
        } else {
            $name = !$this->isContent ? pathinfo($data, PATHINFO_BASENAME) : '';
        }

        if ($this->isContent) {
            $mimeType = $this->mimeType;
            $size     = strlen($data);
        } else {
            $mimeType = $this->getMimeType($data);
            $size     = filesize($data);
        }

        $this->header['Pragma']                    = 'public';
        $this->header['Content-Type']              = $mimeType ?: 'application/octet-stream';
        $this->header['Cache-control']             = 'max-age=' . $this->expire;
        $this->header['Content-Disposition']       = ($this->force ? 'attachment; ' : '') . 'filename="' . $name . '"';
        $this->header['Content-Length']            = $size;
        $this->header['Content-Transfer-Encoding'] = 'binary';
        $this->header['Expires']                   = gmdate("D, d M Y H:i:s", time() + $this->expire) . ' GMT';

        $this->lastModified(gmdate('D, d M Y H:i:s', time()) . ' GMT');

        return $this->isContent ? $data : file_get_contents($data);
    }
}
