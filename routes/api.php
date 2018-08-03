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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function(){
	Route::get('seasons','SeasonsController@all');
	Route::get('seasons/latest','SeasonsController@latest');

	Route::get('sections','SectionsController@all');
	Route::get('sections/{id}','SectionsController@get');
	Route::post('sections/{id}','SectionsController@update');
	Route::delete('sections/{id}','SectionsController@delete');
});