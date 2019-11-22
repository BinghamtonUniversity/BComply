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
Route::get('/admin/reports', ['uses'=>'AdminController@reports']);
Route::get('/admin/reports/{report}/run', ['uses'=>'AdminController@run_report']);

/* End Admin Pages */

Route::get('/logout','UserDashboardController@logout');
Route::any('/demo', ['uses' => 'DemoController@list']);
Route::get('/assignment/{module_assignment}','ModuleAssignmentController@run');

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
    Route::put('/users/{user}/permissions','UserController@set_permissions')->middleware('can:manage_user_permissions,App\User');
    Route::get('/users/{user}/permissions','UserController@get_permissions')->middleware('can:manage_user_permissions,App\User');
    
    Route::get('/users/{user}/assignments','UserController@get_assignments')->middleware('can:manage_users,App\User');
    Route::post('/users/{user}/assignments','UserController@set_assignment')->middleware('can:manage_users,App\User');
    Route::delete('/users/{user}/assignments/{module_assignment}','UserController@delete_assignment')->middleware('can:manage_users,App\User');
    // Can you update an assignment?  I'm thinking no...
    // Route::put('/users/{user}/assignments/{module_assignment}','UserController@update_assignment');

    // Route::post('/users/{user}/groups/{group}');
    // Route::delete('/users/{user}/groups/{group}');

    /* Modules Methods */
    Route::get('/modules','ModuleController@get_all_modules');
    Route::get('/modules/{module}','ModuleController@get_module')->middleware('can:run_report,module');
    Route::post('/modules','ModuleController@add_module')->middleware('can:manage_all_modules,App\Module');
    Route::put('/modules/{module}','ModuleController@update_module')->middleware('can:manage_module,module');
    Route::delete('/modules/{module}','ModuleController@delete_module')->middleware('can:manage_module,module');

    Route::get('/modules/{module}/versions','ModuleController@get_module_versions');
    Route::get('/module_versions','ModuleController@get_module_versions');
    Route::post('/modules/{module}/versions','ModuleController@add_module_version')->middleware('can:manage_module,module');
    Route::put('/modules/{module}/versions/{module_version}','ModuleController@update_module_version')->middleware('can:manage_module,module');
    Route::delete('/modules/{module}/versions/{module_version}','ModuleController@delete_module_version')->middleware('can:manage_module,module');

    Route::get('/modules/{module}/permissions','ModuleController@get_module_permissions');
    Route::put('/modules/{module}/permissions','ModuleController@set_module_permission')->middleware('can:manage_module,module');
    Route::delete('/modules/{module}/permissions/{module_permission}','ModuleController@delete_module_permission')->middleware('can:manage_module,module');

    /* Group Methods */
    Route::get('/groups','GroupController@get_all_groups');
    Route::get('/groups/{group}','GroupController@get_group')->middleware('can:manage_groups,App\Group');
    Route::post('/groups','GroupController@add_group')->middleware('can:manage_groups,App\Group');
    Route::put('/groups/{group}','GroupController@update_group')->middleware('can:manage_groups,App\Group');
    Route::delete('/groups/{group}','GroupController@delete_group')->middleware('can:manage_groups,App\Group');
    Route::get('/groups/{group}/members','GroupController@get_members')->middleware('can:manage_groups,App\Group');
    Route::post('/groups/{group}/members','GroupController@add_member')->middleware('can:manage_groups,App\Group');
    Route::delete('/groups/{group}/members/{user}','GroupController@delete_member')->middleware('can:manage_groups,App\Group');

//    Route::get('/groups/users','GroupController@get_group_memberships');
    Route::post('/groups/{group}/users/{user}','GroupController@add_group_membership')->middleware('can:manage_groups,App\Group');
    Route::delete('/groups/{group}/users/{user}','GroupController@delete_group_membership')->middleware('can:manage_groups,App\Group');

    Route::get('/reports','ReportController@get_all_reports');
    Route::get('/reports/{report}','ReportController@get_report');
    Route::post('/reports','ReportController@add_report');
    Route::put('/reports/{report}','ReportController@update_report');
    Route::delete('/reports/{report}','ReportController@delete_report');
    Route::get('/reports/tables', 'ReportController@get_tables');
    Route::get('/reports/tables/columns', 'ReportController@get_columns');
    Route::get('/reports/{report}/execute', 'ReportController@execute');
});