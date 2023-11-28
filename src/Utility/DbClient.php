<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Utility;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Query\DbalQuery;
use Psr\Cache\CacheItemPoolInterface;

class DbClient implements DbClientInterface
{
    public function __construct(
        private Connection $connection,
        ?CacheItemPoolInterface $cache = null,
    )
    {
        $cache !== null && $this->connection->getConfiguration()->setResultCache($cache);
    }

    /**
     * Returns the first value of the next row of the result or FALSE if there are no more rows.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function fetchColumn(string|DbalQuery $query, mixed ...$params): mixed
    {
        $query = $this->getDbalQuery($query, ...$params);
        $result = $query->executeQuery();
        $result = $result->fetchOne();

        if (false === $result) {
            throw new NotFoundException(sprintf('Record not found for query "%s"', $query::class), 404);
        }

        return $result;
    }

    /**
     * Returns an array containing the values of the first column of the result.
     *
     * @return list<mixed>
     *
     * @throws Exception
     */
    public function fetchFirstColumn(string|DbalQuery $query, mixed ...$params): array
    {
        return $this->getDbalQuery($query, ...$params)->executeQuery()->fetchFirstColumn();
    }

    /**
     * Returns the next row of the result as an associative array or FALSE if there are no more rows.
     *
     * @return array<string,mixed>
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function fetchOne(string|DbalQuery $query, mixed ...$params): array
    {
        $query = $this->getDbalQuery($query, ...$params);
        $result = $query->executeQuery()->fetchAssociative();

        if (false === $result) {
            throw new NotFoundException(sprintf('Record not found for query "%s"', $query::class), 404);
        }

        return $result;
    }

    /**
     * Returns an array containing all of the result rows represented as associative arrays.
     *
     * @return list<array<string,mixed>>
     *
     * @throws Exception
     */
    public function fetchAll(string|DbalQuery $query, mixed ...$params): array
    {
        return $this->getDbalQuery($query, ...$params)->executeQuery()->fetchAllAssociative();
    }

    /**
     * Inserts a table row with specified data.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param array<string, mixed>                                                 $data  Column-value pairs
     * @param array<int, int|string|Type|null>|array<string, int|string|Type|null> $types Parameter types
     *
     * @throws Exception
     */
    public function insert(string $table, array $data, array $types = []): void
    {
        $this->connection->insert($table, $data, $types);
    }

    /**
     * Executes an SQL UPDATE statement on a table.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param array<string, mixed>                                                 $data     Column-value pairs
     * @param array<string, mixed>                                                 $criteria Update criteria
     * @param array<int, int|string|Type|null>|array<string, int|string|Type|null> $types    Parameter types
     *
     * @throws Exception
     */
    public function update(string $table, array $data, array $criteria, array $types = []): void
    {
        $this->connection->update($table, $data, $criteria, $types);
    }

    /**
     * Executes an SQL DELETE statement on a table.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param array<string, mixed>                                                 $criteria Deletion criteria
     * @param array<int, int|string|Type|null>|array<string, int|string|Type|null> $types    Parameter types
     *
     * @throws Exception
     */
    public function delete(string $table, array $criteria, array $types = []): void
    {
        $this->connection->delete($table, $criteria, $types);
    }

    private function getDbalQuery(string|DbalQuery $query, mixed ...$params): DbalQuery
    {
        if (is_string($query)) {
            $query = new $query(
                $this->connection,
                ...$params
            );
        }

        if (!$query instanceof DbalQuery) {
            throw new \RuntimeException(sprintf('Query is invalid. Query should be instance of %s (%s given).', DbalQuery::class, gettype($query)));
        }

        return $query;
    }
}
