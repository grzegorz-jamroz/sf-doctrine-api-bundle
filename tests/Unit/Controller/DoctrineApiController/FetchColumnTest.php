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
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        /** @var Product $product */
        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        // When & Then
        /** @var Product $product */
        foreach ($this->products as $product) {
            $name = $controller->fetchColumn(
                GetProductName::class,
                $product->getUuid()->getBytes()
            );
            $this->assertEquals($product->getName(), $name);
        }
    }

    public function testShouldReturnProperValuesInVariousTypes()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        // When & Then
        foreach ($this->products as $product) {
            $rate = $controller->fetchColumn(
                GetProductRate::class,
                $product->getUuid()->getBytes()
            );
            $this->assertEquals($product->getRate(), $rate);
        }
    }

    public function testShouldThrowNotFoundExceptionExceptionWhenRecordDoesNotExist()
    {
        // Expect & Given
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf('Record not found for query "%s"', GetProductName::class));
        $this->truncateTable(Product::getTableName());
        $controller = new DoctrineApiControllerVariant();
        $this->assertEquals([], $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        // When & Then
        /* @var Product $product */
        $controller->fetchColumn(
            GetProductName::class,
            $this->products->first()->getUuid()->getBytes()
        );
    }
}
