# Laravel Shopping Cart Module

[![Build Status](https://travis-ci.org/php-soft/laravel-shopping-cart.svg)](https://travis-ci.org/php-soft/laravel-shopping-cart)

> This is RESTful APIs

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
    PhpSoft\Illuminate\ArrayView\Providers\ArrayViewServiceProvider::class,
    PhpSoft\Illuminate\ShoppingCart\Providers\ShoppingCartServiceProvider::class,
]
```

## 2. Migration and Seeding

Now generate the migration:

```sh
$ php artisan shoppingcart:migrate
```

It will generate the migration files. You may now run it with the artisan migrate command:

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
// categories resource
Route::get('categories', '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@index');
Route::get('categories/{id}', '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@show');
Route::group(['middleware'=>'auth'], function() { // use middleware jwt.auth if use JSON Web Token
    Route::post('categories', [
        'middleware' => 'permission:create-category',
        'uses' => '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@store'
    ]);
    Route::put('categories/{id}', [
        'middleware' => 'permission:update-category',
        'uses' => '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@update'
    ]);
    Route::delete('categories/{id}', [
        'middleware' => 'permission:delete-category',
        'uses' => '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@destroy'
    ]);
});

Route::get('categories/{id}/products', '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@index');

// products resource
Route::get('products', '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@index');
Route::get('products/{id}', '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@show');
Route::group(['middleware'=>'auth'], function() { // use middleware jwt.auth if use JSON Web Token
    Route::post('products', [
        'middleware' => 'permission:create-product',
        'uses' => '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@store'
    ]);
    Route::put('products/{id}', [
        'middleware' => 'permission:update-product',
        'uses' => '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@update'
    ]);
    Route::delete('products/{id}', [
        'middleware' => 'permission:delete-product',
        'uses' => '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@destroy'
    ]);
});
```

***You can remove middlewares if your application don't require check authenticate and permission!***
