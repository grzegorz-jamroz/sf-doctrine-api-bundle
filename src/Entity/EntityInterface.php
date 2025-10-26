<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Entity;

use Ifrost\ApiFoundation\Entity\ApiEntityInterface;
use Symfony\Component\Uid\Uuid;

interface EntityInterface extends ApiEntityInterface, WithDbalWritableFormat
{
    public function getUuid(): Uuid;

    public static function getTableName(): string;
}
