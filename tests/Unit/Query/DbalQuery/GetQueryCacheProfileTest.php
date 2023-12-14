<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Query\DbalQuery;

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product\GetAllProductsByName;
use Ifrost\DoctrineApiBundle\Tests\Variant\Query\Product\GetAllProductsByNameWithCache;
use PHPUnit\Framework\TestCase;
use PlainDataTransformer\Transform;

class GetQueryCacheProfileTest extends TestCase
{
    private Connection $dbal;

    protected function setUp(): void
    {
        $this->dbal = DriverManager::getConnection([
            'url' => Transform::toString($_ENV['DATABASE_URL'] ?? ''),
        ]);
    }

    public function testShouldAllowToSetNull()
    {
        // Expect & Given
        $query = new GetAllProductsByName($this->dbal, 'guitar');

        // When & Then
        $this->assertEquals(null, $query->getQueryCacheProfile());
    }

    public function testShouldSetInstanceOfQueryCacheProfile()
    {
        // Expect & Given
        $query = new GetAllProductsByNameWithCache($this->dbal, 'guitar');

        // When & Then
        $this->assertInstanceOf(QueryCacheProfile::class, $query->getQueryCacheProfile());
    }
}
