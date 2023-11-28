<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Entity;

interface WithDbalWritableFormat
{
    /**
     * @return array<string, string|int|bool|float|null>
     */
    public function getWritableFormat(): array;
}
