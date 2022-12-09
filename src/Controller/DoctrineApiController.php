<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Ifrost\ApiBundle\Controller\ApiController;
use Ifrost\ApiFoundation\ApiInterface;
use Ifrost\ApiFoundation\Attribute\Api;
use Ifrost\ApiFoundation\Traits\ApiControllerTrait;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Query\DbalQuery;
use Ifrost\DoctrineApiBundle\Utility\DbClient;
use Ifrost\DoctrineApiBundle\Utility\DoctrineApi;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class DoctrineApiController extends ApiController
{
    use ApiControllerTrait;

    protected Connection $dbal;
    protected DbClient $db;

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'doctrine' => '?' . ManagerRegistry::class,
        ]);
    }

    protected function getDbal(): Connection
    {
        if (isset($this->dbal)) {
            return $this->dbal;
        }

        $doctrine = $this->container->get('doctrine');
        $doctrine instanceof ManagerRegistry ?: throw new RuntimeException(sprintf('Container identifier "doctrine" is not instance of %s (%s given).', ManagerRegistry::class, gettype($doctrine)));
        $connection = $doctrine->getConnection();
        $connection instanceof Connection ?: throw new RuntimeException(sprintf('Default dbal connection "doctrine.dbal.default_connection" is not instance of %s (%s given).', Connection::class, gettype($connection)));
        try {
            $cache = $this->container->get('ifrost_doctrine_api.dbal_cache_adapter');
            $cache instanceof CacheItemPoolInterface ?: throw new RuntimeException(sprintf('DBAL Cache Adapter "ifrost_doctrine_api.dbal_cache_adapter" is not instance of %s (%s given).', CacheItemPoolInterface::class, gettype($cache)));
            $connection->getConfiguration()->setResultCache($cache);
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Exception) {
        }

        $this->dbal = $connection;

        return $this->dbal;
    }

    protected function getDbClient(): DbClient
    {
        if (!isset($this->db)) {
            $this->db = new DbClient($this->getDbal());
        }

        return $this->db;
    }

    /**
     * @throws NotFoundException
     * @throws Exception
     */
    protected function fetchColumn(string|DbalQuery $query, mixed ...$params): mixed
    {
        return $this->getDbClient()->fetchColumn($query, ...$params);
    }

    /**
     * @return list<mixed>
     *
     * @throws Exception
     */
    protected function fetchFirstColumn(string|DbalQuery $query, mixed ...$params): array
    {
        return $this->getDbClient()->fetchFirstColumn($query, ...$params);
    }

    /**
     * @return array<string,mixed>
     *
     * @throws NotFoundException
     * @throws Exception
     */
    protected function fetchOne(string|DbalQuery $query, mixed ...$params): array
    {
        return $this->getDbClient()->fetchOne($query, ...$params);
    }

    /**
     * @return list<array<string,mixed>>
     *
     * @throws Exception
     */
    protected function fetchAll(string|DbalQuery $query, mixed ...$params): array
    {
        return $this->getDbClient()->fetchAll($query, ...$params);
    }

    protected function getApi(string $entityClassName = ''): ApiInterface
    {
        if ($entityClassName !== '') {
            return new DoctrineApi($entityClassName, $this->getDbClient(), $this->getApiRequestService());
        }

        $attributes = (new \ReflectionClass(static::class))->getAttributes(Api::class, \ReflectionAttribute::IS_INSTANCEOF);
        $attributes[0] ?? throw new \RuntimeException(sprintf('Controller "%s" has to declare "%s" attribute.', static::class, Api::class));
        $attribute = $attributes[0]->newInstance();
        $entityClassName = $attribute->getEntity();

        return new DoctrineApi($entityClassName, $this->getDbClient(), $this->getApiRequestService());
    }
}
