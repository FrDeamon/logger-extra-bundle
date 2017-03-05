<?php

namespace Deamon\LoggerExtraBundle\Services;

class DeamonLoggerExtraContext
{
    private $locale;
    private $applicationName;

    public function __construct($locale, $applicationName)
    {
        $this->locale = $locale;
        $this->applicationName = $applicationName;
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
