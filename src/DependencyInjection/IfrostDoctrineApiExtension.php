<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\DependencyInjection;

use Exception;
use PlainDataTransformer\Transform;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class IfrostDoctrineApiExtension extends Extension
{
    /**
     * @param array<int|string, mixed> $configs
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);
        $dbClient = Transform::toArray($config['db_client'] ?? []);
        $dbalCacheAdapter = Transform::toArray($config['dbal_cache_adapter'] ?? []);
        $container->setParameter('ifrost_doctrine_api.db_client', $dbClient);
        $container->setParameter('ifrost_doctrine_api.dbal_cache_adapter', $dbalCacheAdapter);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config'),
        );
        $loader->load('services.yaml');

        if ($dbClient['enabled'] ?? true) {
            $loader->load('ifrost_doctrine_api.db_client.yaml');
        }

        if ($dbalCacheAdapter['enabled'] ?? false) {
            $loader->load('ifrost_doctrine_api.dbal_cache_adapter.yaml');
        }
    }

    /**
     * @param array<int|string, mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }
}
