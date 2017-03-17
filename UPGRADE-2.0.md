UPGRADE FROM 1.x to 2.0
=======================

# Table of Contents

- [Configuration](#configuration)

### Configuration

The configuration has changed.

You can now now declare your own user_class and list methods you want to add in logs.

Before :

```yml
    deamon_logger_extra:
        ...
        config:
            ...
            display:
                user: true
                user_id: true
                user_email: true
                user_name: true
```

After :

```yml
    deamon_logger_extra:
        ...
        config:
            ...
            user_class: '\Acme\AppBundle\Security\User\AcmeUser'
            user_methods:
                user_name: getUsername
                user_email: getEmail
                user_id: getId
            display:
                user: true
```
