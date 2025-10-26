<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Entity\Product;
use Ifrost\DoctrineApiBundle\Utility\DbClient;
use Ifrost\DoctrineApiBundle\Utility\TransformRecord;
use Ifrost\Filesystem\JsonFile;
use PHPUnit\Framework\TestCase;
use PlainDataTransformer\TransformNumeric;
use Symfony\Component\Uid\Uuid;

class ProductTestCase extends TestCase
{
    protected Connection $dbal;
    protected DbClient $dbClient;
    protected ArrayCollection $productsData;
    protected ArrayCollection $products;

    protected function setUp(): void
    {
        $controller = new DoctrineApiControllerVariant();
        $this->dbal = $controller->getDbal();
        $this->dbClient = new DbClient($this->dbal);
        $testDirectoryPath = sprintf('%s/products', TESTS_DATA_DIRECTORY);
        $productUuids = [
            '62d925ad-4ef7-47a9-be28-79d71534c099',
            '8b40a6d6-1a79-4edc-bfca-0f8d993c29f3',
            'f3e56592-0bfd-4669-be39-6ac8ab5ac55f',
            'fe687d4a-a5fc-426b-ba15-13901bda54a6',
        ];
        $this->productsData = array_reduce(
            $productUuids,
            function (ArrayCollection $acc, string $uuid) use ($testDirectoryPath) {
                $productData = array_map(
                    function (mixed $value) {
                        if (is_array($value)) {
                            return json_encode($value);
                        }

                        return $value;
                    },
                    (new JsonFile(sprintf('%s/%s.json', $testDirectoryPath, $uuid)))->read(),
                );
                $acc->set($uuid, $productData);

                return $acc;
            },
            new ArrayCollection(),
        );
        $this->products = array_reduce(
            $this->productsData->toArray(),
            function (ArrayCollection $acc, array $productData) {
                $productData = array_map(
                    fn (mixed $value) => TransformRecord::toRead($value),
                    $productData,
                );
                $acc->set(
                    $productData['uuid'],
                    Product::createFromArray(
                        [
                            ...$productData,
                            'uuid' => Uuid::fromString($productData['uuid']),
                            'rate' => TransformNumeric::toInt($productData['rate'] ?? 0, 2),
                        ],
                    ),
                );

                return $acc;
            },
            new ArrayCollection(),
        );
        parent::setUp();
    }

    protected function truncateTable(string $tableName): void
    {
        $this->dbal->executeStatement("TRUNCATE TABLE $tableName");
    }
}
