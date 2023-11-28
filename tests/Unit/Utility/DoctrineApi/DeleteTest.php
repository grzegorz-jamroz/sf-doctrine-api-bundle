<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Utility\DoctrineApi;

use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;

class DeleteTest extends ProductTestCase
{
    public function testShouldDeleteRequestedProduct(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $this->dbClient->insert(Product::getTableName(), $this->products->get($uuid)->getWritableFormat());
        $this->assertEquals(
            $this->products->get($uuid)->getWritableFormat(),
            $this->dbClient->fetchOne(EntityQuery::class, Product::getTableName(), Uuid::fromString($uuid)->getBytes())
        );
        $request = new Request([], [], ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $controller->getApi(Product::class)->delete();

        // Then
        $this->assertCount(0, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
    }

    public function testShouldReturnEmptySuccessResponseWhenTryingToDeleteProductWhichDoesNotExist()
    {
        // Expect
        $this->truncateTable(Product::getTableName());

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
