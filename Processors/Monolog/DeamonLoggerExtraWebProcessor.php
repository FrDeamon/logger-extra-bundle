<?php

namespace Deamon\LoggerExtraBundle\Processors\Monolog;

use Deamon\LoggerExtraBundle\Services\DeamonLoggerExtraContext;
use Monolog\LogRecord;
use Monolog\Processor\WebProcessor as BaseWebProcessor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DeamonLoggerExtraWebProcessor extends BaseWebProcessor
{

    private ?string $environment = null;

    private ?DeamonLoggerExtraContext $loggerExtraContext = null;

    private ?TokenStorageInterface $tokenStorage = null;

    private ?RequestStack $requestStack = null;

    private array $displayConfig;

    private ?string $channelPrefix;

    private ?string $userClass;

    private array $userMethods;

    private LogRecord $record;

    public function __construct(?array $config = null)
    {
        parent::__construct([]);

        $this->channelPrefix =$config['channel_prefix'] ?? null;

        $this->userMethods = $config['user_methods'] ?? [];
        $this->displayConfig = $config['display'] ?? [];
        $this->userClass = $config['user_class'] ?? null;
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $this->record = parent::__invoke($record);

        $this->addContextInfo();
        $this->addRequestInfo();
        $this->addUserInfo();

        return $this->record;
    }

    /**
     * Add extra info about the context of the generated log.
     */
    private function addContextInfo(): void
    {
        if (null !== $this->environment) {
            $this->addInfo('env', $this->environment);
        }

        if (null !== $this->loggerExtraContext) {
            $this->addInfo('locale', $this->loggerExtraContext->getLocale());
            if ($this->configShowExtraInfo('application_name')) {
                $this->record->extra['application'] = $this->loggerExtraContext->getApplicationName();
            }
            $applicationVersion = $this->loggerExtraContext->getApplicationVersion() ?? $this->channelPrefix;
            $this->addInfo('application_version', $applicationVersion);
        }
    }

    /**
     * Add extra info about the request generating the log.
     */
    private function addRequestInfo(): void
    {
        $request = $this->requestStack?->getCurrentRequest();
        if ($request instanceof Request) {
            $this->addInfo('url', $request->getRequestUri());
            $this->addInfo('route', $request->get('_route'));
            $this->addInfo('user_agent', $request->server->get('HTTP_USER_AGENT'));
            $this->addInfo('accept_encoding', $request->headers->get('Accept-Encoding'));
            $this->addInfo('client_ip', $request->getClientIp());
        }
    }

    /**
     * Add extra info on the user generating the log.
     */
    private function addUserInfo(): void
    {
        if (!$this->configShowExtraInfo('user') || empty($this->userClass)) {
            return;
        }

        if (!class_exists($this->userClass) && !interface_exists($this->userClass)) {
            return;
        }

        $token = $this->tokenStorage?->getToken();
        if ($this->isUserInstanceValid($token) && null !== $user = $token->getUser()) {
            $this->appendUserMethodInfo($user);
        }
    }

    /**
     * Append method result of user object.
     */
    private function appendUserMethodInfo(UserInterface $user): void
    {
        foreach ($this->userMethods as $name => $method) {
            if (method_exists($user, $method)) {
                $this->record->extra[$name] = $user->$method();
            }
        }
    }

    /**
     * Check if passed token is an instance of TokenInterface and an instance of config UserClass.
     */
    private function isUserInstanceValid(?TokenInterface $token): bool
    {
        return $token instanceof TokenInterface && $token->getUser() instanceof $this->userClass;
    }

    /**
     * Add the extra info if configured to.
     */
    private function addInfo(string $key, mixed $value): void
    {
        if ($this->configShowExtraInfo($key) && $value !== null) {
            $this->record->extra[$key] = $value;
        }
    }

    /**
     * Tells if the config to display the extra info is enabled or not.
     */
    private function configShowExtraInfo(string $extraInfo): bool
    {
        return isset($this->displayConfig[$extraInfo]) && $this->displayConfig[$extraInfo];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->isMainRequest()) {
            $this->serverData = $event->getRequest()->server->all();
            $this->serverData['REMOTE_ADDR'] = $event->getRequest()->getClientIp();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 4096],
        ];
    }

    public function setLoggerExtraContext(DeamonLoggerExtraContext $loggerExtraContext): void
    {
        $this->loggerExtraContext = $loggerExtraContext;
    }

    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    public function setTokenStorage(TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }
}
