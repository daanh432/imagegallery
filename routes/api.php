<?php

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
Route::namespace('Api')->group(function () {

    // Authentication routes
    Route::prefix('/auth')->group(function () {
        Route::post('/register', 'AuthController@register');
        Route::post('/login', 'AuthController@login');
        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('/user', 'AuthController@user');
            Route::get('/refresh', 'AuthController@refresh');
            Route::post('/logout', 'AuthController@logout');
        });
    });

    // Send contact email route
    Route::post('/contact', 'ContactController@create');

    // User routes
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('/users', 'UserController@index');
        Route::get('/users/{user}', 'UserController@show');
    });
});

// Capture api routes from vue router capture
Route::get('{any}', 'SpaController@NotFound')->where('any', '.*');
Route::post('{any}', 'SpaController@NotFound')->where('any', '.*');
Route::patch('{any}', 'SpaController@NotFound')->where('any', '.*');
Route::delete('{any}', 'SpaController@NotFound')->where('any', '.*');
