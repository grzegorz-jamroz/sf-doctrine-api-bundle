<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Query\DbalQuery;

use Doctrine\DBAL\Cache\CacheException;
use Ifrost\Filesystem\Directory;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product\GetAllProducts;
use Ifrost\DoctrineApiBundle\Tests\Variant\Sample;

class GetCacheDirTest extends TestCase
{
    public function testShouldGenerateCacheUsingFilesystemAdapter()
    {
        // Expect & Given
        $cacheDir = sprintf(ABSPATH . '/var/doctrine/dbal');
        (new Directory($cacheDir))->delete();
        $this->assertDirectoryDoesNotExist($cacheDir);
        $controller = new DoctrineApiControllerVariant();
        $controller->getContainer()->set('ifrost_doctrine_api.dbal_cache_adapter', new FilesystemAdapter('', 0, $cacheDir));

        // When
        $controller->getDbClient()->fetchAll(GetAllProducts::class);

        // Then
        $this->assertEquals(1, (new Directory($cacheDir))->countDirectories());
    }

    public function testShouldThrowRuntimeExceptionWhenInvalidCacheAdapter()
    {
        // Expect & Given
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('DBAL Cache Adapter "ifrost_doctrine_api.dbal_cache_adapter" is not instance of %s (%s given).', CacheItemPoolInterface::class, gettype(new Sample())));
        $controller = new DoctrineApiControllerVariant();
        $controller->getContainer()->set('ifrost_doctrine_api.dbal_cache_adapter', new Sample());

        // When & Then
        $controller->getDbClient()->fetchAll(GetAllProducts::class);
    }

    public function testShouldThrowCacheExceptionWhenTryingToCacheQueryButNoCacheAdapterIsConfigured()
    {
        // Expect & Given
        $this->expectException(CacheException::class);
        $controller = new DoctrineApiControllerVariant();
        $controller->getContainer()->set('ifrost_doctrine_api.dbal_cache_adapter', null);

        // When & Then
        $controller->getDbClient()->fetchAll(GetAllProducts::class);
    }
}
