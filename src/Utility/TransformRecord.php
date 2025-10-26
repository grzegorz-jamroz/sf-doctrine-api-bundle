<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Utility;

use Exception;

class TransformRecord
{
    public static function toRead(mixed $value): string|float|int|bool|array|null
    {
        if (is_string($value) === false) {
            return $value;
        }

        try {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception) {
        }

        return $value;
    }
}
