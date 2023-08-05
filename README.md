# Learn to Win General Symfony Bundle

This bundle is a collection of useful tools for Learn to Win Symfony projects.

## Doctrine DBAL Type

### DateTimeMicrosecondsType

This type extends the default Doctrine DateTimeType to include microseconds.

This is enabled automatically by the bundle adding the following to your `config/packages/doctrine.yaml`:

```yaml
doctrine:
    dbal:
        types:
            datetime: LearnToWin\SymfonyDoctrineDbalTypes\DateTimeMicrosecondsType
```

