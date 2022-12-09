<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Controller\DoctrineApiController;

use Ifrost\ApiFoundation\ApiInterface;
use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\ProductController;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Tests\Variant\Sample;
use Ifrost\DoctrineApiBundle\Utility\DoctrineApi;
use PHPUnit\Framework\TestCase;

class GetApiTest extends TestCase
{
    public function testShouldReturnDoctrineApiWhenEntityClassNamePassedInMethodParameter()
    {
        // Given
        $controller = new DoctrineApiControllerVariant();

        // When & Then
        $this->assertInstanceOf(DoctrineApi::class, $controller->getApi(Product::class));
    }

    public function testShouldReturnDoctrineApiWhenEntityClassNamePassedUsingApiAttribute()
    {
        // Given
        $controller = new ProductController();

        // When & Then
        $this->assertInstanceOf(ApiInterface::class, $controller->getApi());
    }

    public function testShouldThrowInvalidArgumentExceptionWhenGivenEntityClassNameNotImplementsEntityInterface()
    {
        // Expect
        $entityClassName = Sample::class;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Given argument entityClassName (%s) has to implement "%s" interface.', $entityClassName, EntityInterface::class));

        // Given
        $controller = new ProductController();

        // When & Then
        $this->assertInstanceOf(ApiInterface::class, $controller->getApi($entityClassName));
    }
}
