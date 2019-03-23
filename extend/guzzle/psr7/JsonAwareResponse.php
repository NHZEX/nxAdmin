<?php
/**
 * Created by PhpStorm.
 * User: NHZEXG
 * Date: 2018/12/21
 * Time: 19:39
 */

namespace guzzle\psr7;

use GuzzleHttp\Psr7\Response;

/**
 * Class JsonAwareResponse
 * @package app\common\psr7
 * @url https://stackoverflow.com/a/53444976/10242420
 */
class JsonAwareResponse extends Response
{
    /**
     * Cache for performance
     * @var array
     */
    private $json;

    /**
     * @return array|mixed|\Psr\Http\Message\StreamInterface
     * @throws \app\exception\JsonException
     */
    public function getBody()
    {
        if ($this->json) {
            return $this->json;
        }
        // get parent Body stream
        $body = parent::getBody();

        // if JSON HTTP header detected - then decode
        if (false !== strpos($this->getHeaderLine('Content-Type'), 'application/json')) {
            return $this->json = \json_decode_throw_on_error($body);
        }
        return $body;
    }
}
