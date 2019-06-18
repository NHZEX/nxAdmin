<?php


namespace Mlog\Handler;


use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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

    public function __construct($channel, $stream, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false)
    {
        $this->channel = $channel;
        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
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

        $channle = $this->channel;
        $request= app()->request;
        $method = $request->method();
        $ip = $request->ip();
        $url = $request->url(true);

        $log['formatted'] = "{$channle} {$ip} {$method} {$url} \n";
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