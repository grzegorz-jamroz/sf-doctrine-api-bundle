<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\Controller\DoctrineApiController;

use Doctrine\DBAL\Connection;
use Ifrost\DoctrineApiBundle\Tests\Variant\Controller\DoctrineApiControllerVariant;
use Ifrost\DoctrineApiBundle\Tests\Variant\Sample;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

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

    public function testShouldThrowRuntimeExceptionWhenContainerReturnInvalidConnection()
    {
        // Expect
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Container identifier "doctrine.dbal.default_connection" is not instance of %s (%s given).', Connection::class, gettype(new Sample())));

        // Given
        $controller = new DoctrineApiControllerVariant();
        $controller->getContainer()->set('doctrine.dbal.default_connection', new Sample());

        // When & Then
        $controller->getDbal();
    }
}
