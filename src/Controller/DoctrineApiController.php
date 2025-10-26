<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Ifrost\ApiBundle\Controller\ApiController;
use Ifrost\ApiFoundation\ApiInterface;
use Ifrost\ApiFoundation\Attribute\ApiController as ApiControllerAttribute;
use Ifrost\ApiFoundation\Traits\ApiControllerTrait;
use Ifrost\DoctrineApiBundle\Exception\NotFoundException;
use Ifrost\DoctrineApiBundle\Query\DbalQuery;
use Ifrost\DoctrineApiBundle\Utility\DbClientInterface;
use Ifrost\DoctrineApiBundle\Utility\DoctrineApi;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DoctrineApiController extends ApiController
{
    use ApiControllerTrait;

    protected Connection $dbal;
    protected DbClientInterface $db;

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'doctrine' => '?' . ManagerRegistry::class,
            'doctrine.dbal.default_connection' => '?' . Connection::class,
            'ifrost_doctrine_api.db_client' => '?' . DbClientInterface::class,
            'event_dispatcher' => '?' . EventDispatcherInterface::class,
        ]);
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
            return new DoctrineApi(
                $entityClassName,
                $this->getDbClient(),
                $this->getApiRequestService(),
                $this->getMessageHandler(),
                $this->getEventDispatcher()
            );
        }

        $attributes = (new \ReflectionClass(static::class))->getAttributes(ApiControllerAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);
        $attributes[0] ?? throw new \RuntimeException(sprintf('Controller "%s" has to declare "%s" attribute.', static::class, ApiControllerAttribute::class));
        $attribute = $attributes[0]->newInstance();
        $entityClassName = $attribute->getEntity();

        return new DoctrineApi(
            $entityClassName,
            $this->getDbClient(),
            $this->getApiRequestService(),
            $this->getMessageHandler(),
            $this->getEventDispatcher()
        );
    }

    protected function getDbal(): Connection
    {
        if (isset($this->dbal)) {
            return $this->dbal;
        }

        $connection = $this->container->get('doctrine.dbal.default_connection');
        $connection instanceof Connection ?: throw new RuntimeException(sprintf('Container identifier "doctrine.dbal.default_connection" is not instance of %s (%s given).', Connection::class, gettype($connection)));
        $this->dbal = $connection;

        return $this->dbal;
    }

    protected function getDbClient(): DbClientInterface
    {
        if (isset($this->db)) {
            return $this->db;
        }

        $db = $this->container->get('ifrost_doctrine_api.db_client');
        $db instanceof DbClientInterface ?: throw new RuntimeException(sprintf('Container identifier "ifrost_doctrine_api.db_client" is not instance of %s', DbClientInterface::class));
        $this->db = $db;

        return $this->db;
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = $this->container->get('event_dispatcher');
        $eventDispatcher instanceof EventDispatcherInterface ?: throw new RuntimeException(sprintf('Container identifier "event_dispatcher" is not instance of %s', EventDispatcherInterface::class));

        return $eventDispatcher;
    }
}
