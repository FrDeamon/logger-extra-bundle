<?php

namespace DependencyInjection;

use Deamon\LoggerExtraBundle\DependencyInjection\DeamonLoggerExtraExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class DeamonLoggerExtraExtensionTest extends TestCase
{
    /**
     * @var DeamonLoggerExtraExtension
     */
    private $extension;

    /**
     * @var string
     */
    private $root;

    /**
     * @var ContainerBuilder
     */
    private $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension = new DeamonLoggerExtraExtension();
        $this->root = 'deamon_logger_extra';
        $this->container = new ContainerBuilder();
    }

    public function testLoad(): void
    {
        $configs = [
            $this->getValidConfigFull(),
        ];
        $this->extension->load($configs, $this->container);

        $this->assertTrue($this->container->hasDefinition('deamon.logger_extra.context'));
        $this->assertTrue($this->container->hasDefinition('deamon.logger_extra.processors.web_processor'));

        $definition1 = $this->container->getDefinition('deamon.logger_extra.context');
        $this->assertEquals('foo', $definition1->getArgument(0));
        $this->assertCount(3, $definition1->getArguments());
        $this->assertEquals('fr', $definition1->getArgument(1));
        $this->assertEquals('barVersion', $definition1->getArgument(2));

        $definition2 = $this->container->getDefinition('deamon.logger_extra.processors.web_processor');
        $this->assertEquals($configs[0]['config'], $definition2->getArgument(0));

        $this->assertTrue($definition2->hasTag('monolog.processor'));
        $tag = $definition2->getTag('monolog.processor');
        $this->assertCount(1, $tag);
        $this->assertEquals('bar', $tag[0]['handler']);
    }

    public function testDefaultValue(): void
    {
        $configs = [
            $this->getValidConfigMin(),
        ];

        $defaultConfigValues = [
            'channel_prefix' => null,
            'user_class' => null,
            'user_methods' => [
                'user_name' => 'getUsername',
            ],
            'display' => [
                'env' => true,
                'locale' => true,
                'application_name' => true,
                'application_version' => true,
                'url' => true,
                'route' => true,
                'user_agent' => true,
                'accept_encoding' => true,
                'client_ip' => true,
                'user' => true,
                'global_channel' => false,
            ],
        ];
        $this->extension->load($configs, $this->container);

        $this->assertTrue($this->container->hasDefinition('deamon.logger_extra.context'));
        $this->assertTrue($this->container->hasDefinition('deamon.logger_extra.processors.web_processor'));

        $definition1 = $this->container->getDefinition('deamon.logger_extra.context');
        $this->assertNull($definition1->getArgument(0));
        $this->assertCount(3, $definition1->getArguments());
        $this->assertNull($definition1->getArgument(1));
        $this->assertNull($definition1->getArgument(2));

        $definition2 = $this->container->getDefinition('deamon.logger_extra.processors.web_processor');
        $this->assertEquals($defaultConfigValues, $definition2->getArgument(0));
    }

    public function testConvertStringHandlerToArray(): void
    {
        $configs = [
            [
                'application' => null,
                'handlers' => 'bar',
                'config' => null,
            ],
        ];
        $this->extension->load($configs, $this->container);

        $this->assertTrue($this->container->hasDefinition('deamon.logger_extra.context'));
        $this->assertTrue($this->container->hasDefinition('deamon.logger_extra.processors.web_processor'));

        $definition = $this->container->getDefinition('deamon.logger_extra.processors.web_processor');

        $this->assertTrue($definition->hasTag('monolog.processor'));
        $tag = $definition->getTag('monolog.processor');
        $this->assertCount(1, $tag);
        $this->assertEquals('bar', $tag[0]['handler']);
    }

    public function testOptionalServiceReferencesUseIgnoreOnInvalidBehavior(): void
    {
        $loader = new YamlFileLoader(
            $this->container,
            new FileLocator(__DIR__ . '/../../Resources/config')
        );
        $loader->load('processors.yaml');

        $definition = $this->container->getDefinition('deamon.logger_extra.processors.web_processor');
        $methodCalls = $definition->getMethodCalls();

        $this->assertReferenceUsesIgnoreOnInvalidBehavior($methodCalls, 'setTokenStorage');
        $this->assertReferenceUsesIgnoreOnInvalidBehavior($methodCalls, 'setRequestStack');
    }

    /**
     * @return array
     */
    private function getValidConfigFull(): array
    {
        return [
            'application' => [
                'name' => 'foo',
                'locale' => 'fr',
                'version' => 'barVersion'
            ],
            'handlers' => ['bar'],
            'config' => [
                'channel_prefix' => 'barPrefix',
                'user_class' => '\Symfony\Component\Security\Core\User\UserInterface',
                'user_methods' => [
                    'user_name' => 'getUsername',
                ],
                'display' => [
                    'env' => true,
                    'locale' => true,
                    'application_name' => true,
                    'application_version' => true,
                    'url' => true,
                    'route' => true,
                    'user_agent' => true,
                    'accept_encoding' => true,
                    'client_ip' => true,
                    'user' => true,
                    'global_channel' => true,
                ],
            ],
        ];
    }

    private function getValidConfigMin(): array
    {
        return [
            'application' => null,
            'handlers' => ['bar'],
            'config' => null,
        ];
    }

    /**
     * @param array<int, array{0: string, 1: array<int, mixed>}> $methodCalls
     */
    private function assertReferenceUsesIgnoreOnInvalidBehavior(array $methodCalls, string $methodName): void
    {
        foreach ($methodCalls as $methodCall) {
            if ($methodCall[0] !== $methodName) {
                continue;
            }

            $this->assertCount(1, $methodCall[1]);
            $this->assertInstanceOf(Reference::class, $methodCall[1][0]);
            $this->assertSame(ContainerInterface::IGNORE_ON_INVALID_REFERENCE, $methodCall[1][0]->getInvalidBehavior());

            return;
        }

        $this->fail(sprintf('Method call "%s" was not found.', $methodName));
    }
}
