<?php


namespace Mlog\Handler;

use Monolog\Logger;

class RotatingFileHandler extends \Monolog\Handler\RotatingFileHandler
{
    public function __construct($filename, $maxFiles = 0, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false)
    {
        parent::__construct($filename, $maxFiles, $level, $bubble, $filePermission, $useLocking);
        $this->filenameFormat = '{date}';
        $this->dateFormat = 'd';
        $this->url = $this->getTimedFilename();
    }
}
