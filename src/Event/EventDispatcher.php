<?php

/**
 * @see       https://github.com/mezzio/mezzio-swoole for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Swoole\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(private ListenerProviderInterface $listenerProvider)
    {
    }

    /**
     * @return object Returns the event passed to the method.
     */
    public function dispatch(object $event)
    {
        $stoppable = $event instanceof StoppableEventInterface;

        /** @psalm-suppress MixedMethodCall */
        if ($stoppable && $event->isPropagationStopped()) {
            return $event;
        }

        /** @psalm-suppress MixedAssignment */
        foreach ($this->listenerProvider->getListenersForEvent($event) as $listener) {
            /** @psalm-suppress MixedFunctionCall */
            $listener($event);
            if (! $stoppable) {
                continue;
            }
            /** @psalm-suppress MixedMethodCall */
            if (! $event->isPropagationStopped()) {
                continue;
            }
            break;
        }

        return $event;
    }
}
