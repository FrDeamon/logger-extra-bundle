<?php

namespace Deamon\LoggerExtraBundle\Services;

class DeamonLoggerExtraContext
{
    public function __construct(
        private string $applicationName,
        private ?string $locale = null)
    {}

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getApplicationName(): string
    {
        return $this->applicationName;
    }
}
