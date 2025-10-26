<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Messenger\Command;

class CreateEntity
{
    public function __construct(
        private string $uuid,
        private string $entityClassName,
        private array $data,
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
