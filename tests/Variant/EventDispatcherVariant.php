<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Variant;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDispatcherVariant implements EventDispatcherInterface
{
    public function dispatch(object $event, ?string $eventName = null): object
    {
        return $event;
    }
}
