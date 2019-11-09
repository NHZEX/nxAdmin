<?php
declare(strict_types=1);

namespace app\Service\Auth\Traits;

use app\Service\Auth\Contracts\Authenticatable;

trait GuardEvents
{
    /**
     * Register an authentication attempt event listener.
     *
     * @param  mixed  $callback
     * @return void
     */
    public function attempting($callback)
    {
        // TODO  event listen
    }

    /**
     * Fire the attempt event with the arguments.
     *
     * @param  array  $credentials
     * @param  bool  $remember
     * @return void
     */
    protected function fireAttemptEvent(array $credentials, $remember = false)
    {
    }

    /**
     * Fire the login event if the dispatcher is set.
     *
     * @param  Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    protected function fireLoginEvent($user, $remember = false)
    {
    }

    /**
     * Fire the authenticated event if the dispatcher is set.
     *
     * @param  Authenticatable  $user
     * @return void
     */
    protected function fireAuthenticatedEvent($user)
    {
    }

    /**
     * Fire the other device logout event if the dispatcher is set.
     *
     * @param  Authenticatable  $user
     * @return void
     */
    protected function fireOtherDeviceLogoutEvent($user)
    {
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param  Authenticatable|null  $user
     * @param  array  $credentials
     * @return void
     */
    protected function fireFailedEvent($user, array $credentials)
    {
    }
}
