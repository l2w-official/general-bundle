# Learn to Win General Symfony Bundle

This bundle is a collection of useful tools for Learn to Win Symfony projects.

## Setup 

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

