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



Auth::routes();
Route::get('/',function(){
	if(Auth::check()){
		return redirect('admin');
		
	}else{
		return redirect('login');
		
	}
});
Route::get('admin', 'HomeController@index')->name('admin_home');
Route::get('export/{id}','ExportController@get');

