<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AfterFindEvent extends Event
{
    /**
     * @param array<int, string|int|bool|float|null>
     */
    public function __construct(
        private(set) string $entityClassName,
        public array $data,
    ) {}
}
