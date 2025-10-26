<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Utility;

use Ifrost\DoctrineApiBundle\Tests\Variant\Sample;
use Ifrost\DoctrineApiBundle\Utility\TransformRecord;
use PHPUnit\Framework\TestCase;

class TransformRecordTest extends TestCase
{
    public function testShouldThrowInvalidArgumentExceptionForUnsupportedValue(): void
    {
        // Expect & Given
        $object = new Sample();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Unsupported value type (%s).', gettype($object)));

        // When & Then
        TransformRecord::toRead($object);
    }
}
