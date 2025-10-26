<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Utility;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Query\DbalQuery;

interface DbClientInterface
{
    /**
     * Returns the first value of the next row of the result or FALSE if there are no more rows.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function fetchColumn(string|DbalQuery $query, mixed ...$params): mixed;

    /**
     * Returns an array containing the values of the first column of the result.
     *
     * @return list<mixed>
     *
     * @throws Exception
     */
    public function fetchFirstColumn(string|DbalQuery $query, mixed ...$params): array;

    /**
     * Returns the next row of the result as an associative array or FALSE if there are no more rows.
     *
     * @return array<string,mixed>
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function fetchOne(string|DbalQuery $query, mixed ...$params): array;

    /**
     * Returns an array containing all of the result rows represented as associative arrays.
     *
     * @return list<array<string,mixed>>
     *
     * @throws Exception
     */
    public function fetchAll(string|DbalQuery $query, mixed ...$params): array;

    /**
     * Inserts a table row with specified data.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param array<string, mixed>            $data  Column-value pairs
     * @param array<int<0, max>, string|Type> $types Parameter types
     *
     * @throws Exception
     */
    public function insert(string $table, array $data, array $types = []): void;

    /**
     * Executes an SQL UPDATE statement on a table.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param array<string, mixed>            $data     Column-value pairs
     * @param array<string, mixed>            $criteria Update criteria
     * @param array<int<0, max>, string|Type> $types    Parameter types
     *
     * @throws Exception
     */
    public function update(string $table, array $data, array $criteria, array $types = []): void;

    /**
     * Executes an SQL DELETE statement on a table.
     *
     * Table expression and columns are not escaped and are not safe for user-input.
     *
     * @param array<string, mixed>            $criteria Deletion criteria
     * @param array<int<0, max>, string|Type> $types    Parameter types
     *
     * @throws Exception
     */
    public function delete(string $table, array $criteria, array $types = []): void;
}
