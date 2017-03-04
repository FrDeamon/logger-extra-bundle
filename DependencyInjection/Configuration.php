<?php

namespace Deamon\LoggerExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('deamon_logger_extra');

        $rootNode->children()
                ->arrayNode('application')->isRequired()
                    ->children()
                        ->scalarNode('name')->defaultNull()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('handlers')->isRequired()
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function($v) { return array($v); })
                    ->end()
                    ->prototype('scalar')->isRequired()->cannotBeEmpty()->end()
                ->end()
                ->arrayNode('config')->isRequired()->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('channel_prefix')->defaultNull()->cannotBeEmpty()->end()
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
                                ->booleanNode('user_id')->defaultTrue()->end()
                                ->booleanNode('user_email')->defaultTrue()->end()
                                ->booleanNode('user_name')->defaultTrue()->end()
                                ->booleanNode('global_channel')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ;

        return $treeBuilder;
    }
}
