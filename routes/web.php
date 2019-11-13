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

Route::any('/demo', ['uses' => 'DemoController@list']);

Route::group(['prefix' => 'api'], function () {
    Route::get('/tincan/activities/state', 'TinCanController@get_state');
    Route::put('/tincan/activities/state', 'TinCanController@set_state');
    Route::put('/tincan/statements', 'TinCanController@register_statement');
});