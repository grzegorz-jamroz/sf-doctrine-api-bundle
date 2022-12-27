<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeDeleteEvent extends Event
{
    public function __construct(private string $uuid)
    {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
