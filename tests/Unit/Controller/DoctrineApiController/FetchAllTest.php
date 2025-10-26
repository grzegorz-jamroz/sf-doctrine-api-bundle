<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Controller\DoctrineApiController;

use Ifrost\DoctrineApiBundle\Query\DbalCriteria;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;

class FetchAllTest extends ProductTestCase
{
    public function testShouldReturnEmptyArray(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();

        // When
        $data = $controller->fetchAll(EntitiesQuery::class, Product::getTableName());

        // Then
        $this->assertEquals([], $data);
    }

    public function testShouldReturnArrayWithOneProduct(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $product = $this->products->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3');
        $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());

        // When & Then
        $this->assertEquals(
            [$product->getWritableFormat()],
            $controller->fetchAll(EntitiesQuery::class, Product::getTableName()),
        );
    }

    public function testShouldReturnArrayWithTwoProducts(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $productOne = $this->products->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3');
        $this->dbClient->insert(Product::getTableName(), $productOne->getWritableFormat());
        $productTwo = $this->products->get('f3e56592-0bfd-4669-be39-6ac8ab5ac55f');
        $this->dbClient->insert(Product::getTableName(), $productTwo->getWritableFormat());

        // When & Then
        $this->assertEquals(
            [$productOne->getWritableFormat(), $productTwo->getWritableFormat()],
            $controller->fetchAll(EntitiesQuery::class, Product::getTableName()),
        );
    }

    public function testShouldReturnArrayWithOneProductWhenLimitIsOneAndOrderByNameDesc(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $criteria = DbalCriteria::createFromArray([
            'orderBy' => ['name' => 'DESC'],
            'limit' => 1,
        ]);

        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        // When & Then
        $this->assertEquals(
            [$this->products->get('f3e56592-0bfd-4669-be39-6ac8ab5ac55f')->getWritableFormat()],
            $controller->fetchAll(EntitiesQuery::class, Product::getTableName(), $criteria),
        );
    }

    public function testShouldReturnArrayWithTwoProductsWhenOffsetIsOneLimitIsTwoAndOrderByNameDesc(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $criteria = DbalCriteria::createFromArray([
            'orderBy' => ['name' => 'DESC'],
            'limit' => 2,
            'offset' => 1,
        ]);

        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        // When & Then
        $this->assertEquals(
            [
                $this->products->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3')->getWritableFormat(),
                $this->products->get('62d925ad-4ef7-47a9-be28-79d71534c099')->getWritableFormat(),
            ],
            $controller->fetchAll(EntitiesQuery::class, Product::getTableName(), $criteria),
        );
    }
}
