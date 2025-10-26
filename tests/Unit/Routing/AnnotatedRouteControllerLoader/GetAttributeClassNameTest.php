<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Routing\AnnotatedRouteControllerLoader;

use Ifrost\ApiFoundation\Attribute\ApiController;
use Ifrost\DoctrineApiBundle\Tests\Variant\Routing\AnnotatedRouteControllerLoaderVariant;
use PHPUnit\Framework\TestCase;

class GetAttributeClassNameTest extends TestCase
{
    public function testShouldReturnApiAttributeClassName()
    {
        // When & Then
        $this->assertEquals(
            ApiController::class,
            (new AnnotatedRouteControllerLoaderVariant())->getAttributeClassName(),
        );
    }
}
