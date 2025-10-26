<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AfterFindEvent extends Event
{
    public function __construct(
        private string $entityClassName,
        private array $data,
    ) {
    }

    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * @return array<string, string|int|bool|float|null>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @var array<int, string|int|bool|float|null>
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
