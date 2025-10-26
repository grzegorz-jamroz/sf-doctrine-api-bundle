<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Event;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeCreateEvent extends Event
{
    public function __construct(
        private EntityInterface $entity,
        private array $data,
    ) {
    }

    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    /**
     * @return array<string, string|int|bool|float|null>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @var array<string, string|int|bool|float|null>
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
