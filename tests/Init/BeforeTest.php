<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Init;

use PHPUnit\Framework\TestCase;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;

class BeforeTest extends TestCase
{
    public function testShouldSetupEnvironmentBeforeAllTests()
    {
        $this->createTableIfNotExists();
        $this->assertEquals(1, 1);
    }

    protected function createTableIfNotExists(): void
    {
        $controller = new DoctrineApiControllerVariant();
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `product` (
              `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:uuid)',
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `description` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `rate` float DEFAULT NULL,
              PRIMARY KEY (`uuid`),
              UNIQUE KEY `UNIQ_D34A04AD77153098` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;
        $controller->getDbal()->executeStatement($sql);
    }
}
