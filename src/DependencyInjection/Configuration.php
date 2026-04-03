<?php

namespace Hdev\ClicToPayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for ClicToPay Bundle.
 *
 * Simple mode (single account):
 *   clic_to_pay:
 *       user_name: '%env(CLICTOPAY_USER_NAME)%'
 *       password:  '%env(CLICTOPAY_PASSWORD)%'
 *       mode:      test # or prod
 *
 * Advanced mode (multiple accounts):
 *   clic_to_pay:
 *       accounts:
 *           main:
 *               user_name: '...'
 *               password:  '...'
 *               mode:      prod
 *           sandbox:
 *               user_name: '...'
 *               password:  '...'
 *               mode:      test
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('clic_to_pay');

        $treeBuilder->getRootNode()
            ->children()
                // Simple mode – single account
                ->scalarNode('user_name')->defaultNull()->end()
                ->scalarNode('password')->defaultNull()->end()
                ->enumNode('mode')
                    ->values(['test', 'prod'])
                    ->defaultValue('test')
                ->end()
                ->scalarNode('language')->defaultValue('fr')->end()
                ->scalarNode('currency')->defaultValue('788')->end()
                // Advanced mode – multiple named accounts
                ->arrayNode('accounts')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('user_name')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                            ->enumNode('mode')
                                ->values(['test', 'prod'])
                                ->defaultValue('test')
                            ->end()
                            ->scalarNode('language')->defaultValue('fr')->end()
                            ->scalarNode('currency')->defaultValue('788')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
