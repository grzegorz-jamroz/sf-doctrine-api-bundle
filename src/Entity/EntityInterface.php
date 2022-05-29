<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Entity;

use Ifrost\Foundations\ArrayConstructable;

interface EntityInterface extends ArrayConstructable, \JsonSerializable
{
    public static function getTableName(): string;

    /**
     * @return array<int, string>
     */
    public static function getFields(): array;
}
