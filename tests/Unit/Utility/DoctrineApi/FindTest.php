<?php

declare(strict_types=1);

namespace Tests\Unit\Utility\DoctrineApi;

use Ifrost\DoctrineApiBundle\Entity\EntityInterface;
use Ifrost\DoctrineApiBundle\Query\DbalOperator;
use Ifrost\DoctrineApiBundle\Query\Entity\EntitiesQuery;
use Ifrost\Filesystem\JsonFile;
use Symfony\Component\HttpFoundation\Request;
use Tests\Unit\ProductTestCase;
use Tests\Variant\Controller\DoctrineApiControllerVariant;
use Tests\Variant\Entity\Product;
use Tests\Variant\Sample;

class FindTest extends ProductTestCase
{
    public function testShouldReturnEmptyArray(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();

        // When
        $response = $controller->getApi(Product::class)->find();

        // Then
        $this->assertEquals([], json_decode($response->getContent(), true));
    }

    public function testShouldReturnArrayWithOneProduct(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $product = $this->productsData->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3');
        $this->dbClient->insert(Product::TABLE, $product);

        // When
        $response = $controller->getApi(Product::class)->find();

        // Then
        $this->assertEquals([$product], json_decode($response->getContent(), true));
    }

    public function testShouldReturnArrayWithTwoProducts(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $controller = new DoctrineApiControllerVariant();
        $productOne = $this->productsData->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3');
        $this->dbClient->insert(Product::TABLE, $productOne);
        $productTwo = $this->productsData->get('f3e56592-0bfd-4669-be39-6ac8ab5ac55f');
        $this->dbClient->insert(Product::TABLE, $productTwo);

        // When
        $response = $controller->getApi(Product::class)->find();

        // Then
        $this->assertEquals([$productOne, $productTwo], json_decode($response->getContent(), true));
    }

    public function testShouldReturnArrayWithOneProductWhenLimitIsOneAndOrderByNameDesc(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $params = [
            'orderBy' => ['name' => 'DESC'],
            'limit' => 1,
        ];
        $request = new Request([], $params);
        $controller = new DoctrineApiControllerVariant($request);

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When
        $response = $controller->getApi(Product::class)->find();
        $data = json_decode($response->getContent(), true);

        // Then
        $this->assertEquals([$this->productsData->get('f3e56592-0bfd-4669-be39-6ac8ab5ac55f')], $data);
    }

    public function testShouldReturnArrayWithTwoProductsWhenOffsetIsOneLimitIsTwoAndOrderByNameDesc(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $params = [
            'orderBy' => ['name' => 'DESC'],
            'limit' => 2,
            'offset' => 1,
        ];
        $request = new Request([], $params);
        $controller = new DoctrineApiControllerVariant($request);

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When
        $response = $controller->getApi(Product::class)->find();
        $data = json_decode($response->getContent(), true);

        // Then
        $this->assertEquals(
            [
                $this->productsData->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3'),
                $this->productsData->get('62d925ad-4ef7-47a9-be28-79d71534c099'),
            ],
            $data
        );
    }

    public function testShouldReturnArrayWithOneProductForGivenConditions(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $params = [
            'conditions' => [
                [
                    'field' => 'code',
                    'operator' => DbalOperator::EQ->value,
                    'value' => 'STY639PW1',
                ],
            ],
        ];
        $request = new Request([], $params);
        $controller = new DoctrineApiControllerVariant($request);

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When
        $response = $controller->getApi(Product::class)->find();
        $data = json_decode($response->getContent(), true);

        // Then
        $this->assertEquals(
            [
                $this->productsData->get('62d925ad-4ef7-47a9-be28-79d71534c099'),
            ],
            $data
        );
    }

    public function testShouldReturnArrayWithTwoProductsForGivenComplexCriteria(): void
    {
        // Expect & Given
        $this->truncateTable(Product::TABLE);
        $params = [
            'conditions' => (new JsonFile(sprintf('%s/conditions/criteria-2.json', TESTS_DATA_DIRECTORY)))->read(),
        ];
        $request = new Request([], $params);
        $controller = new DoctrineApiControllerVariant($request);

        foreach ($this->productsData as $productData) {
            $this->dbClient->insert(Product::TABLE, $productData);
        }

        $this->assertCount(4, $this->dbClient->fetchAll(EntitiesQuery::class, Product::TABLE));

        // When
        $response = $controller->getApi(Product::class)->find();
        $data = json_decode($response->getContent(), true);

        // Then
        $this->assertEquals(
            [
                $this->productsData->get('8b40a6d6-1a79-4edc-bfca-0f8d993c29f3'),
                $this->productsData->get('fe687d4a-a5fc-426b-ba15-13901bda54a6'),
            ],
            $data
        );
    }


    public function testShouldThrowInvalidArgumentExceptionWhenEntityClassNameIsNotInstanceOfEntityInterface(): void
    {
        // Expect & Given
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Given argument entityClassName (%s) has to implement "%s" interface.', Sample::class, EntityInterface::class));
        $controller = new DoctrineApiControllerVariant();

        // When & Then
        $controller->getApi(Sample::class)->findOne();
    }
}
