<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\DependencyInjection\IfrostDoctrineApiExtension;

use Ifrost\DoctrineApiBundle\DependencyInjection\IfrostDoctrineApiExtension;
use Ifrost\DoctrineApiBundle\Tests\Variant\Sample;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

class GetConfigurationTest extends TestCase
{
    public function testShouldLoadConfigWithAllOptionsDisabled()
    {
        // Expect
        $this->expectException(ParameterNotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent parameter "ifrost_doctrine_api.dbal_cache_dir".');

        // Given
        $configs = [
            'ifrost_doctrine_api' => [],
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

    public function testShouldLoadConfigWithDefaultCacheAdpater()
    {
        // Given
        $configs = [
            'ifrost_doctrine_api' => [
                'dbal_cache_adapter' => true,
            ],
        ];
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.cache_dir', sprintf('%s/var/cache/test', ABSPATH));

        // When
        (new IfrostDoctrineApiExtension())->load($configs, $containerBuilder);

        // Then
        $this->assertInstanceOf(FilesystemAdapter::class, $containerBuilder->get('ifrost_doctrine_api.dbal_cache_adapter'));
    }

    public function testShouldLoadConfigWithCustomCacheAdpater()
    {
        // Given
        $containerBuilder = new ContainerBuilder();
        $configs = [
            'ifrost_doctrine_api' => [
                'dbal_cache_adapter' => true,
            ],
        ];
        $containerBuilder->set('ifrost_doctrine_api.dbal_cache_adapter', new ArrayAdapter());

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
        $configs = [
            'ifrost_doctrine_api' => [
                'dbal_cache_adapter' => true,
            ],
        ];

        // When
        (new IfrostDoctrineApiExtension())->load($configs, $containerBuilder);

        // Then
        $this->assertInstanceOf(FilesystemAdapter::class, $containerBuilder->get('ifrost_doctrine_api.dbal_cache_adapter'));
    }

    public function testShouldThrowRuntimeExceptionWhenCacheAdapterIsInvalid()
    {
        // Expect
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage(sprintf('Invalid type for path "ifrost_doctrine_api.dbal_cache_adapter". Expected "array", but got "%s"', get_debug_type(new Sample())));

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
