<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Query\DbalQuery;

use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product\GetAllProducts;
use Ifrost\Filesystem\Directory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

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
}
