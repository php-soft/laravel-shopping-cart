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

Route::get('products', '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@index');
Route::get('products/{id}', '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@show');
Route::group(['middleware'=>'auth'], function() { // use middleware jwt.auth if use JSON Web Token
    Route::post('products', '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@store');
    Route::put('products/{id}', '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@update');
    Route::delete('products/{id}', '\PhpSoft\Illuminate\ShoppingCart\Controllers\ProductController@destroy');
});

Route::get('categories', '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@index');
Route::get('categories/{id}', '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@show');
Route::group(['middleware'=>'auth'], function() { // use middleware jwt.auth if use JSON Web Token
    Route::post('categories', '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@store');
    Route::put('categories/{id}', '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@update');
    Route::delete('categories/{id}', '\PhpSoft\Illuminate\ShoppingCart\Controllers\CategoryController@destroy');
});
