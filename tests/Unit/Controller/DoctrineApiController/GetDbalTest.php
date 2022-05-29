<?php

declare(strict_types=1);

namespace Tests\Unit\Controller\DoctrineApiController;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Tests\Variant\Controller\DoctrineApiControllerVariant;
use Tests\Variant\Sample;

class GetDbalTest extends TestCase
{
    public function testShouldReturnInstanceOfConnection()
    {
        // Given
        $controller = new DoctrineApiControllerVariant();

        // When & Then
        $this->assertInstanceOf(Connection::class, $controller->getDbal());
        $this->assertInstanceOf(Connection::class, $controller->getDbal());
    }

    public function testShouldThrowRuntimeExceptionWhenContainerReturnInvalidDoctrineIdentifier()
    {
        // Expect
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Container identifier "doctrine" is not instance of %s (%s given).', ManagerRegistry::class, gettype(new Sample())));

        // Given
        $controller = new DoctrineApiControllerVariant();
        $controller->getContainer()->set('doctrine', new Sample());

        // When & Then
        $controller->getDbal();
    }

    public function testShouldThrowRuntimeExceptionWhenContainerReturnInvalidConnection()
    {
        // Expect
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Default dbal connection "doctrine.dbal.default_connection" is not instance of %s (%s given).', Connection::class, gettype(new Sample())));

        // Given
        $controller = new DoctrineApiControllerVariant();
        $controller->getContainer()->set('doctrine.dbal.default_connection', new Sample());

        // When & Then
        $controller->getDbal();
    }
}
