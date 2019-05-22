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
	Route::put('seasons','SeasonsController@create');
	Route::post('seasons/{id}','SeasonsController@update');
	Route::delete('seasons/{id}','SeasonsController@delete');
	Route::get('user_types','UserTypesController@all');
	Route::get('score_types/','ScoresController@score_types');
	Route::get('score_types/{id}','ScoresController@score_types');


	Route::get('sections','SectionsController@all');
	Route::get('sections/{id}','SectionsController@get');
	Route::put('sections','SectionsController@create');
	Route::post('sections/{id}','SectionsController@update');
	Route::delete('sections/{id}','SectionsController@delete');
	Route::post('sections/{id}/import','SectionsController@importFile');

	Route::get('sections/{id}/students','SectionsController@students');
	Route::get('sections/{id}/attendance','SectionsController@attendance');
	Route::post('sections/{id}/attendance','SectionsController@updateAttendance');
	Route::put('sections/{id}/attendance','SectionsController@addAttendance');
	Route::delete('sections/{id}/attendance/{section_attendance_id}','SectionsController@deleteAttendance');


	Route::get('sections/{id}/scores/{type_id}','ScoresController@scores');
	Route::put('sections/{section_id}/scores/{type_id}','ScoresController@addScore');
	Route::delete('sections/{section_id}/scores/{type_id}/{score_id}','ScoresController@deleteScore');
	Route::post('sections/{section_id}/scores/{type_id}/{score_id}','ScoresController@updateScore');
	Route::post('sections/{section_id}/scores/{type_id}/{score_id}/{student_id}','ScoresController@updateStudentScore');

	Route::get('students','StudentsController@all');
	Route::post('students/add','StudentsController@create');
	Route::get('students/{id}','StudentsController@get');
	Route::post('students/{id}','StudentsController@update');
	Route::get('students/{id}/attendance/{section_id}','StudentsController@attendance');
	Route::get('students/{id}/scores/{section_id}','StudentsController@scores');
	Route::post('students/{id}/transfer','StudentsController@transfer');

	Route::post('user/password','UsersController@update_password');

	Route::get('users','UsersController@all');
	Route::post('users','UsersController@create');
	Route::get('users/{id}','UsersController@get');
	Route::post('users/{id}','UsersController@update');

	Route::post('export/grades','ExportController@export');
	Route::post('export/print','ExportController@createPrintFile');
	Route::post('export/class-lists','ExportController@class_lists');
});