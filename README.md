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

### Panel Configuration
Install the library in your panel service provider
```php
<?php
use Teguh02\FilamentDbSync\FilamentDbSync;

public function panel(Panel $panel): Panel
{
    return $panel
    // ... another filament configuration
        ->plugins([
            FilamentDbSync::make(),
        ]);
}
```
### Configuration File and Migrations

Publish the configuration and migrations

```bash
php artisan vendor:publish --provider="Teguh02\FilamentDbSync\FilamentDbSyncServiceProvider"
php artisan migrate
```

## Usage

### Model configuration
In your model class, add the <code>fillable</code> and the <code>casts</code> attribute. For example if we have a model with name is Items, the model configuration should will be below
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    use HasFactory;

    // Define the table property 
    // if the table name is different
    // protected $table = 'items';

    // Define the primary key property
    // if the primary key is different
    // protected $primaryKey = 'id';

    // Define the fillable property to 
    // allow mass assignment on the model
    // and the database sync process
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'expired_at',
    ];

    // Define the casts property to
    // automatically cast the data type
    // of the model attributes
    protected $casts = [
        'stock' => 'integer',
        'price' => 'integer',
        'expired_at' => 'date',
    ];
}
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
