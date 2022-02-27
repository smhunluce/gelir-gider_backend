<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'auth',
], function () {
    Route::post('login', 'App\Http\Controllers\AuthController@login');
    Route::post('register', 'App\Http\Controllers\AuthController@register');

    Route::group([
        'middleware' => 'auth:api',
    ], function () {
        Route::get('logout', 'App\Http\Controllers\AuthController@logout');
        Route::get('user', 'App\Http\Controllers\AuthController@user');
    });
});

Route::apiResource(
    'transactionCategory',
    'App\Http\Controllers\TransactionCategoryController',
    ['except' => ['update', 'destroy']]
)->middleware('auth:api');
Route::apiResource(
    'transaction',
    'App\Http\Controllers\TransactionController'
)->middleware('auth:api');

