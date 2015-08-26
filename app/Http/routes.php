<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

// categories resource
Route::get('categories', '\PhpSoft\ShoppingCart\Controllers\CategoryController@index');
Route::get('categories/{id}', '\PhpSoft\ShoppingCart\Controllers\CategoryController@show');
Route::group(['middleware'=>'auth'], function() { // use middleware jwt.auth if use JSON Web Token
    Route::post('categories', [
        'middleware' => 'permission:create-category',
        'uses' => '\PhpSoft\ShoppingCart\Controllers\CategoryController@store'
    ]);
    Route::put('categories/{id}', [
        'middleware' => 'permission:update-category',
        'uses' => '\PhpSoft\ShoppingCart\Controllers\CategoryController@update'
    ]);
    Route::delete('categories/{id}', [
        'middleware' => 'permission:delete-category',
        'uses' => '\PhpSoft\ShoppingCart\Controllers\CategoryController@destroy'
    ]);
});

Route::get('categories/{id}/products', '\PhpSoft\ShoppingCart\Controllers\ProductController@index');

// products resource
Route::get('products', '\PhpSoft\ShoppingCart\Controllers\ProductController@index');
Route::get('products/{id}', '\PhpSoft\ShoppingCart\Controllers\ProductController@show');
Route::group(['middleware'=>'auth'], function() { // use middleware jwt.auth if use JSON Web Token
    Route::post('products', [
        'middleware' => 'permission:create-product',
        'uses' => '\PhpSoft\ShoppingCart\Controllers\ProductController@store'
    ]);
    Route::put('products/{id}', [
        'middleware' => 'permission:update-product',
        'uses' => '\PhpSoft\ShoppingCart\Controllers\ProductController@update'
    ]);
    Route::delete('products/{id}', [
        'middleware' => 'permission:delete-product',
        'uses' => '\PhpSoft\ShoppingCart\Controllers\ProductController@destroy'
    ]);
});
