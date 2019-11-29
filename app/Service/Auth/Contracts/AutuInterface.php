<?php
declare(strict_types=1);

namespace app\Service\Auth\Contracts;

interface AutuInterface
{
    /**
     * 是否已经登录
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * 获取用户对象
     *
     * @return mixed
     */
    public function user();

    /**
     * 用户具有这个操作的权限
     *
     * @return bool
     */
    public function can(): bool;
}
