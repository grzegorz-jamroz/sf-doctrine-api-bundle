<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\DependencyInjection;

use PlainDataTransformer\Transform;
use Psr\Cache\CacheItemPoolInterface;
use Ramsey\Uuid\Doctrine\UuidType;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
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
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (Transform::toBool($config['doctrine_dbal_types_uuid'])) {
            $container->prependExtensionConfig('doctrine', ['dbal' => ['types' => ['uuid' => UuidType::class]]]);
        }

        $this->setDbalCacheDir($config, $container);
        $this->setDbalCacheAdapter($config, $container);
    }

    /**
     * @param array<int|string, mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }

    /**
     * @param array<string, mixed> $config
     */
    private function setDbalCacheDir(array $config, ContainerBuilder $container): void
    {
        $cacheDir = Transform::toString($config['dbal_cache_dir']);

        if ($cacheDir === '') {
            return;
        }

        if ($cacheDir === 'default') {
            $kernelCacheDir = Transform::toString($container->getParameter('kernel.cache_dir'));
            $container->setParameter('ifrost_doctrine_api.dbal_cache_dir', sprintf('%s/doctrine/dbal', $kernelCacheDir));

            return;
        }

        $container->setParameter('ifrost_doctrine_api.dbal_cache_dir', $cacheDir);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function setDbalCacheAdapter(array $config, ContainerBuilder $container): void
    {
        $adapter = $config['dbal_cache_adapter'] ?? '';

        if ($adapter === '') {
            return;
        }

        if ($adapter === 'default') {
            try {
                $cacheDir = Transform::toString($container->getParameter('ifrost_doctrine_api.dbal_cache_dir'));
                $container->set('ifrost_doctrine_api.dbal_cache_adapter', new FilesystemAdapter('', 0, $cacheDir));

                return;
            } catch (\Exception) {
                return;
            }
        }

        $adapter instanceof CacheItemPoolInterface ?: throw new RuntimeException(sprintf('"ifrost_doctrine_api.dbal_cache_adapter" is not instance of %s (%s given).', CacheItemPoolInterface::class, gettype($adapter)));
        $container->set('ifrost_doctrine_api.dbal_cache_adapter', $adapter);
    }
}
