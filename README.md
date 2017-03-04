#DeamonLoggerExtra Bundle

[![Build Status](https://travis-ci.org/Deamon/logger-extra-bundle.svg?branch=master)](https://travis-ci.org/Deamon/logger-extra-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2b9e464e-d291-487f-9169-8b445ee73d83/mini.png)](https://insight.sensiolabs.com/projects/2b9e464e-d291-487f-9169-8b445ee73d83)


This project is used to add extra context information in your logs.

Dans le projet qui importe le bundle

Ajouter dans config.yml les handlers dans le noeud monolog


## Example

Config sample for a project:

```
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

With this example of monolog config, you can config the bundle to only add extra info on `default_info` handler.

```
deamon_logger_extra:
    application:  
        name: "loc-deamonfront"
    handlers: [default_info]
    config:
        channel_prefix: "v0.1"
```

## Config reference

```
deamon_logger_extra:
    application:  
        name: "loc-deamonfront" #default to null 
    handlers: [default_info] #the only required field
    config:
        channel_prefix: "v0.1" #default to null
        display:
            env: boolean default to true
            locale: boolean default to true
            application_name: boolean default to true
            url: boolean default to true
            route: boolean default to true
            user_agent: boolean default to true
            accept_encoding: boolean default to true
            client_ip: boolean default to true
            user: boolean default to true
            user_id: boolean default to true
            user_email: boolean default to true
            user_name: boolean default to true
            global_channel: boolean default to true
```
