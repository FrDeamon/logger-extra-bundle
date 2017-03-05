<?php

namespace Deamon\LoggerExtraBundle\Processors\Monolog;

use Symfony\Bridge\Monolog\Processor\WebProcessor as BaseWebProcessor;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DeamonLoggerExtraWebProcessor extends BaseWebProcessor
{
    /**
     * @var Container
     */
    private $container = null;

    /**
     * @var array|null
     */
    private $displayConfig;

    /**
     * @var string
     */
    private $channelPrefix;

    /**
     * @var array
     */
    private $record;

    public function __construct($container = null, array $config = null)
    {
        parent::__construct();
        $this->container = $container;
        $this->channelPrefix = $config['channel_prefix'];
        $this->displayConfig = $config['display'];
    }

    /**
     * @param array $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        $this->record = parent::__invoke($record);

        if ($this->container === null) {
            return $this->record;
        }

        $this->addContextInfo();
        $this->addRequestInfo();
        $this->addUserInfo();
        $this->addChannelInfo();

        return $this->record;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    private function addContextInfo()
    {
        $this->addInfo('env', $this->container->get('kernel')->getEnvironment());

        $context = $this->container->get('deamon.logger_extra.context');

        $this->addInfo('locale', $context->getLocale());
        if ($this->configShowExtraInfo('application_name')) {
            $this->record['extra']['application'] = $context->getApplicationName();
        }
    }

    private function addRequestInfo()
    {
        if (null !== $request_stack = $this->container->get('request_stack')) {
            $request = $request_stack->getCurrentRequest();
            if ($request instanceof Request) {
                $this->addInfo('url', $request->getRequestUri());
                $this->addInfo('route', $request->get('_route'));
                $this->addInfo('user_agent', $request->server->get('HTTP_USER_AGENT'));
                $this->addInfo('accept_encoding', $request->headers->get('Accept-Encoding'));
                $this->addInfo('client_ip', $request->getClientIp());
            }
        }
    }

    private function addUserInfo()
    {
        if ($this->configShowExtraInfo('user')) {
            $token = $this->container->get('security.token_storage')->getToken();
            if (($token instanceof TokenInterface) && ($token->getUser() instanceof UserInterface) && null !== $user = $token->getUser()) {
                if ($this->configShowExtraInfo('user_id') && method_exists($user, 'getId')) {
                    $this->record['extra']['user_id'] = $user->getId();
                }
                if ($this->configShowExtraInfo('user_email') && method_exists($user, 'getEmail')) {
                    $this->record['extra']['user_email'] = $user->getEmail();
                }
                if ($this->configShowExtraInfo('user_name') && method_exists($user, 'getUsername')) {
                    $this->record['extra']['user_name'] = $user->getUsername();
                }
            }
        }
    }

    private function addChannelInfo()
    {
        $this->addInfo('global_channel', $this->record['channel']);

        if ($this->channelPrefix !== null) {
            $this->record['channel'] = sprintf('%s.%s', $this->channelPrefix, $this->record['channel']);
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    private function addInfo($key, $value)
    {
        if ($this->configShowExtraInfo($key)) {
            $this->record['extra'][$key] = $value;
        }
    }

    /**
     * @param string $extraInfo
     *
     * @return bool
     */
    private function configShowExtraInfo($extraInfo)
    {
        return isset($this->displayConfig[$extraInfo]) && $this->displayConfig[$extraInfo];
    }
}
