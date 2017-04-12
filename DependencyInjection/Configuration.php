<?php

/*
 * This file is part of the AllProgrammic ResqueBunde package.
 *
 * (c) AllProgrammic SAS <contact@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllProgrammic\Bundle\ResqueBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $nodeBuilder = $treeBuilder->root('resque')->addDefaultsIfNotSet()->children();

        $nodeBuilder
            ->arrayNode('worker')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('sleeping')->defaultValue(5)->end()
                ->end()
            ->end()
            ->arrayNode('redis')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('dsn')->isRequired()->end()
                    ->scalarNode('prefix')->defaultValue('resque:')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
