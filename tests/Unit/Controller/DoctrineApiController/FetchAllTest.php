<?php

declare(strict_types=1);

namespace Tests\Unit\Controller\DoctrineApiController;

use Ifrost\DoctrineApiBundle\Query\DbalCriteria;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Tests\Unit\ProductTestCase;
use Tests\Variant\Controller\DoctrineApiControllerVariant;
use Tests\Variant\Entity\Product;

class FetchAllTest extends ProductTestCase
{
    public function testShouldReturnEmptyArray(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();

        // When
        $data = $controller->fetchAll(EntitiesQuery::class, Product::TABLE);

        // Then
        $this->assertEquals([], $data);
    }

    public function testShouldReturnArrayWithOneProduct(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $product = $this->productsData->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3');
        $this->dbClient->insert(Product::TABLE, $product);

        // When
        $data = $controller->fetchAll(EntitiesQuery::class, Product::TABLE);

        // Then
        $this->assertEquals([$product], $data);
    }

    public function testShouldReturnArrayWithTwoProducts(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $productOne = $this->productsData->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3');
        $this->dbClient->insert(Product::TABLE, $productOne);
        $productTwo = $this->productsData->get('f3e56592-0bfd-4669-be39-6ac8ab5ac55f');
        $this->dbClient->insert(Product::TABLE, $productTwo);

        // When
        $data = $controller->fetchAll(EntitiesQuery::class, Product::TABLE);

        // Then
        $this->assertEquals([$productOne, $productTwo], $data);
    }

    public function testShouldReturnArrayWithOneProductWhenLimitIsOneAndOrderByNameDesc(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $criteria = DbalCriteria::createFromArray([
            'orderBy' => ['name' => 'DESC'],
            'limit' => 1,
        ]);

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When
        $data = $controller->fetchAll(EntitiesQuery::class, Product::TABLE, $criteria);

        // Then
        $this->assertEquals([$this->productsData->get('f3e56592-0bfd-4669-be39-6ac8ab5ac55f')], $data);
    }

    public function testShouldReturnArrayWithTwoProductsWhenOffsetIsOneLimitIsTwoAndOrderByNameDesc(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $criteria = DbalCriteria::createFromArray([
            'orderBy' => ['name' => 'DESC'],
            'limit' => 2,
            'offset' => 1,
        ]);

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When
        $data = $controller->fetchAll(EntitiesQuery::class, Product::TABLE, $criteria);

        // Then
        $this->assertEquals(
            [
                $this->productsData->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3'),
                $this->productsData->get('62d925ad-4ef7-47a9-be28-79d71534c099'),
            ],
            $data
        );
    }
}
