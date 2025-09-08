<?php

namespace Deamon\LoggerExtraBundle\Tests\Services;

use Deamon\LoggerExtraBundle\Services\DeamonLoggerExtraContext;
use PHPUnit\Framework\TestCase;

class DeamonLoggerExtraContextTest extends TestCase
{
    /**
     * @dataProvider getLocaleDataset
     *
     * @param string $locale
     */
    public function testGetLocale($locale): void
    {
        $context = new DeamonLoggerExtraContext('', $locale);
        $this->assertEquals($locale, $context->getLocale(), sprintf('locale should be %s, %s returned.', $locale, $context->getLocale()));
    }

    public function getLocaleDataset(): array
    {
        return [
            ['fr', 'locale should be fr'],
            ['en', 'locale should be en'],
        ];
    }

    /**
     * @dataProvider getApplicationNameDataset
     *
     * @param $applicationName
     */
    public function testGetApplicationName($applicationName): void
    {
        $context = new DeamonLoggerExtraContext($applicationName, 'fr');
        $this->assertEquals($applicationName, $context->getApplicationName(), sprintf('application_name should be %s, %s returned.', $applicationName, $context->getApplicationName()));
    }

    public function getApplicationNameDataset(): array
    {
        return [
            ['github.com/deamon'],
            ['foo.bar'],
        ];
    }
}
