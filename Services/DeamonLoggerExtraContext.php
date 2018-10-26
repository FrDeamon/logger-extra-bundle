<?php

namespace Deamon\LoggerExtraBundle\Services;

class DeamonLoggerExtraContext
{
    /**
     * @var string|null
     */
    private $locale;
    /**
     * @var string|null
     */
    private $applicationName;

    public function __construct(string $applicationName, ?string $locale = null)
    {
        $this->applicationName = $applicationName;
        $this->locale = $locale;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getApplicationName(): ?string
    {
        return $this->applicationName;
    }
}
