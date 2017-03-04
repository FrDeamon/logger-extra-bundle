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

    public function __construct($container = null, array $config = null)
    {
        parent::__construct();
        $this->container = $container;
        $this->channelPrefix = $config['channel_prefix'];
        $this->displayConfig = $config['display'];
    }

    /**
     * @param  array $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        $record = parent::__invoke($record);

        if($this->container === null){
            return $record;
        }

        $this->addContextInfo($record);
        $this->addRequestInfo($record);
        $this->addUserInfo($record);
        $this->addChannelInfo($record);

        return $record;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    private function addContextInfo(&$record)
    {
        if ($this->configShowExtraInfo('env')) {
            $env = $this->container->get('kernel')->getEnvironment();
            $record['extra']['env'] = $env;
        }

        $context = $this->container->get('deamon.logger_extra.context');

        if ($this->configShowExtraInfo('locale')) {
            $record['extra']['locale'] = $context->getLocale();
        }
        if ($this->configShowExtraInfo('application_name')) {
            $record['extra']['application'] = $context->getApplicationName();
        }
    }

    private function addRequestInfo(&$record)
    {
        if (null !== $request = $this->container->get('request_stack')->getCurrentRequest()
        ) {
            if($request instanceof Request){
                if($this->configShowExtraInfo('url')) {
                    $record['extra']['url'] = $request->getRequestUri();
                }
                if ($this->configShowExtraInfo('route')) {
                    $record['extra']['route'] = $request->get('_route');
                }
                if ($this->configShowExtraInfo('user_agent')) {
                    $record['extra']['user_agent'] = $request->server->get('HTTP_USER_AGENT');
                }
                if ($this->configShowExtraInfo('accept_encoding')) {
                    $record['extra']['accept_encoding'] = $request->headers->get('Accept-Encoding');
                }
                if ($this->configShowExtraInfo('client_ip')) {
                    $record['extra']['client_ip'] = $request->getClientIp();
                }
            }
        }
    }

    private function addUserInfo(&$record)
    {
        if ($this->configShowExtraInfo('user')) {
            $token = $this->container->get('security.token_storage')->getToken();
            if (($token instanceof TokenInterface) && ($token->getUser() instanceof UserInterface) && null !== $user = $token->getUser()) {
                if ($this->configShowExtraInfo('user_id') && method_exists($user, 'getId')) {
                    $record['extra']['user_id'] = $user->getId();
                }
                if ($this->configShowExtraInfo('user_email') && method_exists($user, 'getEmail')) {
                    $record['extra']['user_email'] = $user->getEmail();
                }
                if ($this->configShowExtraInfo('user_name') && method_exists($user, 'getUsername')) {
                    $record['extra']['user_name'] = $user->getUsername();
                }
            }
        }
    }

    private function addChannelInfo(&$record)
    {
        if ($this->configShowExtraInfo('global_channel')) {
            $record['extra']['global_channel'] = $record['channel'];
        }

        if($this->channelPrefix !== null) {
            $record['channel'] = sprintf('%s.%s', $this->channelPrefix,$record['channel']);
        }
    }

    /**
     * @param $extraInfo
     *
     * @return bool
     */
    private function configShowExtraInfo($extraInfo)
    {
        return isset($this->displayConfig[$extraInfo]) && $this->displayConfig[$extraInfo];
    }
}
