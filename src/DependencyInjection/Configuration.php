<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ifrost_doctrine_api');
        /** @var ArrayNodeDefinition $definition */
        $definition = $treeBuilder->getRootNode();
        $builder = $definition->children();
        $builder->booleanNode('doctrine_dbal_types_uuid')->defaultValue(true)->end();
        $builder->variableNode('dbal_cache_dir')->defaultValue(null)->end();
        $builder->variableNode('dbal_cache_adapter')->defaultValue(null)->end();

        return $treeBuilder;
    }
}
