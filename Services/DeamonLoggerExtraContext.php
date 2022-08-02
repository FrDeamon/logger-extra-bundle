<?php

namespace Deamon\LoggerExtraBundle\Services;

class DeamonLoggerExtraContext
{
    public function __construct(
        private ?string $applicationName,
        private ?string $locale = null,
        private ?string $applicationVersion = null)
    {
        if(null !== $applicationVersion){
            $this->applicationVersion = trim($this->applicationVersion);
        }
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getApplicationName(): ?string
    {
        return $this->applicationName;
    }

    public function getApplicationVersion(): ?string
    {
        return $this->applicationVersion;
    }
}
