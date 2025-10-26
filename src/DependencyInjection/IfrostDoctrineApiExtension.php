<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\DependencyInjection;

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
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ifrost_doctrine_api.db_client', $config['db_client'] ?? []);
        $container->setParameter('ifrost_doctrine_api.dbal_cache_adapter', $config['dbal_cache_adapter'] ?? []);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config'),
        );
        $loader->load('services.yaml');

        if ($container->getParameter('ifrost_doctrine_api.db_client')['enabled']) {
            $loader->load('ifrost_doctrine_api.db_client.yaml');
        }

        if ($container->getParameter('ifrost_doctrine_api.dbal_cache_adapter')['enabled']) {
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
