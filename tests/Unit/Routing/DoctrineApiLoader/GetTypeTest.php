<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Routing\DoctrineApiLoader;

use Ifrost\DoctrineApiBundle\Tests\Variant\Routing\DoctrineApiLoaderVariant;
use PHPUnit\Framework\TestCase;

class GetTypeTest extends TestCase
{
    public function testShouldReturnDoctrineApiLoaderType()
    {
        // When & Then
        $this->assertEquals(
            'doctrine_api_attribute',
            (new DoctrineApiLoaderVariant())->getType(),
        );
    }
}
