<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Messenger\Command;

class UpdateEntity
{
    /**
     * @param array<string, string|int|bool|float|array<mixed,mixed>|null> $data
     */
    public function __construct(
        private(set) string $uuid,
        private(set) string $entityClassName,
        private(set) array $data,
    ) {
    }
}
