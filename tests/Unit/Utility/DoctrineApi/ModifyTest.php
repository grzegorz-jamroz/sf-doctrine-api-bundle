<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Utility\DoctrineApi;

use Doctrine\DBAL\Exception;
use Ifrost\ApiBundle\Utility\ApiRequest;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Ifrost\DoctrineApiBundle\Utility\DoctrineApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Tests\Variant\Utility\DbClientSecondVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Utility\DbClientVariant;

class ModifyTest extends ProductTestCase
{
    public function testShouldModifyOnlyRequestedFieldsForProduct(): void
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
        $requestData = [
            'code' => 'EBG34F321',
            'name' => 'Headphones',
        ];
        $request = new Request([], $requestData, ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $controller->getApi(Product::class)->modify();

        // Then
        $productData = $this->dbClient->fetchOne(EntityQuery::class, Product::TABLE, $uuid);
        $this->assertEquals(
            [
                'uuid' => 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f',
                'code' => 'EBG34F321',
                'name' => 'Headphones',
                'description' => 'Shure',
                'tags' => [],
            ],
            Product::createFromArray($productData)->jsonSerialize()
        );
    }

    public function testShouldThrowNotFoundExceptionWhenTryingToModifyProductWhichDoesNotExist()
    {
        // Expect
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf('Record "%s" not found', Product::class));
        $this->truncateTable(Product::TABLE);

        // Given
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $requestData = [
            'name' => 'accordion',
        ];
        $request = new Request([], $requestData, ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        // When & Then
        $controller->getApi(Product::class)->modify();
    }

    public function testShouldThrowNotUniqueExceptionWhenTryingToUpdateProductWhichHasNotUniqueCode()
    {
        // Expect
        $this->expectException(NotUniqueException::class);
        $this->expectExceptionMessage(sprintf('Unable to modify "%s" due to not unique fields.', Product::class));
        $this->truncateTable(Product::TABLE);

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // Given
        $productData = [
            'code' => 'KHL324ED2',
            'name' => 'Headphones',
        ];
        $request = new Request([], $productData, ['uuid' => 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f']);
        $controller = new DoctrineApiControllerVariant($request);

        // When & Then
        $controller->getApi(Product::class)->modify();
    }

    public function testShouldThrowDbalExceptionWhenUnknownErrorOccurredDuringCreatingEntity()
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown error occurred in fetchOne');
        $controller = new DoctrineApiControllerVariant();
        $dbClient = new DbClientSecondVariant($controller->getDbal());
        $requestStack = new RequestStack();
        $requestStack->push(new Request());

        // When & Then
        (new DoctrineApi(Product::class, $dbClient, new ApiRequest($requestStack)))->modify();
    }

    public function testShouldThrowDbalExceptionWhenUnknownErrorOccurred()
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown error occurred');
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $this->dbClient->insert(Product::TABLE, $this->productsData->get($uuid));
        $productData = $this->dbClient->fetchOne(EntityQuery::class, Product::TABLE, $uuid);
        $this->assertEquals(
            $this->products->get($uuid)->jsonSerialize(),
            Product::createFromArray($productData)->jsonSerialize()
        );
        $controller = new DoctrineApiControllerVariant();
        $dbClient = new DbClientVariant($controller->getDbal());
        $requestStack = new RequestStack();
        $requestData = [
            'name' => 'Headphones',
        ];
        $requestStack->push(new Request([], $requestData, ['uuid' => $uuid]));

        // When & Then
        (new DoctrineApi(Product::class, $dbClient, new ApiRequest($requestStack)))->modify();
    }
}
