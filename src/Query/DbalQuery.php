<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Query;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Psr\Cache\CacheItemPoolInterface;

abstract class DbalQuery extends QueryBuilder
{
    public function __construct(private Connection $connection)
    {
        parent::__construct($connection);
        $this->prepareQuery();
    }

    public function executeQuery(): Result
    {
        return $this->connection->executeQuery(
            $this->getSQL(),
            $this->getParameters(),
            $this->getParameterTypes(),
            $this->getQueryCacheProfile()
        );
    }

    abstract protected function prepareQuery(): void;

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
}
