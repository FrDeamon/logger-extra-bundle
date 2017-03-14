<?php

namespace Deamon\LoggerExtraBundle\Processors\Monolog;

use Symfony\Bridge\Monolog\Processor\WebProcessor as BaseWebProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DeamonLoggerExtraWebProcessor extends BaseWebProcessor
{
    /**
     * @var ContainerInterface
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

    /** @var string */
    private $userClass;

    /** @var array */
    private $userMethods;

    /**
     * @var array
     */
    private $record;

    public function __construct(ContainerInterface $container = null, array $config = null)
    {
        parent::__construct();
        $this->container = $container;
        $this->channelPrefix = $config['channel_prefix'];
        $this->displayConfig = $config['display'];
        $this->userClass = $config['user_class'];
        $this->userMethods = $config['user_methods'];
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

    /**
     * Add extra info about the context of the generated log.
     */
    private function addContextInfo()
    {
        $this->addInfo('env', $this->container->get('kernel')->getEnvironment());

        $context = $this->container->get('deamon.logger_extra.context');

        $this->addInfo('locale', $context->getLocale());
        if ($this->configShowExtraInfo('application_name')) {
            $this->record['extra']['application'] = $context->getApplicationName();
        }
    }

    /**
     * Add extra info about the request generating the log.
     */
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

    /**
     * Add extra info on the user generating the log.
     */
    private function addUserInfo()
    {
        if ($this->configShowExtraInfo('user')) {
            if (!class_exists($this->userClass) && !interface_exists($this->userClass)) {
                return;
            }
            $token = $this->container->get('security.token_storage')->getToken();
            if (($token instanceof TokenInterface) && ($token->getUser() instanceof $this->userClass) && null !== $user = $token->getUser()) {
                foreach ($this->userMethods as $name => $method) {
                    if (method_exists($user, $method)) {
                        $this->record['extra'][$name] = $user->$method();
                    }
                }
            }
        }
    }

    /**
     * Add channel info to ease the log interpretation.
     */
    private function addChannelInfo()
    {
        $this->addInfo('global_channel', $this->record['channel']);

        if ($this->channelPrefix !== null) {
            $this->record['channel'] = sprintf('%s.%s', $this->channelPrefix, $this->record['channel']);
        }
    }

    /**
     * Add the extra info if configured to.
     *
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
     * Tells if the config to display the extra info is enabled or not.
     *
     * @param string $extraInfo
     *
     * @return bool
     */
    private function configShowExtraInfo($extraInfo)
    {
        return isset($this->displayConfig[$extraInfo]) && $this->displayConfig[$extraInfo];
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
