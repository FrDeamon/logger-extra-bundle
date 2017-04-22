DeamonLoggerExtra Bundle
==============================

[![Build Status](https://travis-ci.org/FrDeamon/logger-extra-bundle.svg?branch=master&style=flat)](https://travis-ci.org/FrDeamon/logger-extra-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FrDeamon/logger-extra-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/FrDeamon/logger-extra-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/FrDeamon/logger-extra-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/FrDeamon/logger-extra-bundle/?branch=master)
![symfony version](https://img.shields.io/badge/symfony->=2.7,%20>=3.0-blue.svg)
![php version](https://img.shields.io/badge/php->=5.6.0,%20>=7-blue.svg)


[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5a913c84-a190-40f7-9e46-3c2052692fcd/big.png)](https://insight.sensiolabs.com/projects/5a913c84-a190-40f7-9e46-3c2052692fcd)


This project is used to add extra context information in your logs.  

If you need compatibility with Symfony<2.7, have a look at versions <1.0.  

Requirements
----------------
php 5.6.0

symfony/security

symfony/dependency-injection

symfony/monolog-bridge

symfony/http-foundation

symfony/http-kernel

symfony/config

Compatible with Symfony starting from 2.7

Installation
----------------

You need to add a package to your dependency list :

    // composer.json
    "deamon/logger-extra-bundle": "^2.0"

Then enable the bundle into your kernel

    // app/AppKernel.php
    new Deamon\LoggerExtraBundle\DeamonLoggerExtraBundle(),

Finally you need to configure the bundle.


## Config Example

Given this config sample of a project:

```
// app/config/config.yml
monolog:
    handlers:
        default_info:
            type: gelf
            publisher:
                hostname: "%graylog_host%"
            level: INFO
            channels: [!request, !security, !app, !monitoring, !deprecation, !php]
        default_notice:
            type: gelf
            publisher:
                hostname: "%graylog_host%"
            level: NOTICE
            channels: [request, security, app, php]
```            

With this example of monolog config, you can configure this bundle to only add extra info on `default_info` handler.

```
// app/config/config.yml
deamon_logger_extra:
    application:  
        name: "loc-deamonfront"
    handlers: [default_info]
    config:
        channel_prefix: "v0.1"
```

## Config reference

```
// app/config/config.yml
deamon_logger_extra:
    application:
        name: "loc-deamonfront" # default to null 
    handlers: [default_info] # the only required field
    config:
        channel_prefix: "v0.1" # default to null
        user_class: "\Symfony\Component\Security\Core\User\UserInterface" # default value
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
deamon_logger_extra:
    application: ~
    handlers: 'default_info'
    config: ~
```
