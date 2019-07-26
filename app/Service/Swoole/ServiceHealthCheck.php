<?php
declare(strict_types=1);

namespace app\Service\Swoole;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use HZEX\TpSwoole\Contract\ServiceHealthCheckInterface;
use think\facade\Config;

class ServiceHealthCheck implements ServiceHealthCheckInterface
{
    /**
     * @var string 健康检查结果
     */
    protected $message = '';
    /**
     * @var int 健康检查错误码
     */
    protected $code = 0;

    /**
     * 健康检查结果
     * @return string
     */
    public function getMessage(): string
    {
        if (0 !== $this->code) {
            $this->message = substr($this->message, 0, 64);
        }
        if ("\n" !== substr($this->message, -1, 1)) {
            $this->message .= PHP_EOL;
        }
        return $this->message;
    }

    /**
     * 健康检查错误码
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * 健康检查处理器
     * @return bool
     */
    public function handle(): bool
    {
        $config = Config::get('swoole.server');

        // 获取服务端口
        if (empty($config['listen'])) {
            $port = $config['port'];
        } else {
            if (false === strpos($config['listen'], ':')) {
                $port = '9501';
            } else {
                [, $port] = explode(':', $config['listen']);
            }
        }
        $port = (int) $port;

        // 请求检查页面
        $client = new Client();
        try {
            $res = $client->get("http://127.0.0.1:{$port}/survive");
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $this->message = $e->getResponse()->getBody()->getContents();
                $this->code = $e->getResponse()->getStatusCode();
            } else {
                $this->message = $e->getMessage();
                $this->code = $e->getCode();
            }
            return false;
        }

        $this->message = $res->getBody()->getContents();
        if (200 !== $res->getStatusCode()) {
            $this->code = $res->getStatusCode();
            return false;
        }

        return true;
    }
}
