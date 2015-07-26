# Laravel Shopping Cart Module

[![Build Status](https://travis-ci.org/php-soft/laravel-shopping-cart.svg)](https://travis-ci.org/php-soft/laravel-shopping-cart)

## 1. Installation

Install via composer - edit your `composer.json` to require the package.

```js
"require": {
    // ...
    "php-soft/laravel-shopping-cart": "dev-master",
}
```

Then run `composer update` in your terminal to pull it in.
Once this has finished, you will need to add the service provider to the `providers` array in your `app.php` config as follows:

```php
'providers' => [
    // ...
    PhpSoft\Illuminate\ShoppingCart\Providers\ShoppingCartServiceProvider::class,
]
```

## 2. Migration and Seeding

Now generate the migration:

```sh
$ php artisan shoppingcart:migrate
```

It will generate the `<timestamp>_shoppingcart_setup_tables.php` migration. You may now run it with the artisan migrate command:

```sh
$ php artisan migrate
```

Running Seeders with command:

```sh
$ php artisan db:seed --class=ShoppingCartModuleSeeder
```

## 3. Usage

Add routes in `app/Http/routes.php`

```php
Route::resource('products', '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController');
```
