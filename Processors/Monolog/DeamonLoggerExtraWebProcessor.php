<?php

namespace Deamon\LoggerExtraBundle\Processors\Monolog;

use Deamon\LoggerExtraBundle\Services\DeamonLoggerExtraContext;
use Symfony\Bridge\Monolog\Processor\WebProcessor as BaseWebProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DeamonLoggerExtraWebProcessor extends BaseWebProcessor
{
    /**
     * @var string
     */
    private $environment = null;

    /**
     * @var DeamonLoggerExtraContext
     */
    private $loggerExtraContext = null;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage = null;

    /**
     * @var RequestStack
     */
    private $requestStack = null;

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

    public function __construct(array $config = null)
    {
        parent::__construct();
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
        if (null !== $this->environment) {
            $this->addInfo('env', $this->environment);
        }

        if (null !== $this->loggerExtraContext) {
            $this->addInfo('locale', $this->loggerExtraContext->getLocale());
            if ($this->configShowExtraInfo('application_name')) {
                $this->record['extra']['application'] = $this->loggerExtraContext->getApplicationName();
            }
        }
    }

    /**
     * Add extra info about the request generating the log.
     */
    private function addRequestInfo()
    {
        if (null !== $this->requestStack) {
            $request = $this->requestStack->getCurrentRequest();
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

            if (!$this->tokenStorage instanceof TokenStorage) {
                return;
            }

            $token = $this->tokenStorage->getToken();
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
     * @param DeamonLoggerExtraContext $loggerExtraContext
     */
    public function setLoggerExtraContext(DeamonLoggerExtraContext $loggerExtraContext)
    {
        $this->loggerExtraContext = $loggerExtraContext;
    }

    /**
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
}
