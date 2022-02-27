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
    Route::post('login', 'App\Http\Controllers\AuthController@login')->name('auth.login');
    Route::post('register', 'App\Http\Controllers\AuthController@register')->name('auth.register');

    Route::group([
        'middleware' => 'auth:api',
    ], function () {
        Route::get('logout', 'App\Http\Controllers\AuthController@logout')->name('auth.logout');
        Route::get('user', 'App\Http\Controllers\AuthController@user')->name('auth.user');
    });
});

Route::apiResource(
    'transactionCategory',
    'App\Http\Controllers\TransactionCategoryController',
    ['except' => ['update', 'destroy']]
)->middleware('auth:api');

Route::get(
    'transaction/history',
    'App\Http\Controllers\TransactionController@history'
)->name('transaction.history')->middleware('auth:api');
Route::apiResource(
    'transaction',
    'App\Http\Controllers\TransactionController'
)->middleware('auth:api');

