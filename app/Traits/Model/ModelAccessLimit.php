<?php

declare(strict_types=1);

namespace app\Traits\Model;

use app\Exception\AccessControl;
use app\Model\AdminUser;
use app\Service\Auth\AuthHelper;
use think\db\Query;
use think\Model;
use function array_keys;
use function count;

/**
 * Trait ModelAccessLimit
 * @package app\Traits\Model
 * @mixin \app\Contracts\ModelAccessLimit
 */
trait ModelAccessLimit
{
    protected bool $withoutAccessLimit = false;

    public function withoutWriteAccessLimit()
    {
        $this->withoutAccessLimit = true;
        return $this;
    }

    public function scopeAccessControl(Query $query)
    {
        if (!$this instanceof \app\Contracts\ModelAccessLimit) {
            return;
        }
        if (empty(AuthHelper::id())) {
            return;
        }
        if ($query->getModel()->withoutAccessLimit) {
            return;
        }
        $genre = AuthHelper::userGenre();
        if (AdminUser::GENRE_SUPER_ADMIN === $genre) {
            return;
        }

        if ($genreControl = $this->getAccessControl($genre)) {
            if (count($genreControl) === 1 && isset($genreControl['self'])) {
                $query->whereRaw("id = {$this->getAllowAccessTarget()}");
            } else {
                unset($genreControl['self']);
                $query->whereIn('genre', array_keys($genreControl));
            }
        } else {
            $query->where('genre', '=', null);
        }
    }

    /**
     * @param static|Model $data
     * @throws AccessControl
     */
    protected static function checkAccessControl($data)
    {
        if (!$data instanceof \app\Contracts\ModelAccessLimit) {
            return;
        }
        /** @noinspection PhpUndefinedFieldInspection */
        /** @phpstan-ignore-next-line */
        if ($data->withoutAccessLimit) {
            return;
        }
        if (empty($id = AuthHelper::id())) {
            return;
        }
        $genre = AuthHelper::userGenre();
        if (AdminUser::GENRE_SUPER_ADMIN === $genre) {
            return;
        }

        $dataGenre = $data->getOrigin('genre') ?? $data->getData('genre');
        if (null === $dataGenre) {
            return;
        }
        $genreControl = $data->getAccessControl($genre);
        if (empty($genreControl)) {
            throw new AccessControl('当前登陆的用户无该数据的操作权限');
        }
        if (isset($genreControl['self'])
            && $genreControl['self'] === 'rw'
            && $data->getAllowAccessTarget() === $data->getOrigin('id')
        ) {
            return;
        } elseif (isset($genreControl[$dataGenre]) && $genreControl[$dataGenre] === 'rw') {
            return;
        }
        throw new AccessControl('当前登陆的用户无该数据的操作权限');
    }
}
