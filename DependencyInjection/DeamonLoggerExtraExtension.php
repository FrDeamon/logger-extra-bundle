<?php

namespace Deamon\LoggerExtraBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DeamonLoggerExtraExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('processors.xml');

        $definition = $container->getDefinition('deamon.logger_extra.context');
        $definition->addArgument($config['application']['name']);
        $definition->addArgument($config['application']['locale']);
        $definition->addArgument($config['application']['version']);

        $definition = $container->getDefinition('deamon.logger_extra.processors.web_processor');
        $definition->addArgument($config['config']);

        $definition->clearTag('monolog.processor');
        foreach ($config['handlers'] as $handler) {
            $definition->addTag('monolog.processor', ['handler' => $handler]);
        }
    }
}
