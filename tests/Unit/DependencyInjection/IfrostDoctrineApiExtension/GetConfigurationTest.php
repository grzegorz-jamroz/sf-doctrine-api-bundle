<?php

declare(strict_types=1);

namespace Tests\Unit\DependencyInjection\IfrostDoctrineApiExtension;

use Ifrost\DoctrineApiBundle\DependencyInjection\IfrostDoctrineApiExtension;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Ramsey\Uuid\Doctrine\UuidType;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Tests\Variant\Sample;

class GetConfigurationTest extends TestCase
{

    public function testShouldLoadConfigWithAllOptionsDisabled()
    {
        // Expect
        $this->expectException(ParameterNotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent parameter "ifrost_doctrine_api.dbal_cache_dir".');

        // Given
        $configs = [
            'ifrost_doctrine_api' => [
                'doctrine_dbal_types_uuid' => false,
            ],
        ];
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.cache_dir', sprintf('%s\var\cache\test', ABSPATH));

        // When
        (new IfrostDoctrineApiExtension())->load($configs, $containerBuilder);

        // Then
        $this->assertEquals([], $containerBuilder->getExtensionConfig('doctrine'));
        $this->assertFalse($containerBuilder->has('ifrost_doctrine_api.dbal_cache_adapter'));
        $containerBuilder->getParameter('ifrost_doctrine_api.dbal_cache_dir');
    }

    public function testShouldLoadConfigWithDefaultCacheAdpaterAndDefaultCacheDir()
    {
        // Given
        $configs = [
            'ifrost_doctrine_api' => [
                'doctrine_dbal_types_uuid' => true,
                'dbal_cache_adapter' => 'default',
                'dbal_cache_dir' => 'default',
            ],
        ];
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.cache_dir', sprintf('%s/var/cache/test', ABSPATH));
        $expectedCacheDir = sprintf('%s/doctrine/dbal', $containerBuilder->getParameter('kernel.cache_dir'));

        // When
        (new IfrostDoctrineApiExtension())->load($configs, $containerBuilder);

        // Then
        $this->assertEquals(UuidType::class, $containerBuilder->getExtensionConfig('doctrine')[0]['dbal']['types']['uuid']);
        $this->assertInstanceOf(FilesystemAdapter::class, $containerBuilder->get('ifrost_doctrine_api.dbal_cache_adapter'));
        $this->assertEquals($expectedCacheDir, $containerBuilder->getParameter('ifrost_doctrine_api.dbal_cache_dir'));
    }

    public function testShouldNotLoadDefaultCacheAdpaterWhenDefaultCacheDirIsNull()
    {
        // Given
        $configs = [
            'ifrost_doctrine_api' => [
                'dbal_cache_adapter' => 'default',
            ],
        ];
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.cache_dir', sprintf('%s\var\cache\test', ABSPATH));

        // When
        (new IfrostDoctrineApiExtension())->load($configs, $containerBuilder);

        // Then
        $this->assertFalse($containerBuilder->has('ifrost_doctrine_api.dbal_cache_adapter'));
    }

    public function testShouldLoadConfigWithCustomCacheAdpater()
    {
        // Given
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.cache_dir', sprintf('%s\var\cache\test', ABSPATH));
        $configs = [
            'ifrost_doctrine_api' => [
                'dbal_cache_adapter' => new ArrayAdapter(),
            ],
        ];

        // When
        (new IfrostDoctrineApiExtension())->load($configs, $containerBuilder);

        // Then
        $this->assertInstanceOf(ArrayAdapter::class, $containerBuilder->get('ifrost_doctrine_api.dbal_cache_adapter'));
    }

    public function testShouldLoadConfigWithDefaultCacheAdpaterAndCustomCacheDir()
    {
        // Given
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.cache_dir', sprintf('%s\var\cache\test', ABSPATH));
        $expectedCacheDir = sprintf('%s/doctrine/dbal/special', $containerBuilder->getParameter('kernel.cache_dir'));
        $configs = [
            'ifrost_doctrine_api' => [
                'dbal_cache_adapter' => 'default',
                'dbal_cache_dir' => $expectedCacheDir,
            ],
        ];

        // When
        (new IfrostDoctrineApiExtension())->load($configs, $containerBuilder);

        // Then
        $this->assertInstanceOf(FilesystemAdapter::class, $containerBuilder->get('ifrost_doctrine_api.dbal_cache_adapter'));
        $this->assertEquals($expectedCacheDir, $containerBuilder->getParameter('ifrost_doctrine_api.dbal_cache_dir'));
    }

    public function testShouldThrowRuntimeExceptionWhenCacheAdapterIsInvalid()
    {
        // Expect
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('"ifrost_doctrine_api.dbal_cache_adapter" is not instance of %s (%s given).', CacheItemPoolInterface::class, gettype(new Sample())));

        // Given
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.cache_dir', sprintf('%s\var\cache\test', ABSPATH));
        $configs = [
            'ifrost_doctrine_api' => [
                'dbal_cache_adapter' => new Sample(),
            ],
        ];

        // When & Then
        (new IfrostDoctrineApiExtension())->load($configs, $containerBuilder);
    }
}
