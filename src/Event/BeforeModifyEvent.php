<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Event;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeModifyEvent extends Event
{
    /**
     * @param array<string, string|int|bool|float|null>
     */
    public function __construct(
        private(set) EntityInterface $entity,
        public array $data,
    ) {
    }
}
