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
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Tests\Variant\Utility\DbClientSecondVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Utility\DbClientVariant;

class UpdateTest extends ProductTestCase
{
    public function testShouldUpdateAllFieldsForRequestedProduct(): void
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $this->dbClient->insert(Product::getTableName(), $this->products->get($uuid)->getWritableFormat());
        $this->assertEquals(
            $this->products->get($uuid)->getWritableFormat(),
            $this->dbClient->fetchOne(EntityQuery::class, Product::getTableName(), Uuid::fromString($uuid)->getBytes())
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
        $this->assertEquals(
            [
                'uuid' => Uuid::fromString($uuid)->getBytes(),
                'code' => 'PDO79R564',
                'name' => 'accordion',
                'description' => '',
                'rate' => 0,
                'tags' => '[]',
            ],
            $this->dbClient->fetchOne(
                EntityQuery::class,
                Product::getTableName(),
                Uuid::fromString($uuid)->getBytes()
            )
        );
    }

    public function testShouldThrowNotFoundExceptionWhenTryingToUpdateProductWhichDoesNotExist()
    {
        // Expect
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(sprintf('Record "%s" not found', Product::class));
        $this->truncateTable(Product::getTableName());

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
        $this->truncateTable(Product::getTableName());

        foreach ($this->products as $product) {
            $this->dbClient->insert(Product::getTableName(), $product->getWritableFormat());
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::getTableName()));
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
        $this->truncateTable(Product::getTableName());
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown error occurred in fetchOne');
        $controller = new DoctrineApiControllerVariant();
        $dbClient = new DbClientSecondVariant($controller->getDbal());
        $requestStack = new RequestStack();
        $request = new Request([], [], ['uuid' => 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f']);
        $requestStack->push($request);

        // When & Then
        (new DoctrineApi(Product::class, $dbClient, new ApiRequest($requestStack), $controller->getEventDispatcher()))->update();
    }

    public function testShouldThrowDbalExceptionWhenUnknownErrorOccurred()
    {
        // Expect & Given
        $this->truncateTable(Product::getTableName());
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown error occurred');
        $uuid = 'f3e56592-0bfd-4669-be39-6ac8ab5ac55f';
        $this->dbClient->insert(Product::getTableName(), $this->products->get($uuid)->getWritableFormat());
        $this->assertEquals(
            $this->products->get($uuid)->getWritableFormat(),
            $this->dbClient->fetchOne(EntityQuery::class, Product::getTableName(), Uuid::fromString($uuid)->getBytes())
        );
        $controller = new DoctrineApiControllerVariant();
        $dbClient = new DbClientVariant($controller->getDbal());
        $requestStack = new RequestStack();
        $requestData = [
            'name' => 'Headphones',
        ];
        $requestStack->push(new Request([], $requestData, ['uuid' => $uuid]));

        // When & Then
        (new DoctrineApi(Product::class, $dbClient, new ApiRequest($requestStack), $controller->getEventDispatcher()))->update();
    }
}
