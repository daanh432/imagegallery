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
        Route::post('/register', 'AuthController@register')->middleware('throttle:6,5');
        Route::post('/login', 'AuthController@login')->middleware('throttle:6,5');
        Route::group(['middleware' => 'auth:api'], function () {
            Route::get('/user', 'AuthController@user');
            Route::get('/refresh', 'AuthController@refresh');
            Route::post('/logout', 'AuthController@logout');
        });
    });

    // Send contact email route
    Route::post('/contact', 'ContactController@create');

    // Image show route private / password / public
    Route::get('/users/{user}/images/{image}', 'ImagesController@show')->name('users.images.show')->where('image', '(.*)');
    // Album show route private / password / public
    Route::get('/users/{user}/albums/{album}', 'AlbumsController@show');

    // Logged in routes
    Route::group(['middleware' => 'auth:api'], function () {
        // User profile routes
        Route::get('/users', 'UserController@index');
        Route::get('/users/{user}', 'UserController@show');

        // User image overview routes
        Route::get('/users/{user}/images', 'ImagesController@index');
        Route::post('/users/{user}/images', 'ImagesController@store');
        Route::patch('/users/{user}/images/{image}', 'ImagesController@update');
        Route::delete('/users/{user}/images/{image}', 'ImagesController@destroy');

        // User album overview routes
        Route::get('/users/{user}/albums', 'AlbumsController@index');
        Route::post('/users/{user}/albums', 'AlbumsController@store');
        Route::patch('/users/{user}/albums/{album}', 'AlbumsController@update');
        Route::delete('/users/{user}/albums/{album}', 'AlbumsController@destroy');
    });
});

// Capture api routes from vue router capture
Route::get('{any}', 'SpaController@NotFound')->where('any', '.*');
Route::post('{any}', 'SpaController@NotFound')->where('any', '.*');
Route::patch('{any}', 'SpaController@NotFound')->where('any', '.*');
Route::delete('{any}', 'SpaController@NotFound')->where('any', '.*');
