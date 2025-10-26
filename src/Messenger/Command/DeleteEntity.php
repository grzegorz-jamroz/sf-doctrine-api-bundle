<?php
declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Messenger\Command;

class DeleteEntity
{
    public function __construct(
        private string $uuid,
        private string $entityClassName,
    )
    {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }
}
