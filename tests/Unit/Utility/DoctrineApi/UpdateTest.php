<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Utility\DoctrineApi;

use Doctrine\DBAL\Exception;
use Ifrost\ApiBundle\Utility\ApiRequest;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Exception\NotUniqueException;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\DoctrineApiBundle\Query\Entity\EntityQuery;
use Ifrost\DoctrineApiBundle\Tests\Unit\ProductTestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Tests\Variant\Utility\DbClientSecondVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Utility\DbClientVariant;
use Ifrost\DoctrineApiBundle\Utility\DoctrineApi;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
            $this->dbClient->fetchOne(EntityQuery::class, Product::getTableName(), Uuid::fromString($uuid)->toBinary()),
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
                'uuid' => Uuid::fromString($uuid)->toBinary(),
                'code' => 'PDO79R564',
                'name' => 'accordion',
                'description' => '',
                'rate' => 0,
                'tags' => '[]',
            ],
            $this->dbClient->fetchOne(
                EntityQuery::class,
                Product::getTableName(),
                Uuid::fromString($uuid)->toBinary(),
            ),
        );
    }

    public function testShouldNotThrowAnyExceptionWhenTryingToUpdateProductWhichDoesNotExist()
    {
        // Expect
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
        $this->assertEquals(1, 1);
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
}
