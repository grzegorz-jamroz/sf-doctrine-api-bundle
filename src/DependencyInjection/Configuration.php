<?php

declare(strict_types=1);

namespace Ifrost\DoctrineApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ifrost_doctrine_api');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->booleanNode('doctrine_dbal_types_uuid')
                    ->defaultValue(true)
                ->end()
                ->arrayNode('dbal_cache_adapter')
                    ->canBeEnabled()
                ->end()
                ->arrayNode('db_client')
                    ->canBeDisabled()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
