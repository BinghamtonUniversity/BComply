<?php

use Illuminate\Http\Request;

// All Public API Routes are prepended by /api/public
// as per the RouteServiceProvider Controller
//
// You must authenticate with a valid username / password
// as specified by: API_USER and API_PASS 
// in your .env file

Route::any('/sync','PublicAPIController@sync');
Route::get('/cron', function () {
    $exitCode = Artisan::call('schedule:run');
    return ['code'=>$exitCode];
});
// Users
Route::get('/users/{unique_id}','PublicAPIController@get_user');
Route::post('/users','PublicAPIController@create_user');
Route::put('/users/{unique_id}','PublicAPIController@update_user');
Route::get('/users/{unique_id}/assignments','PublicAPIController@get_user_assignments');

// Modules
Route::get('/modules/{module}/assignments','PublicAPIController@get_module_assignments');

// Groups
Route::post('/groups/{group_slug}/users/{unique_id}','PublicAPIController@add_group_membership');
Route::delete('/groups/{group_slug}/users/{unique_id}','PublicAPIController@delete_group_membership');

//todo New Impersonate
//Use the same pass_reset methods
Route::get('/users/{unique_id}/impersonate', 'PublicAPIController@impersonate_user');