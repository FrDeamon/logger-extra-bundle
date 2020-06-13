<?php

namespace Deamon\LoggerExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('deamon_logger_extra');

        $treeBuilder->getRootNode()->children()
                ->arrayNode('application')->isRequired()
                    ->children()
                        ->scalarNode('name')->defaultNull()->cannotBeEmpty()->end()
                        ->scalarNode('locale')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('handlers')->isRequired()
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function($v) {
                            return array($v);
                        })
                    ->end()
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                ->end()
                ->arrayNode('config')->isRequired()->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('channel_prefix')->defaultNull()->end()
                        ->scalarNode('user_class')->defaultValue(null)->end()
                        ->arrayNode('user_methods')
                            ->useAttributeAsKey('value')
                            ->normalizeKeys(false)
                            ->defaultValue(array(
                                'user_name' => 'getUsername',
                            ))
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('display')->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('env')->defaultTrue()->end()
                                ->booleanNode('locale')->defaultTrue()->end()
                                ->booleanNode('application_name')->defaultTrue()->end()
                                ->booleanNode('url')->defaultTrue()->end()
                                ->booleanNode('route')->defaultTrue()->end()
                                ->booleanNode('user_agent')->defaultTrue()->end()
                                ->booleanNode('accept_encoding')->defaultTrue()->end()
                                ->booleanNode('client_ip')->defaultTrue()->end()
                                ->booleanNode('user')->defaultTrue()->end()
                                ->booleanNode('global_channel')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
