<?php

namespace Guzzle\Psr7;

use GuzzleHttp\Psr7\Response;
use function json_decode_throw_on_error;
use function strpos;

/**
 * Class JsonAwareResponse
 * @package app\common\psr7
 * @url https://stackoverflow.com/a/53444976/10242420
 */
class JsonAwareResponse extends Response
{
    /**
     * Cache for performance
     */
    private array $json;

    public function getJson()
    {
        if ($this->json) {
            return $this->json;
        }
        // get parent Body stream
        $body = $this->getBody();

        // if JSON HTTP header detected - then decode
        if (false !== strpos($this->getHeaderLine('Content-Type'), 'application/json')) {
            return $this->json = json_decode_throw_on_error($body);
        }
        return $body;
    }
}
