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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', ['uses'=>'UserDashboardController@home']);
Route::get('/logout','UserDashboardController@logout');

Route::any('/demo', ['uses' => 'DemoController@list']);

Route::group(['prefix' => 'api'], function () {
    Route::get('/tincan/activities/state', 'TinCanController@get_state');
    Route::put('/tincan/activities/state', 'TinCanController@set_state');
    Route::put('/tincan/statements', 'TinCanController@register_statement');


    /* User Methods */
    Route::get('/users','UserController@get_all_users');
    Route::get('/users/{user}','UserController@get_user');
    Route::post('/users','UserController@add_user');
    Route::put('/users/{user}','UserController@update_user');
    Route::delete('/users/{user}','UserController@delete_user');
    Route::post('/users/{user}/assign/{module_version}','UserController@assign_module');
    Route::post('/users/{user}/permissions','UserController@set_permissions');

    Route::post('/us ers/{user}/groups/{group}');
    Route::delete('/users/{user}/groups/{group}');


    /* Modules Methods */
    Route::get('/modules','ModuleController@get_all_modules');
    Route::get('/modules/{module}','ModuleController@get_module');
    Route::post('/modules/','ModuleController@add_module');
    Route::put('/modules/{module}','ModuleController@update_module');
    Route::delete('/modules/{module}','ModuleController@delete_module');
    Route::post('/modules/{module}/permissions','ModuleController@set_permissions');


});