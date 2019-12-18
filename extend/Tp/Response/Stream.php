<?php
declare(strict_types=1);

namespace Tp\Response;

use RuntimeException;
use think\Cookie;
use think\Response;
use function fastcgi_finish_request;
use function fclose;
use function fopen;
use function function_exists;
use function get_resource_type;
use function header;
use function headers_sent;
use function http_response_code;
use function is_null;
use function is_resource;
use function stream_copy_to_stream;
use function stream_get_contents;
use function stream_get_meta_data;

class Stream extends Response
{
    /**
     * 输出type
     * @var string
     */
    protected $contentType = 'application/octet-stream';

    protected $streamMeta = [];

    /**
     * Stream constructor.
     * @param Cookie $cookie
     * @param resource $data
     * @param int    $code
     */
    public function __construct(Cookie $cookie, $data, int $code = 200)
    {
        if (!is_resource($data)) {
            throw new RuntimeException('$data is not resource');
        }
        if ('stream' !== get_resource_type($data)) {
            throw new RuntimeException('$data is not stream resource');
        }
        $this->init($data, $code);
        if (!$this->isSupportWrapper($wrapperType)) {
            throw new RuntimeException('$data is not stream wrapper type: ' . $wrapperType);
        }
        $this->cookie = $cookie;
    }

    public function isSupportWrapper(&$wrapperType)
    {
        $wrapperType = $this->getStreamMeta()['wrapper_type'];
        return 'plainfile' === $wrapperType;
    }

    public function getStreamMeta()
    {
        return stream_get_meta_data($this->data);
    }

    /**
     * 输出类型
     * @access public
     * @param string      $contentType 输出类型
     * @param string|null $charset
     * @return $this
     */
    public function contentType(string $contentType, string $charset = null)
    {
        if (!empty($contentType)) {
            $this->header['Content-Type'] = $contentType;
        }

        return $this;
    }

    public function getContent(): string
    {
        return stream_get_contents($this->data);
    }

    public function send(): void
    {
        if (!headers_sent() && !empty($this->header)) {
            // 发送状态码
            http_response_code($this->code);
            // 发送头部信息
            foreach ($this->header as $name => $val) {
                header($name . (!is_null($val) ? ':' . $val : ''));
            }
        }
        if ($this->cookie) {
            $this->cookie->save();
        }

        $output = fopen('php://output', 'w');
        stream_copy_to_stream($this->data, $output);
        fclose($output);

        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
    }

    public function __destruct()
    {
        fclose($this->data);
    }
}
