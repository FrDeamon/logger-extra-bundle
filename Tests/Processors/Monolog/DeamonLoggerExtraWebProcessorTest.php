<?php

namespace Deamon\LoggerExtraBundle\Tests\Processors\Monolog;

use Deamon\LoggerExtraBundle\Processors\Monolog\DeamonLoggerExtraWebProcessor;
use Deamon\LoggerExtraBundle\Services\DeamonLoggerExtraContext;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\UserInterface;

class DeamonLoggerExtraWebProcessorTest extends TestCase
{
    public function testProcessorWithNullContainer()
    {
        $processor = new DeamonLoggerExtraWebProcessor();
        $originalRecord = $this->getRecord();
        $record = $processor->__invoke($originalRecord);

        $this->assertEquals($originalRecord, $record);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddContextInfo()
    {
        $config = $this->getDisplayConfig([
            'env' => true,
            'locale' => true,
            'application_name' => true,
        ]);

        $processor = new DeamonLoggerExtraWebProcessor($config);
        $processor->setLoggerExtraContext($this->getLoggerExtraContext());
        $processor->setEnvironment('env_foo');
        $record = $processor->__invoke($this->getRecord());

        $this->assertArrayHasKeyAndEquals('env', $record['extra'], 'env_foo');
        $this->assertArrayHasKeyAndEquals('locale', $record['extra'], 'fr');
        $this->assertArrayHasKeyAndEquals('application', $record['extra'], 'foo_app');
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddContextInfoWithoutLocale()
    {
        $config = $this->getDisplayConfig([
            'env' => true,
            'locale' => true,
            'application_name' => true,
        ]);

        $processor = new DeamonLoggerExtraWebProcessor($config);
        $processor->setLoggerExtraContext($this->getLoggerExtraContext(null));
        $processor->setEnvironment('env_foo');
        $record = $processor->__invoke($this->getRecord());

        $this->assertArrayHasKeyAndEquals('env', $record['extra'], 'env_foo');
        $this->assertArrayNotHasKey('locale', $record['extra'], 'fr');
        $this->assertArrayHasKeyAndEquals('application', $record['extra'], 'foo_app');
    }

    public function testAddRequestInfo()
    {
        $config = $this->getDisplayConfig(
            [
                'url' => true,
                'route' => true,
                'user_agent' => true,
                'accept_encoding' => true,
                'client_ip' => true,
            ]
        );

        $processor = new DeamonLoggerExtraWebProcessor($config);
        $processor->setRequestStack($this->getRequestStack());
        $record = $processor->__invoke($this->getRecord());

        $this->assertArrayHasKeyAndEquals('url', $record['extra'], 'requested_uri');
        $this->assertArrayHasKeyAndEquals('route', $record['extra'], 'requested_route');
        $this->assertArrayHasKeyAndEquals('user_agent', $record['extra'], 'user_agent_string');
        $this->assertArrayHasKeyAndEquals('accept_encoding', $record['extra'], 'Bar-Encoding');
        $this->assertArrayHasKeyAndEquals('client_ip', $record['extra'], '123.456.789.123');
    }

    public function testAddOnlyUserInfoOnDefinedClass()
    {
        $config = $this->getDisplayConfig([
            'user' => true,
        ], null, '\Deamon\LoggerExtraBundle\Tests\Processors\Monolog\MyUserWithAllFields', [
            'user_name' => 'getUsername',
        ]);

        $processor = new DeamonLoggerExtraWebProcessor($config);
        $processor->setTokenStorage($this->getTokenStorage(new MyUserWithOnlyUsername()));

        $record = $processor->__invoke($this->getRecord());

        // MyUserWithOnlyUsername does not implement the user_class so no extra logs
        $this->assertArrayNotHasKey('user_name', $record['extra']);
    }

    public function testAddUserInfoWithNotExistingClass()
    {
        $config = $this->getDisplayConfig([
            'user' => true,
        ], null, 'NotExistingUserClass');

        $processor = new DeamonLoggerExtraWebProcessor($config);
        $record = $processor->__invoke($this->getRecord());

        $this->assertArrayNotHasKey('user_name', $record['extra']);
    }

    public function testAddUserinfoWithNoTokenStorage()
    {
        $config = $this->getDisplayConfig([
            'user' => true,
        ]);

        $processor = new DeamonLoggerExtraWebProcessor($config);
        $record = $processor->__invoke($this->getRecord());

        $this->assertArrayNotHasKey('user_name', $record['extra']);
    }

    public function testAddUserInfo()
    {
        $config = $this->getDisplayConfig([
            'user' => true,
        ], null, 'Deamon\LoggerExtraBundle\Tests\Processors\Monolog\MyUserWithAllFields', [
            'user_name' => 'getUsername',
            'user_email' => 'getEmail',
            'user_id' => 'getId',
        ]);

        $processor = new DeamonLoggerExtraWebProcessor($config);
        $processor->setTokenStorage($this->getTokenStorage(new MyUserWithAllFields()));
        $record = $processor->__invoke($this->getRecord());

        $this->assertArrayHasKeyAndEquals('user_id', $record['extra'], 1);
        $this->assertArrayHasKeyAndEquals('user_email', $record['extra'], 'foo@acme.com');
        $this->assertArrayHasKeyAndEquals('user_name', $record['extra'], 'foo');
    }

    public function testAddChannelInfoWithoutChannelPrefix()
    {
        $config = $this->getDisplayConfig(['global_channel' => true]);
        $processor = new DeamonLoggerExtraWebProcessor($config);

        $originalRecord = $this->getRecord();
        $record = $processor->__invoke($originalRecord);

        $this->assertArrayHasKeyAndEquals('global_channel', $record['extra'], $originalRecord['channel']);
    }

    public function testAddChannelInfoWithChannelPrefix()
    {
        $config = $this->getDisplayConfig(['global_channel' => true], 'prefix');
        $processor = new DeamonLoggerExtraWebProcessor($config);
        $originalRecord = $this->getRecord();
        $record = $processor->__invoke($originalRecord);

        $this->assertArrayHasKeyAndEquals('global_channel', $record['extra'], $originalRecord['channel']);
        $this->assertArrayHasKeyAndEquals('channel', $record, sprintf('prefix.%s', $originalRecord['channel']));
    }

    protected function getDisplayConfig($trueValues, $channelPrefix = null, $user_class = null, $user_methods = null)
    {
        if (null === $user_class) {
            $user_class = '\Symfony\Component\Security\Core\User\UserInterface';
        }

        if (!is_array($user_methods)) {
            $user_methods = [
                'user_name' => 'getUsername',
            ];
        }
        $ret = array_merge(
            [
                'env' => false,
                'locale' => false,
                'application_name' => false,
                'url' => false,
                'route' => false,
                'user_agent' => false,
                'accept_encoding' => false,
                'client_ip' => false,
                'user' => false,
                'global_channel' => false,
            ],
            $trueValues
        );

        return [
            'channel_prefix' => $channelPrefix,
            'user_class' => $user_class,
            'user_methods' => $user_methods,
            'display' => $ret,
        ];
    }

    protected function assertArrayHasKeyAndEquals($key, $array, $value)
    {
        $this->assertArrayHasKey($key, $array);
        $this->assertEquals($value, $array[$key]);
    }

    /**
     * @param int    $level
     * @param string $message
     * @param array  $context
     *
     * @return array Record
     * @throws \Psr\Log\InvalidArgumentException
     */
    protected function getRecord($level = Logger::WARNING, $message = 'test', $context = array())
    {
        return array(
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'level_name' => Logger::getLevelName($level),
            'channel' => 'test',
            'datetime' => \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true))),
            'extra' => array(),
        );
    }

    private function getRequestStack()
    {
        $request = new Request([], [], [
            '_route' => 'requested_route',
        ], [], [], [
            'HTTP_ACCEPT-ENCODING' => 'Bar-Encoding',
            'HTTP_USER_AGENT' => 'user_agent_string',
            'REQUEST_URI' => 'requested_uri',
            'REMOTE_ADDR' => '123.456.789.123',
        ]);
        $stack = new RequestStack();
        $stack->push($request);

        return $stack;
    }

    private function getLoggerExtraContext($locale = 'fr')
    {
        return new DeamonLoggerExtraContext('foo_app', $locale);
    }

    private function getTokenStorage(UserInterface $user = null)
    {
        $storage = new TokenStorage();
        $storage->setToken(new MyToken($user));

        return $storage;
    }
}

class MyUserWithOnlyUsername implements UserInterface
{
    private $userName;

    public function __construct($userName = 'foo')
    {
        $this->userName = $userName;
    }

    public function getUsername()
    {
        return $this->userName;
    }

    public function getRoles()
    {
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }
}

class MyUserWithAllFields extends MyUserWithOnlyUsername
{
    private $id;
    private $email;

    public function __construct($id = 1, $email = 'foo@acme.com', $userName = 'foo')
    {
        parent::__construct($userName);
        $this->id = $id;
        $this->email = $email;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEmail()
    {
        return $this->email;
    }
}

class MyToken extends AbstractToken
{
    public function __construct($user = null)
    {
        parent::__construct();
        $this->setUser($user);
    }

    public function getCredentials()
    {
    }
}
