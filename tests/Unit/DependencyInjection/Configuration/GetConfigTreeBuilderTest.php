<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\Tests\Unit\DependencyInjection\Configuration;

use Ifrost\DoctrineApiBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class GetConfigTreeBuilderTest extends TestCase
{
    public function testShouldReturnDefaultTreeBuilder()
    {
        // Given
        $children = ['dbal_cache_adapter'];
        $treeBuilder = (new Configuration())->getConfigTreeBuilder();

        // When & Then
        foreach ($children as $child) {
            $definition = $treeBuilder->getRootNode()->find($child);
            $this->assertInstanceOf(NodeDefinition::class, $definition);
        }
    }
}
