<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Controller\DoctrineApiController;

use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ramsey\Uuid\Uuid;

class FetchOneTest extends ProductTestCase
{
    public function testShouldReturnRequestedProduct()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';

        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        // When & Then
        $this->assertEquals(
            $this->products->get($uuid)->getWritableFormat(),
            $controller->fetchOne(EntityQuery::class, Product::getTableName(), Uuid::fromString($uuid)->getBytes())
        );
    }

    public function testShouldThrowExceptionNotFoundExceptionWhenRequestedProductDoesNotExist(): void
    {
        // Expect & Given
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf('Record not found for query "%s"', EntityQuery::class));
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();

        // When & Then
        $response = $controller->fetchOne(EntityQuery::class, Product::getTableName(), '850186cc-9bac-44b8-a0f4-cea287290b8b');
    }
}
