<?php

namespace Maba\Bundle\GentleForceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('maba_gentle_force');

        $children = $rootNode->children();

        $this->configureRedis($children->arrayNode('redis'));
        $this->configureLimits($children->arrayNode('limits'));

        return $treeBuilder;
    }

    private function configureRedis(ArrayNodeDefinition $node)
    {
        $builder = $node->children();
        $builder->scalarNode('host')->defaultValue('localhost');
        $builder->scalarNode('prefix')->defaultNull();
    }

    private function configureLimits(ArrayNodeDefinition $node)
    {
        /** @var ArrayNodeDefinition $limitsPrototype */
        $limitsPrototype = $node->useAttributeAsKey('name')->prototype('array');

        /** @var ArrayNodeDefinition $limitPrototype */
        $limitPrototype = $limitsPrototype->prototype('array');
        $limitPrototype->validate()->ifTrue(function ($nodeConfig) {
            return isset($nodeConfig['bucketed_usages']) && isset($nodeConfig['bucketed_period']);
        })->thenInvalid('Only one of bucketed_usages or bucketed_period must be provided');

        $limitChildren = $limitPrototype->children();
        $limitChildren->scalarNode('max_usages')->isRequired();
        $limitChildren->scalarNode('period')->isRequired();
        $limitChildren->scalarNode('bucketed_usages');
        $limitChildren->scalarNode('bucketed_period');
    }
}