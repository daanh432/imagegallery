<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/api', 'SpaController@NotFound');

Route::get('/', 'SpaController@index')->name('index');
Route::get('/{any}', 'SpaController@index')->where('any', '.*');
