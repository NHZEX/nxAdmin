<?php
declare(strict_types=1);

namespace app\Service\Auth\Traits;

/**
 * Trait GuardHelpers
 * @package app\Service\Auth\Traits
 */
trait GuardHelpers
{
    /**
     * 获取错误消息
     *
     * @var string
     */
    protected $message = '';

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
    /**
     * @param string $message
     */
    protected function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
