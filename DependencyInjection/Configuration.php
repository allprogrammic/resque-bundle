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
        $treeBuilder = new TreeBuilder('resque');
        $nodeBuilder = $treeBuilder->addDefaultsIfNotSet()->children();

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
            ->end()
            ->arrayNode('alert')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('subject')->defaultValue('Resque Alert')->end()
                    ->scalarNode('from')->defaultValue('sender@domain.com')->end()
                    ->scalarNode('to')->defaultValue('recipient@domain.com')->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
