<?php

namespace app\Exception;

use app\Traits\PrintAbnormal;
use LogicException;
use think\Response;
use Throwable;

/**
 * Class BusinessResult.
 */
class BusinessResult extends LogicException
{
    use PrintAbnormal;

    public const FLAG_IGNORE_LOG = 0x01;

    protected int $flag = 0;

    protected ?int $httpCode = null;
    protected ?Response $response = null;

    public static function create(string $message = '', int $code = 0, ?Throwable $previous = null, ?int $httpCode = null, ?Response $response = null): self
    {
        return (new self($message, $code, $previous))->setHttpCode($httpCode)->setResponse($response);
    }

    public static function createWithNotRecord(string $message = '', int $code = 0, ?Throwable $previous = null, ?int $httpCode = null, ?Response $response = null): self
    {
        $error = new self($message, $code, $previous);
        $error->setFlag(self::FLAG_IGNORE_LOG);
        $error->setHttpCode($httpCode);
        $error->setResponse($response);

        return $error;
    }

    // 自定义字符串输出的样式
    public function __toString(): string
    {
        return "[#{$this->code}] {$this->message}";
    }

    public function setFlag(int $flag, bool $ignore = true): static
    {
        if ($ignore) {
            $this->flag |= $flag;
        } elseif ($this->isFlag($flag)) {
            $this->flag ^= $flag;
        }

        return $this;
    }

    public function isFlag(int $flag): bool
    {
        return ($this->flag & $flag) === $flag;
    }

    public function isIgnoreLog(): bool
    {
        return $this->isFlag(self::FLAG_IGNORE_LOG);
    }

    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }

    public function setHttpCode(?int $httpCode): static
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): static
    {
        $this->response = $response;

        return $this;
    }
}
