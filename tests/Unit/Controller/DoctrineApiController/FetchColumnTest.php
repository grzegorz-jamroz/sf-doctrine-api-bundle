<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Controller\DoctrineApiController;

use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use PlainDataTransformer\Transform;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product\GetProductName;
use Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product\GetProductRate;

class FetchColumnTest extends ProductTestCase
{
    public function testShouldReturnStringWithProductNameAsResult()
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        /** @var Product $product */
        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::TABLE, $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When & Then
        /** @var Product $product */
        foreach ($this->products as $product) {
            $name = $controller->fetchColumn(
                GetProductName::class,
                $product->getUuid()
            );
            $this->assertEquals($product->getName(), $name);
        }
    }

    public function testShouldReturnProperValuesInVariousTypes()
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When & Then
        foreach ($this->productsData as $productData) {
            $rate = $controller->fetchColumn(
                GetProductRate::class,
                $productData['uuid']
            );
            $this->assertEquals(Transform::toString($productData['rate']), Transform::toString($rate));
        }
    }

    public function testShouldThrowNotFoundExceptionExceptionWhenRecordDoesNotExist()
    {
        // Expect & Given
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf('Record not found for query "%s"', GetProductName::class));
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When & Then
        /* @var Product $product */
        $controller->fetchColumn(
            GetProductName::class,
            $this->products->first()->getUuid()
        );
    }
}
