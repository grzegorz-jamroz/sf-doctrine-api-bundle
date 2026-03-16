<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Query;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Uid\Uuid;
use Throwable;

abstract class DbalQuery extends QueryBuilder
{
    protected bool $useCamelCase = false;

    public function __construct(private Connection $connection)
    {
        parent::__construct($connection);
        $this->prepareQuery();
    }

    public function fetchFirstColumn(): array
    {
        return array_map(
            fn(array $row) => $this->transformKeyCase(
                $this->convertJsonToArray(
                    $this->convertBinaryUuids($row)
                )
            ),
            $this->executeQuery()->fetchFirstColumn(),
        );
    }

    /**
     * @return array<string, mixed>|false
     */
    public function fetchAssociative(): array|false
    {
        $result = $this->executeQuery()->fetchAssociative();

        if ($result === false) {
            return false;
        }

        return $this->transformKeyCase(
            $this->convertJsonToArray(
                $this->convertBinaryUuids($result)
            )
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchAllAssociative(): array
    {
        return array_map(
            fn(array $row) => $this->transformKeyCase(
                $this->convertJsonToArray(
                    $this->convertBinaryUuids($row)
                )
            ),
            $this->executeQuery()->fetchAllAssociative(),
        );
    }

    public function executeQuery(): Result
    {
        return $this->connection->executeQuery(
            $this->getSQL(),
            $this->getParameters(),
            $this->getParameterTypes(),
            $this->getResultCache() !== null ? $this->getQueryCacheProfile() : null,
        );
    }

    abstract protected function prepareQuery(): void;

    /**
     * @return string[]
     */
    public function getBinaryUuidFields(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getJsonFields(): array
    {
        return [];
    }

    public function getQueryCacheProfile(): ?QueryCacheProfile
    {
        return null;
    }

    /**
     * Gets the cache driver implementation that is used for query result caching.
     */
    protected function getResultCache(): ?CacheItemPoolInterface
    {
        return $this->connection->getConfiguration()->getResultCache();
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function convertBinaryUuids(array $row): array
    {
        foreach ($this->getBinaryUuidFields() as $field) {
            if (!isset($row[$field]) || $row[$field] === null) {
                continue;
            }

            try {
                $row[$field] = Uuid::fromBinary($row[$field])->toString();
            } catch (Throwable) {
            }
        }

        return $row;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function convertJsonToArray(array $row): array
    {
        foreach ($this->getJsonFields() as $field) {
            if (!isset($row[$field]) || $row[$field] === null) {
                continue;
            }

            try {
                $row[$field] = json_decode($row[$field], true, flags: JSON_THROW_ON_ERROR);
            } catch (Throwable) {
            }
        }

        return $row;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function transformKeyCase(array $row): array
    {
        if (!$this->useCamelCase) {
            return $row;
        }

        return $this->convertKeysToCamelCase($row);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function convertKeysToCamelCase(array $row): array
    {
        $result = [];

        foreach ($row as $key => $value) {
            $result[$this->snakeToCamelCase($key)] = $value;
        }

        return $result;
    }

    private function snakeToCamelCase(string $key): string
    {
        return lcfirst(
            str_replace('_', '', ucwords($key, '_'))
        );
    }
}
