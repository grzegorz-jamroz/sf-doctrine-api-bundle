<?php

declare(strict_types=1);

namespace Tests\Unit\Controller\DoctrineApiController;

use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Tests\Unit\ProductTestCase;
use Tests\Variant\Controller\DoctrineApiControllerVariant;
use Tests\Variant\Entity\Product;

class FetchOneTest extends ProductTestCase
{
    public function testShouldReturnRequestedProduct()
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When
        $productData = $controller->fetchOne(EntityQuery::class, Product::TABLE, $uuid);

        // Then
        $this->assertEquals($this->productsData->get($uuid), $productData);
    }

    public function testShouldThrowExceptionNotFoundExceptionWhenRequestedProductDoesNotExist(): void
    {
        // Expect & Given
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf('Record not found for query "%s"', EntityQuery::class));
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();

        // When & Then
        $response = $controller->fetchOne(EntityQuery::class, Product::TABLE, '850186cc-9bac-44b8-a0f4-cea287290b8b');
    }
}
