<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Entity;

use Ifrost\ApiFoundation\Entity\ApiEntityInterface;

interface EntityInterface extends ApiEntityInterface
{
    /**
     * @return array<string, string|int|bool|float|null>
     */
    public function getWritableFormat(): array;

    public static function getTableName(): string;
}
