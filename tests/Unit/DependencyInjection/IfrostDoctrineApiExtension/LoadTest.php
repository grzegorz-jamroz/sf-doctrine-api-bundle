<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\DependencyInjection\IfrostDoctrineApiExtension;

use Ifrost\DoctrineApiBundle\DependencyInjection\Configuration;
use Ifrost\DoctrineApiBundle\DependencyInjection\IfrostDoctrineApiExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoadTest extends TestCase
{
    public function testShouldReturnConfiguration()
    {
        // When & Then
        $this->assertInstanceOf(Configuration::class, (new IfrostDoctrineApiExtension())->getConfiguration([], new ContainerBuilder()));
    }
}
