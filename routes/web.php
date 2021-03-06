<?php

Route::any('/external', ['uses' => 'ExternalController@list']);
Route::any('/login', ['uses' => 'CASController@login']);
Route::get('/logout','UserDashboardController@logout');

Route::group(['middleware'=>['custom.auth']], function () {
    /* User Pages */
    Route::get('/',['uses' => 'UserDashboardController@my_assignments']);
    Route::get('/history', ['uses'=>'UserDashboardController@assignment_history']);
    Route::get('/shop',['uses'=>'UserDashboardController@shop_courses']);
    Route::get('/assignment/{module_assignment}','ModuleAssignmentController@run');
    Route::get('/assignment/{module_assignment}/certificate', 'ModuleAssignmentController@certificate')->middleware('can:certificate_policy,module_assignment');
    Route::get('/module/{module}','UserDashboardController@module_redirect');

    /* Admin Pages */
    Route::group(['prefix' => 'admin'], function () {
        Route::get('/', ['uses'=>'AdminController@admin']);
        Route::get('/users', ['uses'=>'AdminController@users']);
        Route::get('/users/{user}/assignments', ['uses'=>'AdminController@user_assignments']);
        Route::get('/groups', ['uses'=>'AdminController@groups']);
        Route::get('/groups/{group}/members', ['uses'=>'AdminController@group_members']);
        Route::get('/modules', ['uses'=>'AdminController@modules']);
        Route::get('/modules/{module}/versions', ['uses'=>'AdminController@module_versions']);
        Route::get('/modules/{module}/permissions', ['uses'=>'AdminController@module_permissions']);
        Route::get('/modules/{module}/assignments', ['uses'=>'AdminController@module_assignments']);
        Route::get('/reports', ['uses'=>'AdminController@reports']);
        Route::get('/reports/{report}/run', ['uses'=>'AdminController@run_report']);
        Route::get('/bulk_assignments', ['uses'=>'AdminController@bulk_assignments']);
        Route::get('/bulk_assignments/{bulk_assignment}/run', ['uses'=>'AdminController@run_assignment']);
    });

    Route::group(['prefix' => 'api'], function () {

        /* Articulate Tincan Integration */
        Route::get('/tincan/activities/state', 'TinCanController@get_state');
        Route::put('/tincan/activities/state', 'TinCanController@set_state');
        Route::put('/tincan/statements', 'TinCanController@register_statement');

        /* Articulate Tincan Integration */
        Route::get('/video/state/{assignment}', 'VideoController@get_duration');
        Route::put('/video/state/{assignment}', 'VideoController@set_duration');
        Route::put('/video/statements/{assignment}', 'VideoController@register_statement');

        /* User Methods */
        Route::get('/users','UserController@get_all_users')->middleware('can:view_in_admin,App\User');
        Route::get('/users/search/{search_string?}','UserController@search');
        Route::get('/users/{user}','UserController@get_user');
        Route::post('/users/bulk_inactivate','UserController@bulk_inactivate')->middleware('can:manage_users,App\User');
        Route::post('/users','UserController@add_user')->middleware('can:manage_users,App\User');
        Route::put('/users/{user}','UserController@update_user')->middleware('can:manage_users,App\User');
        Route::delete('/users/{user}','UserController@delete_user')->middleware('can:manage_users,App\User');
        Route::put('/users/{source_user}/merge_into/{target_user}','UserController@merge_user')->middleware('can:manage_users,App\User');
        Route::put('/users/{user}/permissions','UserController@set_permissions')->middleware('can:manage_user_permissions,App\User');
        Route::get('/users/{user}/permissions','UserController@get_permissions')->middleware('can:manage_user_permissions,App\User');
        Route::post('/users/assignments/{module}','UserController@self_assignment');
        Route::get('/users/{user}/assignments','UserController@get_assignments')->middleware('can:manage_users,App\User');
        Route::post('/users/{user}/assignments/{module}','UserController@set_assignment')->middleware('can:assign_module,App\User,module');
        Route::delete('/users/{user}/assignments/{module_assignment}','UserController@delete_assignment')->middleware('can:delete_assignment,App\User,module_assignment');
        Route::post('/login/{user}','UserController@login_user')->middleware('can:impersonate_users,App\User');

        /* Modules Methods */
        Route::get('/modules','ModuleController@get_all_modules')->middleware('can:view_in_admin,App\Module');
        Route::get('/modules/{module}','ModuleController@get_module')->middleware('can:view_module,module');
        Route::post('/modules','ModuleController@add_module')->middleware('can:create_modules,App\Module');
        Route::put('/modules/{module}','ModuleController@update_module')->middleware('can:manage_module,module');
        Route::delete('/modules/{module}','ModuleController@delete_module')->middleware('can:delete_module,module');

        Route::get('/modules/{module}/versions','ModuleController@get_module_versions');
        Route::get('/module_versions','ModuleController@get_module_versions');
        Route::get('/module_versions/public','ModuleController@get_public_module_versions');
        Route::post('/modules/{module}/versions','ModuleController@add_module_version')->middleware('can:manage_module,module');
        Route::put('/modules/{module}/versions/{module_version}','ModuleController@update_module_version')->middleware('can:manage_module,module');

        Route::delete('/modules/{module}/versions/{module_version}','ModuleController@delete_module_version')->middleware('can:manage_module,module');
        Route::get('/modules/{module}/versions/{module_version}/assignments','ModuleController@get_module_version_assignments');
        Route::post('/modules/{module}/versions/{module_version}/upload', 'FileUploadController@upload');//->middleware('can:manage_bulk_assignments, App\BulkAssignment');
        Route::get('/modules/{module}/versions/{module_version}/exists', 'FileUploadController@exists');//->middleware('can:manage_bulk_assignments, App\BulkAssignment');
        Route::get('/modules/{module}/assignments','ModuleController@get_module_assignments');
        Route::get('/modules/{module}/permissions','ModuleController@get_module_permissions');
        Route::put('/modules/{module}/permissions','ModuleController@set_module_permission')->middleware('can:manage_module,module');
        Route::delete('/modules/{module}/permissions/{module_permission}','ModuleController@delete_module_permission')->middleware('can:manage_module,module');

        /* Group Methods */
        Route::get('/groups','GroupController@get_all_groups')->middleware('can:view_in_admin,App\Group');
        Route::get('/groups/{group}','GroupController@get_group')->middleware('can:manage_groups,App\Group');
        Route::post('/groups','GroupController@add_group')->middleware('can:manage_groups,App\Group');
        Route::put('/groups/{group}','GroupController@update_group')->middleware('can:manage_groups,App\Group');
        Route::delete('/groups/{group}','GroupController@delete_group')->middleware('can:manage_groups,App\Group');
        Route::get('/groups/{group}/members','GroupController@get_members')->middleware('can:manage_groups,App\Group');
        Route::post('/groups/{group}/members','GroupController@add_member')->middleware('can:manage_groups,App\Group');
        Route::delete('/groups/{group}/members/{user}','GroupController@delete_member')->middleware('can:manage_groups,App\Group');
        Route::post('/groups/{group}/users/bulk_add','GroupController@bulk_add_members')->middleware('can:manage_groups,App\Group');
        Route::post('/groups/{group}/users/{user}','GroupController@add_group_membership')->middleware('can:manage_group_membership,App\Group');
        Route::delete('/groups/{group}/users/{user}','GroupController@delete_group_membership')->middleware('can:manage_group_membership,App\Group');

        /* Report Methods */
        Route::get('/reports','ReportController@get_all_reports')->middleware('can:view_reports, App\Report');
        Route::get('/reports/{report}','ReportController@get_report')->middleware('can:view_reports, App\Report');
        Route::post('/reports','ReportController@add_report')->middleware('can:manage_reports, App\Report');
        Route::put('/reports/{report}','ReportController@update_report')->middleware('can:update_report,report');
        Route::delete('/reports/{report}','ReportController@delete_report')->middleware('can:update_report,report');
        Route::get('/reports/tables', 'ReportController@get_tables')->middleware('can:view_reports, App\Report');
        Route::get('/reports/tables/columns', 'ReportController@get_columns')->middleware('can:view_reports, App\Report');
        Route::get('/reports/{report}/execute', 'ReportController@execute')->middleware('can:view_reports, App\Report');

        /*Bulk Assignments Methods*/
        Route::get('/bulk_assignments', 'BulkAssignmentController@get_all_bulk_assignments')->middleware('can:manage_bulk_assignments, App\BulkAssignment');
        Route::get('/bulk_assignments/{bulk_assignment}', 'BulkAssignmentController@get_bulk_assignment')->middleware('can:manage_bulk_assignments, App\BulkAssignment');
        Route::post('/bulk_assignments', 'BulkAssignmentController@add_bulk_assignment')->middleware('can:manage_bulk_assignments, App\BulkAssignment');
        Route::put('/bulk_assignments/{bulk_assignment}', 'BulkAssignmentController@update_bulk_assignment')->middleware('can:manage_bulk_assignments, App\BulkAssignment');
        Route::delete('/bulk_assignments/{bulk_assignment}','BulkAssignmentController@delete_bulk_assignment')->middleware('can:manage_bulk_assignments, App\BulkAssignment');
        Route::get('/bulk_assignments/tables', 'BulkAssignmentController@get_tables')->middleware('can:manage_bulk_assignments,  App\BulkAssignment');
        Route::get('/bulk_assignments/tables/columns', 'BulkAssignmentController@get_columns')->middleware('can:manage_bulk_assignments, App\BulkAssignment');
        Route::get('/bulk_assignments/{bulk_assignment}/execute/{test?}', 'BulkAssignmentController@execute')->middleware('can:manage_bulk_assignments, App\BulkAssignment');

        /* Module Assignment Bulk Completion */
        Route::put('/assignment/{module_assignment}/complete','ModuleAssignmentController@check_complete')->middleware('can:complete_policy, App\ModuleAssignment,module_assignment');

        //php artisan migrate:refresh --seed
        Route::get('/db/refresh','AdminController@refresh_db');
    });
});