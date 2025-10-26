<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeDeleteEvent extends Event
{
    public function __construct(
        private(set) string $uuid,
        private(set) string $entityClassName,
    ) {
    }
}
