<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Routing\AnnotatedRouteControllerLoader;

use Ifrost\ApiFoundation\Attribute\Api as ApiAttribute;
use PHPUnit\Framework\TestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Routing\AnnotatedRouteControllerLoaderVariant;

class GetAttributeClassNameTest extends TestCase
{
    public function testShouldReturnApiAttributeClassName()
    {
        // When & Then
        $this->assertEquals(
            ApiAttribute::class,
            (new AnnotatedRouteControllerLoaderVariant())->getAttributeClassName()
        );
    }
}
