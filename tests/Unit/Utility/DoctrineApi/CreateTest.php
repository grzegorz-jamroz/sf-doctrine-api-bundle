<?php

declare(strict_types=1);

namespace Tests\Unit\Utility\DoctrineApi;

use Doctrine\DBAL\Exception;
use Ifrost\ApiBundle\Utility\ApiRequest;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Ifrost\DoctrineApiBundle\Utility\DoctrineApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Unit\ProductTestCase;
use Tests\Variant\Controller\DoctrineApiControllerVariant;
use Tests\Variant\Entity\Product;
use Tests\Variant\Utility\DbClientVariant;

class CreateTest extends ProductTestCase
{
    public function testShouldCreateRequestedProduct(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $this->assertCount(0, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $product = $this->productsData->get($uuid);
        $request = new Request([], $product);
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $controller->getApi(Product::class)->create();

        // Then
        $productData = $controller->fetchOne(EntityQuery::class, Product::TABLE, $uuid);
        $this->assertEquals(
            $this->products->get($uuid)->jsonSerialize(),
            Product::createFromArray($productData)->jsonSerialize()
        );
    }

    public function testShouldThrowNotUniqueExceptionWhenTryingToCreateProductWhichHasNotUniqueUuid()
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
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
        $this->truncateTable(Product::TABLE);
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
        $this->truncateTable(Product::TABLE);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown error occurred');
        $controller = new DoctrineApiControllerVariant();
        $dbClient = new DbClientVariant($controller->getDbal());
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        // When & Then
        (new DoctrineApi(Product::class, $dbClient, new ApiRequest($requestStack)))->create();
    }
}
