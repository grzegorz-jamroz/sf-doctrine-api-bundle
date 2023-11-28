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
                `uuid` BINARY(16) NOT NULL COMMENT '(DC2Type:uuid_binary)', 
                `name` VARCHAR(255) NOT NULL,
                `code` VARCHAR(255) NOT NULL, 
                `description` LONGTEXT DEFAULT NULL,
                `rate` INT NOT NULL,
                `tags` JSON NOT NULL COMMENT '(DC2Type:json)', 
                UNIQUE INDEX UNIQ_D34A04AD77153098 (code), 
                PRIMARY KEY(uuid)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL;
        $controller->getDbal()->executeStatement($sql);
    }
}
