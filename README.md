# This package allow sync two different laravel filament app db

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teguh02/filament-db-sync.svg?style=flat-square)](https://packagist.org/packages/teguh02/filament-db-sync)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/teguh02/filament-db-sync/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/teguh02/filament-db-sync/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/teguh02/filament-db-sync/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/teguh02/filament-db-sync/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/teguh02/filament-db-sync.svg?style=flat-square)](https://packagist.org/packages/teguh02/filament-db-sync)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require teguh02/filament-db-sync
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-db-sync-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-db-sync-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-db-sync-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$filamentDbSync = new Teguh02\FilamentDbSync();
echo $filamentDbSync->echoPhrase('Hello, Teguh02!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Teguh Rijanandi](https://github.com/teguh02)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
