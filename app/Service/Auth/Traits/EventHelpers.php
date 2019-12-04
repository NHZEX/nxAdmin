<?php
declare(strict_types=1);

namespace app\Service\Auth\Traits;

use app\Service\Auth\Listens\AuthenticatedEvent;
use app\Service\Auth\Listens\LoginEvent;
use think\Container;
use think\Event;

/**
 * Trait EventHelpers
 * @package app\Service\Auth\Traits
 * @property Container $container
 */
trait EventHelpers
{
    protected function triggerAuthenticatedEvent($user)
    {
        /** @var Event $event */
        $event = $this->container->get('event');
        $event->trigger(AuthenticatedEvent::class, [$user]);
    }

    protected function triggerLoginEvent($user, $remember = false)
    {
        /** @var Event $event */
        $event = $this->container->get('event');
        $event->trigger(LoginEvent::class, [$user]);
    }
}
