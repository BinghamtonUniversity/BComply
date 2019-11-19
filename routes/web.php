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

/* Admin Pages */
Route::get('/admin/', ['uses'=>'AdminController@admin']);
Route::get('/admin/users', ['uses'=>'AdminController@users']);
Route::get('/admin/groups', ['uses'=>'AdminController@groups']);
Route::get('/admin/modules', ['uses'=>'AdminController@modules']);
Route::get('/admin/modules/{module}/versions', ['uses'=>'AdminController@module_versions']);
/* End Admin Pages */

Route::get('/logout','UserDashboardController@logout');

Route::any('/demo', ['uses' => 'DemoController@list']);

Route::group(['prefix' => 'api'], function () {
    Route::get('/tincan/activities/state', 'TinCanController@get_state');
    Route::put('/tincan/activities/state', 'TinCanController@set_state');
    Route::put('/tincan/statements', 'TinCanController@register_statement');


    /* User Methods */
    Route::get('/users','UserController@get_all_users');
    Route::get('/users/{user}','UserController@get_user');
    Route::post('/users','UserController@add_user')->middleware('can:manage_users,App\User');
    Route::put('/users/{user}','UserController@update_user')->middleware('can:manage_users,App\User');
    Route::delete('/users/{user}','UserController@delete_user')->middleware('can:manage_users,App\User');
    Route::put('/users/{user}/permissions','UserController@set_permissions')->middleware('can:manage_users,App\User');
    Route::get('/users/{user}/permissions','UserController@get_permissions')->middleware('can:manage_users,App\User');
    Route::post('/users/{user}/assign/{module_version}','UserController@assign_module');

    // Route::post('/users/{user}/groups/{group}');
    // Route::delete('/users/{user}/groups/{group}');

    /* Modules Methods */
    Route::get('/modules','ModuleController@get_all_modules');
    Route::get('/modules/{module}','ModuleController@get_module');
    Route::post('/modules','ModuleController@add_module');
    Route::put('/modules/{module}','ModuleController@update_module');
    Route::delete('/modules/{module}','ModuleController@delete_module');
    Route::get('/modules/{module}/versions','ModuleController@get_module_versions');
    Route::post('/modules/{module}/versions','ModuleController@add_module_version');
    Route::put('/modules/{module}/versions/{module_version}','ModuleController@update_module_version');
    Route::delete('/modules/{module}/versions/{module_version}','ModuleController@delete_module_version');
    Route::post('/modules/{module}/permissions','ModuleController@set_permissions');

    /* Group Methods */
    Route::get('/groups','GroupController@get_all_groups');
    Route::get('/groups/{group}','GroupController@get_group');
    Route::post('/groups','GroupController@add_group');
    Route::put('/groups/{group}','GroupController@update_group');
    Route::delete('/groups/{group}','GroupController@delete_group');

//    Route::get('/groups/users','GroupController@get_group_memberships');
    Route::post('/groups/{group}/users/{user}','GroupController@add_group_membership');
    Route::delete('/groups/{group}/users/{user}','GroupController@delete_group_membership');
});