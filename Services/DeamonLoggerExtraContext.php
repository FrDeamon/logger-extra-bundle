<?php

namespace Deamon\LoggerExtraBundle\Services;

class DeamonLoggerExtraContext
{
    private $locale;
    private $applicationName;

    public function __construct($applicationName, $locale = null)
    {
        $this->applicationName = $applicationName;
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getApplicationName()
    {
        return $this->applicationName;
    }
}
