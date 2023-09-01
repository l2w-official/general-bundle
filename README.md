# Learn to Win General Symfony Bundle

This bundle is a collection of useful tools for Learn to Win Symfony projects.

## Setup 

You will need to ensure that composer is authorized on github:
`composer config --global --auth github-oauth.github.com [token]`

To get the token:

In your GitHub account, click your account icon in the top-right corner, select Settings and Developer Settings. 
Then select Personal Access Tokens.

To add this bundle to your project, add the following to your composer.json:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:l2w-official/general_bundle.git"
    }
  ],
  "extra": {
    "symfony": {
      "allow-contrib": false,
      "require": "6.2.*",
      "endpoint": [
        "https://api.github.com/repos/l2w-official/recipes/contents/index.json",
        "flex://defaults"
      ]
    }
  }
}
```

Then run `composer require learn-to-win/general-bundle:^0.1`.

## Development Setup


## Configuration

### Doctrine DBAL Type

#### DateTimeMicrosecondsType

This type extends the default Doctrine DateTimeType to include microseconds.

This is enabled automatically by the bundle adding the following to your `config/packages/doctrine.yaml`:

```yaml
doctrine:
    dbal:
        types:
            datetime: LearnToWin\SymfonyDoctrineDbalTypes\DateTimeMicrosecondsType
```
### RabbitMQ Entity Events

This will create a doctrine listener, this will listen for postPersist, postUpdate, and postDelete events.
When one of those events occurs a message will be sent out to the RabbitMQ system configured under `entity_event`
exchange.

#### Data sent in message:

LearnToWin\GeneralBundle\Message\EntityMessage

```json
{
  "resource": "lower case short name of the entity, ie: user, organization...",
  "action": "which action took place, 'persist', 'update', 'delete'",
  "data": "json encoded string version of the entity"
}
```

#### Message exchange/queue config:

config/packages/messenger.yml

```yaml
framework:
  messenger:
    transports:
            rabbit_entity_publish:
                dsn: '%env(MESSENGER_TRANSPORT_DSN_RABBIT)%'
                options:
                    exchange:
                        name: entity_event
                        type: topic
                    queues: []

            rabbit_entity_subscribe:
              dsn: '%env(MESSENGER_TRANSPORT_DSN_RABBIT)%'
              options:
                exchange:
                  name: entity_event
                  type: topic
                queues:
                  user:
                    binding_keys: ['user.persist'] # resource.action, use * to represent wildcard like `user.*` for all actions

    routing:
      'LearnToWin\GeneralBundle\Message\EntityMessage': ['rabbit_entity_publish', 'rabbit_entity_subscribe']
```

#### Consuming messages

To consume messages (subscribe) you will need to run the `messenger:consume` console command.

`php bin/console messenger:consume rabbit_entity_subscribe  --limit=10 --memory-limit=512M`

The `rabbit_entity_subscribe` is the transport to consume messages on, you can add more as needed but you don't need to
add ones that are publish only like the `rabbit_entity_publish` as the MessageBus `dispatch` call is used for that.
The `--limit` will limit the process to 10 messages at most, it will die after that.
The `--memory-limit` will limit the process memory to 512 Mb, it will die if that is exceeded.

This is best run in a supervisord system.

##### Supervisord config

```ini
[program:messenger-consume]
command=php /var/www/html/bin/console messenger:consume rabbit_entity_subscribe --limit=10 --memory-limit=512M
environment=MESSENGER_CONSUMER_NAME=%(program_name)s_%(process_num)02d
numprocs=1
startsecs=0
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
startretries=10
process_name=%(program_name)s_%(process_num)02d
```