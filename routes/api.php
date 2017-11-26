<?php

use Illuminate\Http\Request;

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

Route::get('request-token', 'ApiController@requestToken');

Route::group(['middleware' => ['auth.device']], function () {
	Route::get('/', 'VideoController@index');
	Route::get('status/{id}', 'VideoController@status');
	Route::get('restart/{id}', 'VideoController@restart');
	Route::post('trim', 'VideoController@trim');
});

