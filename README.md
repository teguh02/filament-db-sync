![Background](assets/images/background.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/teguh02/filament-db-sync.svg?style=flat-square)](https://packagist.org/packages/teguh02/filament-db-sync)
[![Total Downloads](https://img.shields.io/packagist/dt/teguh02/filament-db-sync.svg?style=flat-square)](https://packagist.org/packages/teguh02/filament-db-sync)

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

### db_sync.php config
For the plugins config, you can adjust manually as you needs.
```php
<?php

// config for Teguh02/FilamentDbSync
return [

    /**
     * The host where the data will be synced
     */
    'sync_host' => env('SYNC_HOST', 'http://localhost'),

    /**
     * The token to be used for authentication
     */
    'auth_token' => env('SYNC_TOKEN', 'default_token'),

    /**
     * Models configuration
     */
    'models' => [
        /**
         * If set to true, the package will
         * automatically scan all models in the app/Models directory
         */
        'auto_scan' => env('AUTO_SCAN_MODELS', true),

        /**
         * If auto_scan is set to true,
         * this configuration will be used to exclude models
         * which will not be synced
         */
        'excluded' => [
            // App\Models\User::class,
        ],

        /**
         * When auto_scan is set to false,
         * this configuration will be used to include models
         */
        'included' => [
            // App\Models\User::class,
        ],

        /**
         * The column to be used as the key
         * when syncing data
         */
        'column_as_key' => [
            // class => column
            App\Models\User::class => 'email',

            // or you can use the table name
            // table_name => column
            // 'users' => 'email',
        ],
    ],

    /**
     * Sync configuration
     */
    'sync' => [
        /**
         * The action to be taken when there is duplicate data
         *
         * Available options:
         * - update : update the existing data
         * - duplicate : create a new data with the same data
         */
        'duplicate_data_action' => env('DUPLICATE_DATA_ACTION', 'update'),
    ],
];
```

### Sync Env Configuration
Please set your env configuration following below, for example we have 2 different server below. Server 1 app domain is server1.com and then server 2 domain is server2.com

On the Server 1 : 
```bash
SYNC_TOKEN=FUswndOCKEm5rAKzgqFDsXZ5euWhA535tOzgE00n9tuP4IsofFslPM5VgtrT
SYNC_HOST=http://server2.com
```

On the Server 2 :
```bash
SYNC_TOKEN=FUswndOCKEm5rAKzgqFDsXZ5euWhA535tOzgE00n9tuP4IsofFslPM5VgtrT
SYNC_HOST=http://server1.com
```

### Queue configuration
Because this plugin use a jobs function to execute huge data, please set your queue driver according your needs
```bash
#QUEUE_CONNECTION=sync
QUEUE_CONNECTION=database
```

## Screenshoot
![Screenshot](https://github.com/user-attachments/assets/7c0add30-0f0f-4b1c-baa8-cccf59f61444)


## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Teguh Rijanandi](https://github.com/teguh02)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
