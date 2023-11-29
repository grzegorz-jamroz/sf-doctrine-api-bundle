<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Utility\DoctrineApi;

use Doctrine\DBAL\Exception;
use Ifrost\ApiBundle\Utility\ApiRequest;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Ifrost\DoctrineApiBundle\Utility\DoctrineApi;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Tests\Variant\Utility\DbClientVariant;

class CreateTest extends ProductTestCase
{
    public function testShouldCreateRequestedProduct(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $this->assertCount(0, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $product = $this->productsData->get($uuid);
        $request = new Request([], $product);
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $controller->getApi(Product::class)->create();

        // Then
        $this->assertEquals(
            $this->products->get($uuid)->getWritableFormat(),
            $controller->fetchOne(EntityQuery::class, Product::getTableName(), Uuid::fromString($uuid)->getBytes())
        );
    }

    public function testShouldCreateRequestedProductWithoutGivenUuid(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $this->assertCount(0, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $productData = $this->productsData->get($uuid);
        unset($productData['uuid']);
        $request = new Request([], $productData);
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $response = $controller->getApi(Product::class)->create();

        // Then
        $this->assertEquals(
            array_filter(
                $this->products->get($uuid)->jsonSerialize(),
                fn (string $key) => $key !== 'uuid',
                ARRAY_FILTER_USE_KEY
            ),
            array_filter(
                json_decode($response->getContent(), true),
                fn (string $key) => $key !== 'uuid',
                ARRAY_FILTER_USE_KEY
            ),
        );
        $this->assertEquals(
            array_filter(
                $this->products->get($uuid)->getWritableFormat(),
                fn (string $key) => $key !== 'uuid',
                ARRAY_FILTER_USE_KEY
            ),
            array_filter(
                $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName())[0],
                fn (string $key) => $key !== 'uuid',
                ARRAY_FILTER_USE_KEY
            ),
        );
    }


    public function testShouldCreateProductWithoutTags(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $this->assertCount(0, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        $uuid = 'fe687d4a-a5fc-426b-ba15-13901bda54a6';
        $productData = $this->productsData->get($uuid);
        $productData = array_filter($productData, fn (string $key) => $key !== 'tags', ARRAY_FILTER_USE_KEY);
        $request = new Request([], $productData);
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $controller->getApi(Product::class)->create();

        // Then
        $this->assertEquals(
            $this->products->get($uuid)->getWritableFormat(),
            $controller->fetchOne(EntityQuery::class, Product::getTableName(), Uuid::fromString($uuid)->getBytes())
        );
    }

    public function testShouldCreateProductWithArrayOfThreeTags(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $this->assertCount(0, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
        $uuid = '8b40a6d6-1a79-4edc-bfca-0f8d993c29f3';
        $request = new Request([], $this->products->get($uuid)->jsonSerialize());
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $controller->getApi(Product::class)->create();

        // Then
        $this->assertEquals(
            $this->products->get($uuid)->getWritableFormat(),
            $controller->fetchOne(EntityQuery::class, Product::getTableName(), Uuid::fromString($uuid)->getBytes())
        );
    }

    public function testShouldThrowNotUniqueExceptionWhenTryingToCreateProductWhichHasNotUniqueUuid()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $this->expectException(NotUniqueException::class);
        $this->expectExceptionMessage(sprintf('Unable to create "%s" due to not unique fields.', Product::class));
        $productData = $this->productsData->get('f3e56592-0bfd-4669-be39-6ac8ab5ac55f');
        $request = new Request([], $productData);
        $controller = new DoctrineApiControllerVariant($request);

        // When & Then
        $controller->getApi(Product::class)->create();
        $controller->getApi(Product::class)->create();
    }

    public function testShouldThrowNotUniqueExceptionWhenTryingToCreateProductWhichHasNotUniqueCode()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $this->expectException(NotUniqueException::class);
        $this->expectExceptionMessage(sprintf('Unable to create "%s" due to not unique fields.', Product::class));
        $productOneData = $this->productsData->get('f3e56592-0bfd-4669-be39-6ac8ab5ac55f');
        $productTwoData = $this->productsData->get('f3e56592-0bfd-4669-be39-6ac8ab5ac55f');
        $productTwoData['uuid'] = '1e6c4129-30ba-4bfa-8d35-9df10ffcb1d1';

        // When & Then
        $request = new Request([], $productOneData);
        $controller = new DoctrineApiControllerVariant($request);
        $controller->getApi(Product::class)->create();

        $request = new Request([], $productTwoData);
        $controller = new DoctrineApiControllerVariant($request);
        $controller->getApi(Product::class)->create();
    }


    public function testShouldThrowDbalExceptionWhenUnknownErrorOccurred()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown error occurred');
        $controller = new DoctrineApiControllerVariant();
        $dbClient = new DbClientVariant($controller->getDbal());
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        // When & Then
        (new DoctrineApi(Product::class, $dbClient, new ApiRequest($requestStack), $controller->getEventDispatcher()))->create();
    }
}
