<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Utility\DoctrineApi;

use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Symfony\Component\HttpFoundation\Request;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;

class FindOneTest extends ProductTestCase
{
    public function testShouldReturnRequestedProduct()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $request = new Request([], [], ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));

        // When
        $response = $controller->getApi(Product::class)->findOne();
        $productData = json_decode($response->getContent(), true);

        // Then
        $this->assertEquals($this->products->get($uuid)->jsonSerialize(), $productData);
    }

    public function testShouldThrowExceptionNotFoundExceptionWhenRequestedProductDoesNotExist(): void
    {
        // Expect & Given
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf('Record "%s" not found', Product::class));
        $this->truncateTable(Product::getTableName());
        $uuid = '850186cc-9bac-44b8-a0f4-cea287290b8b';
        $request = new Request([], [], ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        // When & Then
        $controller->getApi(Product::class)->findOne();
    }
}
