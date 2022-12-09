<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Entity;

use Ifrost\ApiFoundation\Entity\ApiEntityInterface;

interface EntityInterface extends ApiEntityInterface
{
    public static function getTableName(): string;
}
