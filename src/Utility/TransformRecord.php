<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Utility;

use InvalidArgumentException;
use Throwable;

class TransformRecord
{
    /**
     * @return string|float|int|bool|array<mixed, mixed>|null
     */
    public static function toRead(mixed $value): string|float|int|bool|array|null
    {
        if (
            $value === null
            || is_float($value)
            || is_int($value)
            || is_bool($value)
            || is_array($value)
        ) {
            return $value;
        }

        if (is_string($value) === false) {
            throw new InvalidArgumentException(sprintf('Unsupported value type (%s).', gettype($value)));
        }

        try {
            $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            /** @var array<mixed,mixed> $value */
            return $value;
        } catch (Throwable) {
            return $value;
        }
    }
}
