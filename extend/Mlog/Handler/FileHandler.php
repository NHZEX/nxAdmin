<?php

namespace Mlog\Handler;

use Monolog\Handler\StreamHandler;

/**
 * 文件处理器
 * Created by PhpStorm.
 */
class FileHandler extends StreamHandler
{
    /**
     * 内容
     * @var array
     */
    protected $messages = [];

    protected $channel;

    protected $recordInfo = [];

    public function __construct($channel)
    {
        //获取mlog的文件配置
        $config = config('mlog.file');
        //设置文件名
        $timestamp = time();
        $filename = $config['path'] . date('Ym', $timestamp)
            . DIRECTORY_SEPARATOR . date('d', $timestamp) . '.log';

        $this->channel = $channel;

        parent::__construct($filename, $config['level']);
    }

    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processRecord($record);
        $record['formatted'] = $this->getFormatter()->format($record);
        $this->messages[] = $record['formatted'];

        return false === $this->bubble;
    }

    protected function write(array $record)
    {
        if (empty($this->messages)) {
            return;
        }

        $date = date('Y-m-d h:i:sa', time());
        $channle = $this->channel;
        $request= app()->request;
        $method = $request->method();
        $ip = $request->ip();
        $url = $request->url(true);

        $log['formatted'] = "{$date} {$channle} {$ip} {$method} {$url} \n";
        foreach ($this->messages as $level => $formatted) {
            $log['formatted'] .= "{$formatted}";
        }
        $log['formatted'] .= "------------------------------\n";
        parent::write($log);
    }

    public function close()
    {
        $this->write([]);
        parent::close();
    }
}
