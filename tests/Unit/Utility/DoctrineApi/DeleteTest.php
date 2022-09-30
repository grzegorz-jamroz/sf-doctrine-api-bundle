<?php

declare(strict_types=1);

namespace Tests\Unit\Utility\DoctrineApi;

use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Symfony\Component\HttpFoundation\Request;
use Tests\Unit\ProductTestCase;
use Tests\Variant\Controller\DoctrineApiControllerVariant;
use Tests\Variant\Entity\Product;

class DeleteTest extends ProductTestCase
{
    public function testShouldDeleteRequestedProduct(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $this->dbClient->insert(Product::TABLE, $this->productsData->get($uuid));
        $productData = $this->dbClient->fetchOne(EntityQuery::class, Product::TABLE, $uuid);
        $this->assertEquals(
            $this->products->get($uuid)->jsonSerialize(),
            Product::createFromArray($productData)->jsonSerialize()
        );
        $request = new Request([], [], ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $controller->getApi(Product::class)->delete();

        // Then
        $this->assertCount(0, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));
    }

    public function testShouldReturnEmptySuccessResponseWhenTryingToDeleteProductWhichDoesNotExist()
    {
        // Expect
        $this->truncateTable(Product::TABLE);

        // Given
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $request = new Request([], [], ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $response = $controller->getApi(Product::class)->delete();

        // Then
        $this->assertEquals([], json_decode($response->getContent(), true));
    }
}
