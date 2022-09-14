# Glide adapter for Charcoal

Provides support and utilities for [Glide][league/glide] to manipulate images
on-demand in [Charcoal][charcoal/charcoal].

## Contributing

We appreciate any contribution to the skeleton and Charcoal, whether it's a bug,
typo, suggestions, or improvements.

## Development

### Linting

By default, the skeleton is developed with a number of coding style and
static code analysis tools:

* [EditorConfig] — Maintain consistent coding styles between different editors.
* [JSON Lint][seld/jsonlint] — JSON coding style linter.
* [PHP Syntax Check][PHP-CLI/options] — PHP syntax checker from the command line.
* [PHP_CodeSniffer] (PHPCS) — PHP coding style linter.
* [PHPStan] — Static PHP code analyser.
* [Psalm] — Static PHP code analyser.

PHPStan and Psalm are used together to take advantage of each one's specialties.

Linting can be executing by running one of the following commands:

```shell
# Run JSON Lint, PHP Lint, PHPCS, PHPStan, and Psalm
composer lint

# Run only JSON Lint
composer lint:json
./vendor/bin/jsonlint config/*.json

# Run only PHP syntax check
composer lint:php
php -l src/*.php

# Run only PHPCS
composer lint:phpcs
./vendor/bin/phpcs -ps --colors src/

# Run only PHPStan
composer lint:phpstan
./vendor/bin/phpstan analyse

# Run only Psalm
composer lint:psalm
./vendor/bin/psalm
```

### Testing

By default, PHP tests are located in the [`tests`](tests) directory and developed
with the [PHPUnit] framework.

Tests can be executing by running one of the following commands:

```shell
composer test
composer test:phpunit
./vendor/bin/phpunit
```

---

🚂

[//]: # (General Links)
[charcoal/charcoal]:  https://github.com/charcoalphp/charcoal
[Composer]:           https://getcomposer.org/
[intervention/image]: https://github.com/Intervention/image
[league/glide]:       https://glide.thephpleague.com/

[//]: # (Development Links)
[PHP-CLI/options]:   https://php.net/manual/en/features.commandline.options.php
[PHP_CodeSniffer]:   https://github.com/squizlabs/PHP_CodeSniffer
[PHPStan]:           https://phpstan.org/
[PHPUnit]:           https://phpunit.de/
[Psalm]:             https://psalm.dev/
[seld/jsonlint]:     https://github.com/Seldaek/jsonlint
