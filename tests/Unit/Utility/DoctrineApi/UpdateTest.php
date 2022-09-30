<?php

declare(strict_types=1);

namespace Tests\Unit\Utility\DoctrineApi;

use Doctrine\DBAL\Exception;
use Ifrost\ApiBundle\Utility\ApiRequest;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Ifrost\DoctrineApiBundle\Utility\DoctrineApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\Unit\ProductTestCase;
use Tests\Variant\Controller\DoctrineApiControllerVariant;
use Tests\Variant\Entity\Product;
use Tests\Variant\Utility\DbClientSecondVariant;
use Tests\Variant\Utility\DbClientVariant;

class UpdateTest extends ProductTestCase
{
    public function testShouldUpdateAllFieldsForRequestedProduct(): void
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
            'code' => 'PDO79R564',
            'name' => 'accordion',
        ];
        $request = new Request([], $requestData, ['uuid' => $uuid]);
        $controller = new DoctrineApiControllerVariant($request);

        // When
        $controller->getApi(Product::class)->update();

        // Then
        $productData = $this->dbClient->fetchOne(EntityQuery::class, Product::TABLE, $uuid);
        $this->assertEquals(
            [
                'uuid' => 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f',
                'code' => 'PDO79R564',
                'name' => 'accordion',
                'description' => '',
            ],
            Product::createFromArray($productData)->jsonSerialize()
        );
    }

    public function testShouldThrowNotFoundExceptionWhenTryingToUpdateProductWhichDoesNotExist()
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
        $controller->getApi(Product::class)->update();
    }

    public function testShouldThrowNotUniqueExceptionWhenTryingToUpdateProductWhichHasNotUniqueCode()
    {
        // Expect & Given
        $this->expectException(NotUniqueException::class);
        $this->expectExceptionMessage(sprintf('Unable to update "%s" due to not unique fields.', Product::class));
        $this->truncateTable(Product::TABLE);

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));
        $productData = [
            'code' => 'KHL324ED2',
            'name' => 'accordion',
            'description' => 'latin/folk',
        ];
        $request = new Request([], $productData, ['uuid' => 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f']);
        $controller = new DoctrineApiControllerVariant($request);

        // When & Then
        $controller->getApi(Product::class)->update();
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
        (new DoctrineApi(Product::class, $dbClient, new ApiRequest($requestStack)))->update();
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
        (new DoctrineApi(Product::class, $dbClient, new ApiRequest($requestStack)))->update();
    }
}
