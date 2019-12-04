<?php
declare(strict_types=1);

namespace app\Service\Auth\Access;

use app\Service\Auth\Exception\AuthorizationException;
use think\Container;
use think\exception\FuncNotFoundException;

class Gate
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * The user resolver callable.
     *
     * @var callable
     */
    protected $userResolver;

    /**
     * @var array
     */
    protected $abilities = [];

    /**
     * All of the registered before callbacks.
     *
     * @var array
     */
    protected $beforeCallbacks = [];

    /**
     * All of the registered after callbacks.
     *
     * @var array
     */
    protected $afterCallbacks = [];

    public function __construct(
        Container $container,
        callable $userResolver,
        array $abilities = [],
        array $beforeCallbacks = [],
        array $afterCallbacks = []
    ) {
        $this->container       = $container;
        $this->userResolver    = $userResolver;
        $this->abilities       = $abilities;
        $this->afterCallbacks  = $afterCallbacks;
        $this->beforeCallbacks = $beforeCallbacks;
    }

    /**
     * Register a callback to run before all Gate checks.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function before(callable $callback)
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to run after all Gate checks.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function after(callable $callback)
    {
        $this->afterCallbacks[] = $callback;

        return $this;
    }

    /**
     * 是否存在能力
     *
     * @param string[] $abilitys
     * @return bool
     */
    public function has(...$abilitys): bool
    {
        foreach ($abilitys as $ability) {
            if (!isset($this->abilities[$this->getAlias($ability)])) {
                return false;
            }
        }
        return true;
    }

    /**
     * 定义一个能力
     * @param string          $ability
     * @param callable|string $callback
     * @return $this
     */
    public function define(string $ability, $callback)
    {
        $this->abilities[$ability] = $callback;
        return $this;
    }

    /**
     * 当前用户是否授予给定能力
     *
     * @param string      $ability
     * @param array|mixed $arguments
     * @return bool
     */
    public function allows(string $ability, $arguments = [])
    {
        return $this->check($ability, $arguments);
    }

    /**
     * 当前用户是否拒绝给定能力
     *
     * @param string      $ability
     * @param array|mixed $arguments
     * @return bool
     */
    public function denies(string $ability, $arguments = [])
    {
        return !$this->allows($ability, $arguments);
    }

    /**
     * 当前用户是否授予给定能力
     *
     * @param iterable|string $abilities
     * @param array|mixed     $arguments
     * @return bool
     */
    public function check($abilities, $arguments = [])
    {
        $abilities = (array) $abilities;
        foreach ($abilities as $ability) {
            if (!$this->inspect($ability, $arguments)->allowed()) {
                return false;
            }
        }
        return true;
    }

    /**
     * 是否应该基于当前用户指定能力
     *
     * @param iterable|string $abilities
     * @param array|mixed     $arguments
     * @return bool
     */
    public function any($abilities, $arguments = [])
    {
        return $this->check($abilities, $arguments);
    }

    /**
     * 检查用户给定得能力
     *
     * @param string      $ability
     * @param array|mixed $arguments
     * @return Response
     */
    public function inspect($ability, $arguments = [])
    {
        try {
            $result = $this->raw($ability, $arguments);

            if ($result instanceof Response) {
                return $result;
            }

            return $result ? Response::allow() : Response::deny();
        } catch (AuthorizationException $e) {
            return $e->toResponse();
        }
    }


    public function raw($ability, $arguments = [])
    {
        $user = $this->resolveUser();
        $arguments = is_array($arguments) ? $arguments : (array) $arguments;

        $result = $this->callBeforeCallbacks($user, $ability, $arguments);

        if (null === $result) {
            $result = $this->callAuthCallback($user, $ability, $arguments);
        }

        return $this->callAfterCallbacks($user, $ability, $arguments, $result);
    }

    /**
     * @param mixed  $user
     * @param string $ability
     * @param array  $arguments
     * @return bool
     */
    protected function callAuthCallback($user, $ability, array $arguments)
    {
        array_unshift($arguments, $user);
        try {
            return $this->container->invoke($this->abilities[$this->getAlias($ability)], $arguments);
        } catch (FuncNotFoundException $exception) {
            return null;
        }
    }

    /**
     * Call all of the before callbacks and return if a result is given.
     *
     * @param  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @return bool|null
     */
    protected function callBeforeCallbacks($user, $ability, array $arguments)
    {
        foreach ($this->beforeCallbacks as $before) {
            if (! is_null($result = $this->container->invoke($before, [$user, $ability, $arguments]))) {
                return $result;
            }
        }
        return null;
    }

    /**
     * Call all of the after callbacks with check result.
     *
     * @param  $user
     * @param  string  $ability
     * @param  array  $arguments
     * @param  bool  $result
     * @return bool|null
     */
    protected function callAfterCallbacks($user, $ability, array $arguments, $result)
    {
        foreach ($this->afterCallbacks as $after) {
            $afterResult = $this->container->invoke($after, [$user, $ability, $result, $arguments]);

            $result = $result ?? $afterResult;
        }

        return $result;
    }

    /**
     * @param string $ability
     * @return string
     */
    public function getAlias(string $ability): string
    {
        if (isset($this->abilities[$ability])) {
            $callback = $this->abilities[$ability];

            if (is_string($callback)) {
                return $this->getAlias($callback);
            }
        }

        return $ability;
    }

    /**
     * Resolve the user from the user resolver.
     *
     * @return mixed
     */
    protected function resolveUser()
    {
        return call_user_func($this->userResolver);
    }

    /**
     * Get a gate instance for the given user.
     *
     * @param mixed $user
     * @return static
     */
    public function forUser($user)
    {
        $callback = function () use ($user) {
            return $user;
        };

        // 可以考虑使用 clone 实现
        return new static($this->container, $callback, $this->abilities, $this->beforeCallbacks, $this->afterCallbacks);
    }
}
