# Glide adapter for Charcoal

[![Build Status][github-badge]][github-actions]
[![Latest Stable Version][version-badge]](CHANGELOG.md)
[![License][license-badge]](LICENSE)
[![Supported PHP Version][php-badge]](composer.json)

Integrates [Glide][league/glide] into [Charcoal][charcoal/charcoal]
to manipulate images on-demand.

> […] Its straightforward API is exposed via HTTP, similar to cloud image
> processing services like Imgix and Cloudinary. Glide leverages powerful
> libraries like [Intervention Image][intervention/image] (for image handling and
> manipulation) and [Flysystem][league/flysystem] (for file system abstraction).

## Installation

This library is available on [Packagist][charcoal/contrib-glide] and can be
installed using [Composer]:

```shell
composer require charcoal/contrib-glide
```

## Configuration

### Default Server

You can define a single Glide service with a single server:

```json
{
    "apis": {
        "glide": {
            "signature": "signing-key",
            "source": "%app.public_path%/uploads"
            "cache": "%app.cache_path%/glide",
            "defaults": {
                "mark": "logo.png",
                "markw": "30w",
                "markpad": "5w"
            },
            "presets": {
                "thumbnail": {
                    "w": 300,
                    "h": 300,
                    "q": 75,
                    "fit": "crop"
                },
                "hero": {
                    "w": 1600,
                    "h": 900,
                    "q": 90,
                    "fit": "max"
                }
            }
        }
    }
}
```

### Multiple Servers

You can also configure multiple servers, each of which is entirely separate:

```json
{
    "apis": {
        "glide": {
            "signature": "signing-key",
            "servers": {
                "first": [
                    "source": "%app.public_path%/uploads/first",
                    "cache": "%app.cache_path%/glide/first",
                    "base_url": "/img/",
                    "max_image_size": 40000,
                    "presets": [
                        "invalid_preset": null,
                        "small_hero": [
                            "w": 200,
                            "h": 200,
                            "fit": 'crop',
                            "nil": null,
                        ],
                    ],
                ],
                "second": [
                    "source": "%app.public_path%/uploads/second",
                    "cache": "%app.cache_path%/glide/second",
                ],
            },
            "default_server": "first"
        }
    }
}
```

With the Glide manager instance, you can retrieve a given server or URL builder,
and switch the current server:

```php
$glideManager->getServer('second');

$glideManager->getUrlBuilder('second');

$glideManager->using('second')->getUrlBuilder();
```

See [Glide documentation](https://glide.thephpleague.com/2.0/config/setup/)
for more information on configuring servers.

## Usage

TBD

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
with the [Pest] framework, which internally uses the [PHPUnit] framework.

Tests can be executing by running one of the following commands:

```shell
composer test
composer test:pest
./vendor/bin/pest
```

---

🚂

[//]: # (General Links)
[charcoal/admin]:         https://github.com/charcoalphp/charcoal/tree/main/packages/admin
[charcoal/charcoal]:      https://github.com/charcoalphp/charcoal
[charcoal/contrib-glide]: https://packagist.org/packages/charcoal/contrib-glide
[Composer]:               https://getcomposer.org/
[intervention/image]:     https://github.com/Intervention/image
[league/flysystem]:       http://flysystem.thephpleague.com/
[league/glide]:           https://glide.thephpleague.com/

[//]: # (Development Links)
[Pest]:              https://pestphp.com/
[PHP-CLI/options]:   https://php.net/manual/en/features.commandline.options.php
[PHP_CodeSniffer]:   https://github.com/squizlabs/PHP_CodeSniffer
[PHPStan]:           https://phpstan.org/
[PHPUnit]:           https://phpunit.de/
[Psalm]:             https://psalm.dev/
[seld/jsonlint]:     https://github.com/Seldaek/jsonlint

[//]: # (Badge Links)
[github-actions]:    https://github.com/charcoalphp/boilerplate/actions
[github-badge]:      https://img.shields.io/github/workflow/status/charcoalphp/boilerplate/Test?label=build
[license-badge]:     https://img.shields.io/packagist/l/charcoal/boilerplate.svg?style=flat-square
[php-badge]:         https://img.shields.io/packagist/php-v/charcoal/boilerplate?style=flat-square&logo=php
[version-badge]:     https://img.shields.io/packagist/v/charcoal/boilerplate.svg?style=flat-square&logo=packagist
