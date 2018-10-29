DeamonLoggerExtra Bundle
==============================

[![Build Status](https://travis-ci.org/FrDeamon/logger-extra-bundle.svg?branch=master&style=flat)](https://travis-ci.org/FrDeamon/logger-extra-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FrDeamon/logger-extra-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/FrDeamon/logger-extra-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/FrDeamon/logger-extra-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/FrDeamon/logger-extra-bundle/?branch=master)
![symfony version](https://img.shields.io/badge/symfony->=4.0-blue.svg)
![php version](https://img.shields.io/badge/php->=7.1-blue.svg)


[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5a913c84-a190-40f7-9e46-3c2052692fcd/big.png)](https://insight.sensiolabs.com/projects/5a913c84-a190-40f7-9e46-3c2052692fcd)


This project is used to add extra context information in your logs.  

If you need compatibility with previous Symfony versions, have a look at previous releases.  

Requirements
----------------
php >=7.1

symfony/security
symfony/dependency-injection
symfony/monolog-bridge
symfony/http-kernel
symfony/http-foundation
symfony/config

Compatible with Symfony starting from 4.0

Installation
----------------

You need to add a package to your dependency list :
```
    // composer.json
    "deamon/logger-extra-bundle": "^4.0"
```

Then enable the bundle into your kernel
```
    // config/bundles.php
    return [
        // ...
        App\Acme\TestBundle\AcmeTestBundle::class => ['all' => true],
    ];
```

Finally you need to configure the bundle.


## Config Example

Given this config sample of a project:

```
// config/packages/monolog.yml
monolog:
    handlers:
        main:
            type: stream
            path: "%kernel.logs_dir%/%kernel.environment%.log"
            level: debug
            channels: ["!event"]
```            

With this example of monolog config, you can configure this bundle to only add extra info on `main` handler.

```
// config/packages/deamon_logger_extra.yml
deamon_logger_extra:
    application:  
        name: "loc-deamonfront"
    handlers: [main]
    config:
        channel_prefix: "v0.1"
```

## Config reference

```
// config/packages/deamon_logger_extra.yaml
deamon_logger_extra:
    application:
        name: "loc-deamonfront" # default to null
        locale: "fr" # default to null
    handlers: [main] # the only required field
    config:
        channel_prefix: "v0.1" # default to null
        user_class: "\\Symfony\\\Component\\Security\\Core\\User\\UserInterface" # default to null
        user_methods:
            user_name: getUsername # default value
        display:
            env: false # default to true
            locale: false # default to true
            application_name: false # default to true
            url: false # default to true
            route: false # default to true
            user_agent: false # default to true
            accept_encoding: false # default to true
            client_ip: false # default to true
            user: false # default to true
            global_channel: false # default to true
```
## Minimal configuration

```
// config/packages/deamon_logger_extra.yaml
deamon_logger_extra:
    application: ~
    handlers: 'main'
    config: ~
```
