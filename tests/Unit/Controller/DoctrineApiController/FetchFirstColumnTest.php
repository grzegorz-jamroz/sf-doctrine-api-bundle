<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Controller\DoctrineApiController;

use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product\GetAllProductName;
use Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product\GetAllProductsByName;

class FetchFirstColumnTest extends ProductTestCase
{
    public function testShouldReturnEmptyArrayWhenNoProductMatches()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        /** @var Product $product */
        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        // When
        $results = $controller->fetchFirstColumn(GetAllProductsByName::class, 'trumpet');

        // Then
        $this->assertEquals([], $results);
    }

    public function testShouldReturnOneProductNameWhenOnlyOneProductMatches()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        /** @var Product $product */
        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        $expected = [
            $this->products->get('62d925ad-4ef7-47a9-be28-79d71534c099')->getName(),
        ];

        // When
        $results = $controller->fetchFirstColumn(GetAllProductsByName::class, 'drum');

        // Then
        $this->assertEquals($expected, $results);
    }

    public function testShouldReturnTwoProductNamesWhenOnlyTwoProductsMatch()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        /** @var Product $product */
        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        $expected = [
            $this->products->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3')->getName(),
            $this->products->get('fe687d4a-a5fc-426b-ba15-13901bda54a6')->getName(),
        ];

        // When
        $results = $controller->fetchFirstColumn(GetAllProductsByName::class, 'guita');

        // Then
        $this->assertEquals($expected, $results);
    }

    public function testShouldReturnArrayWithAllProductNamesAsResult()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        $productNames = array_map(fn (Product $product) => $product->getName(), $this->products->toArray());
        /** @var Product $product */
        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        // When
        $results = $controller->fetchFirstColumn(
            GetAllProductName::class,
            $product->getUuid(),
        );

        // Then
        $this->assertEquals(array_values($productNames), $results);
    }
}
