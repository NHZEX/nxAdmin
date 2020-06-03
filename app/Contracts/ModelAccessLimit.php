<?php
declare(strict_types=1);

namespace app\Contracts;

interface ModelAccessLimit
{
    /**
     * 获取访问规则
     * @param int $genre
     * @return array<array<string>>|null
     */
    public function getAccessControl(int $genre): ?array;

    /**
     * 允许访问目标
     * @return int
     */
    public function getAllowAccessTarget();
}
