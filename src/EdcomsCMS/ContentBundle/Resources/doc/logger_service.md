## Slack Logger Configuration

To configure Edcoms Slack Logger, we need first to specify *edcoms.logger.slack* service after the monolog handlers in the **application environment configuration** file like bellow:

```
monolog:
    handlers:
        slack:
            type: service
            id: edcoms.logger.slack
```

In the same file we also have to declare the parameters:

```
parameters:
    edcoms_logger_slack:
        token: '%slack_api_token%'
        channel: '%slack_channel%'
        bot_name: Edcoms DEV
        useAttachment: true
        icon_emoji: :ghost:
        level: critical
        bubble: true
        useShortAttachment: false
        includeContextAndExtra: true
        saltKey: '__NOT HERE__'
        processors: ['email', 'ipv4']   # available processors
```

Where only the **token** and the **channel** are required, which should be declared in the **parameters.yml** file.
The other parameters are optional.

