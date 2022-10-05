<?php

declare(strict_types=1);

namespace Tests\Unit\Utility\DoctrineApi;

use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Symfony\Component\HttpFoundation\Request;
use Tests\Unit\ProductTestCase;
use Tests\Variant\Controller\DoctrineApiControllerVariant;
use Tests\Variant\Entity\Product;

class FindOneTest extends ProductTestCase
{
    public function testShouldReturnRequestedProduct()
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $request = new Request([], [], ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When
        $response = $controller->getApi(Product::class)->findOne();
        $productData = json_decode($response->getContent(), true);

        // Then
        $this->assertEquals($this->productsData->get($uuid), $productData);
    }

    public function testShouldThrowExceptionNotFoundExceptionWhenRequestedProductDoesNotExist(): void
    {
        // Expect & Given
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf('Record "%s" not found', Product::class));
        $this->truncateTable(Product::TABLE);
        $uuid = '850186cc-9bac-44b8-a0f4-cea287290b8b';
        $request = new Request([], [], ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        // When & Then
        $controller->getApi(Product::class)->findOne();
    }
}
