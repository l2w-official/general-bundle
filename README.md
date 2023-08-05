# Learn to Win General Symfony Bundle

This bundle is a collection of useful tools for Learn to Win Symfony projects.

To add this bundle to your project, add the following to your composer.json:

```json
"repositories":[
    {
        "type": "vcs",
        "url": "git@github.com:l2w-official/general_bundle.git"
    }
],
```

Then run `composer require learn-to-win/general-bundle:dev-main`.

`dev-main` is the development version of the bundle. If you want to use a specific version, replace `dev-main` with the version you want to use. These are generally tags in the format `1.0.0`.

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

