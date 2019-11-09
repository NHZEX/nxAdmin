<?php
/** @noinspection PhpIncompatibleReturnTypeInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
declare(strict_types=1);

namespace app\Service\Auth;

use app\Service\Auth\Contracts\Authenticatable;
use app\Service\Auth\Contracts\Hasher;
use app\Service\Auth\Contracts\UserProvider;
use think\contract\Arrayable;
use think\helper\Str;
use think\Model;

class ThinkOrmUserProvider implements UserProvider
{
    /**
     * @var string
     */
    protected $model;

    /**
     * @var Hasher
     */
    protected $hasher;

    /**
     * Create a new database user provider.
     *
     * @param Hasher $hasher
     * @param string $model
     */
    public function __construct(Hasher $hasher, $model)
    {
        $this->model = $model;
        $this->hasher = $hasher;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier
     * @return Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        /** @var Model|Authenticatable $model */
        $model = $this->createModel();

        return $model
            ->where($model->getAuthIdentifierName(), $identifier)
            ->find();
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param mixed  $identifier
     * @param string $token
     * @return Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();

        /** @var Authenticatable|Model $retrievedModel */
        $retrievedModel = $model->where($model->getAuthIdentifierName(), $identifier)->find();

        if (! $retrievedModel) {
            return null;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retrievedModel : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param Authenticatable|Model $user
     * @param string          $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        // TODO 需要确认工作是否正常
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * 通过给定的凭据检索用户
     * Retrieve a user by the given credentials.
     *
     * @param array $credentials
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) || !isset($credentials['password'])) {
            return null;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->createModel();

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query = $query->whereIn($key, $value);
            } else {
                $query = $query->where($key, '=', $value);
            }
        }

        return $query->find();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param Authenticatable $user
     * @param array           $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return $this->hasher->check($plain, $user->getAuthPassword());
    }


    /**
     * Create a new instance of the model.
     *
     * @return Model|Authenticatable
     */
    public function createModel()
    {
        $class = '\\' . ltrim($this->model, '\\');

        return new $class;
    }


    /**
     * Gets the hasher implementation.
     *
     * @return Hasher
     */
    public function getHasher()
    {
        return $this->hasher;
    }

    /**
     * Gets the name of the Eloquent user model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Sets the name of the Eloquent user model.
     *
     * @param  string  $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }
}
