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
Route::get('/admin/users/{user}/assignments', ['uses'=>'AdminController@user_assignments']);
Route::get('/admin/groups', ['uses'=>'AdminController@groups']);
Route::get('/admin/groups/{group}/members', ['uses'=>'AdminController@group_members']);
Route::get('/admin/modules', ['uses'=>'AdminController@modules']);
Route::get('/admin/modules/{module}/versions', ['uses'=>'AdminController@module_versions']);
Route::get('/admin/modules/{module}/permissions', ['uses'=>'AdminController@module_permissions']);

/* End Admin Pages */

Route::get('/logout','UserDashboardController@logout');

Route::any('/demo', ['uses' => 'DemoController@list']);

Route::group(['prefix' => 'api'], function () {
    Route::get('/tincan/activities/state', 'TinCanController@get_state');
    Route::put('/tincan/activities/state', 'TinCanController@set_state');
    Route::put('/tincan/statements', 'TinCanController@register_statement');

    /* User Methods */
    Route::get('/users','UserController@get_all_users');
    Route::get('/users/search/{search_string?}','UserController@search');
    Route::get('/users/{user}','UserController@get_user');
    Route::post('/users','UserController@add_user')->middleware('can:manage_users,App\User');
    Route::put('/users/{user}','UserController@update_user')->middleware('can:manage_users,App\User');
    Route::delete('/users/{user}','UserController@delete_user')->middleware('can:manage_users,App\User');
    Route::put('/users/{user}/permissions','UserController@set_permissions')->middleware('can:manage_users,App\User');
    Route::get('/users/{user}/permissions','UserController@get_permissions')->middleware('can:manage_users,App\User');
    
    Route::get('/users/{user}/assignments','UserController@get_assignments');
    Route::post('/users/{user}/assignments','UserController@set_assignment');
    Route::delete('/users/{user}/assignments/{module_assignment}','UserController@delete_assignment');
    // Can you update an assignment?  I'm thinking no...
    // Route::put('/users/{user}/assignments/{module_assignment}','UserController@update_assignment');

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
    Route::get('/modules/{module}/permissions','ModuleController@get_module_permissions');
    Route::put('/modules/{module}/permissions','ModuleController@set_module_permission');
    Route::delete('/modules/{module}/permissions/{module_permission}','ModuleController@delete_module_permission');

    /* Group Methods */
    Route::get('/groups','GroupController@get_all_groups');
    Route::get('/groups/{group}','GroupController@get_group');
    Route::post('/groups','GroupController@add_group');
    Route::put('/groups/{group}','GroupController@update_group');
    Route::delete('/groups/{group}','GroupController@delete_group');
    Route::get('/groups/{group}/members','GroupController@get_members');
    Route::post('/groups/{group}/members','GroupController@add_member');
    Route::delete('/groups/{group}/members/{user}','GroupController@delete_member');

//    Route::get('/groups/users','GroupController@get_group_memberships');
    Route::post('/groups/{group}/users/{user}','GroupController@add_group_membership');
    Route::delete('/groups/{group}/users/{user}','GroupController@delete_group_membership');
});