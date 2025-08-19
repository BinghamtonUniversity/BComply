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


/**
 * Get all assignments for all users
 *  parameters: 
 *      module_id (required) - only return records that are from this module
 *      assigned_after (optional) - only return records that were assigned after a specific date (formatted as 2025-04-29)
 *      updated_after (optional) - only return records that were updated after a specific date (formatted as 2025-04-29)
 *      updated_before (optional) - only return records that were updated before a specific date (formatted as 2025-04-29)
 *      current_version (optional) - if false, then all version, else only the current version
 *      status (optional) - only return the passed statuses
 *    -- all of the above are inclusive (>= or <=) so the names are slightly misleading
 * 
 * test - http://bcomplydev.local:8000/api/public/assignments
 */

Route::get('/assignments', 'PublicAPIController@get_all_assignments');

/**
 * Get all the assignments that fit a user
 *  return rows from the assignments table
 * 
 * test - http://bcomplydev.local:8000/api/public/users/B00168387/assignments
 */
Route::get('/users/{unique_id}/assignments','PublicAPIController@get_user_assignments');

// Modules
/**
 *  lookup module 
 *  parameters:
 *      module_name (required) - name to look up, can include %s for LIKE comparisons
 *                               if omitted return all modules
 *      
 *  returns:
 *      rows from modules table
 * 
 *  test - http://bcomplydev.local:8000/api/public/modules?module_name=%test%
 *         http://bcomplydev.binghamton.edu/api/public/modules?module_name=%test%
 */
Route::get('/modules','PublicAPIController@get_modules_by_name');

/**
 *  lookup all the module assignments (not necessarily with status assigned - status can be anything)
 *   parameters:
 *      assigned_after (optional) - only return records that were assigned after a specific date (formatted as 2025-04-29)
 *      updated_after (optional) - only return records that were updated after a specific date (formatted as 2025-04-29)
 *      updated_before (optional) - only return records that were updated before a specific date (formatted as 2025-04-29)
 *      current_version (optional) - if false, then return all versions
 *      status (optional) - an array of statuses
 *    -- all of the above are inclusive (>= or <=) so the names are slightly misleading
 * 
 *  test - http://bcomplydev.local:8000/api/public/modules/2/assignments
 */
Route::get('/modules/{module}/assignments','PublicAPIController@get_module_assignments');


/**
 *  lookup all the assignments data
 *   parameters:
 *      version (optional) - only return completions of a specific version
 *      current_version (optional) - boolean to only return the current version - default to true
 *      grace_period (optional) - used only if current version is true - number of days users have to complete a new version once it's created
 *      completed_after (optional) - only return records that were completed after a specific date (formatted as 2025-04-29)
 *      status (optional) - an array that specifies which statuses should be returned
 *  test - http://bcomplydev.local:8000/api/public/modules/2/assignment_data
 */
Route::get('/modules/{module}/assignments_data','PublicAPIController@get_module_assignments_data');

/**
 * get the status of an assignment for a user
 *  parameters:
 *      assigned_after (optional) - only return records that were assigned after a specific date (formatted as 2025-04-29)
 *      completed_after (optional) - only return records that were completed after a specific date (formatted as 2025-04-29)
 *      status (optional) - only return records that have the status specified
 *      current_version (optional) - boolean to only return the current version
 *  returns:
 *      all rows from module_assignments for student and assignment
 * 
 *  test - http://bcomplydev.local:8000/api/public/modules/2/users/B00168387
 */
Route::get('/modules/{module}/users/{unique_id}','PublicAPIController@get_user_module_status');

/**
 * Assigns a module to a user
 *  parameters:
 *      due_date (required) - (formated as 2025-04-29) - null if omitted
 * 
 * test - http://bcomplydev.local:8000/api/public/modules/2/users/B00168387?due_date=2025-08-15
 */
Route::post('/modules/{module}/users/{unique_id}', 'PublicAPIController@assign_module_to_user');

// Groups
/**
 *  lookup groups  
 *  parameters:
 *      group_name (required) - name to look up, can include %s for LIKE comparisons
 *                               if omitted return all groups
 *  returns:
 *      rows from groups table
 * 
 *  test - http://bcomplydev.local:8000/api/public/groups?group_name=%test%
 */
Route::get('/groups', 'PublicAPIController@get_groups_by_name');

/**
 * adds a user to a group (or updates the user if they already exist in the group)
 * 
 *  test - http://bcomplydev.local:8000/api/public/groups/api_group1/users/B00168387
 */
Route::post('/groups/{group_slug}/users/{unique_id}','PublicAPIController@add_group_membership');

/**
 * removes a user from a group
 * 
 *  test - http://bcomplydev.local:8000/api/public/groups/api_group1/users/B00168387
 */
Route::delete('/groups/{group_slug}/users/{unique_id}','PublicAPIController@delete_group_membership');

/**
 *  gets all module assignments for the users of a group where the module id = module
 *  parameters:
 *      assigned_after (optional) - only return records that were assigned after a specifice date (formated as 2025-04-29)
 *      completed_after (optional) - returns the completed status after the date for all of the users of the group 
 *      version (optional) 
 *      current_version (optional) - default to true unless version is specified
 *  returns: 
 *      all rows for module_assigments that fit the criteria plus the module_name, group_name, user_name, and boolean for completed
 * 
 *  test - http://bcomplydev.local:8000/api/public/groups/test_group/modules/2?assigned_after=2025-01-01
 */
Route::get('/groups/{slug}/modules/{module}','PublicAPIController@get_group_module_status');

/**
 * Assign module for everyone in a group
 *  parameters:
 *      not_completed_after (optional): don't assign it to anyone that has complete the module after date (fomatted like after=2025-05-01)
 *      version (optional): assign a specific version, if not passed the current version is used
 *      due_date (optional): enter a due date for the assignment specified like 2025-05-01
 *  returns:
 *      the module that was assigned or an error message
 * 
 *  test - http://bcomplydev.local:8000/api/public/groups/test_group/modules/2?due_date=2026-01-01
 */

//Route::post('/groups/{slug}/modules/{module}','BulkAssignmentController@assign_module_to_group_members'); 

/**
 * Gets all users that are in a group
 *  parameters:
 *      none
 *  returns:
 *      the users in the group
 * 
 *  - http://bcomplydev.local:8000/api/public/groups/1
 */
Route::get('/groups/{group}','PublicAPIController@get_all_group_users'); 

/**
 * Create a group with the group slug
 *  parameters:
 *      group_name (optional): if not given, the group name is the same as the slug
 *  returns:
 *      the group row or a message why the group could not be created
 * 
 *  test - http://bcomplydev.local:8000/api/public/groups/api_group5?group_name=New%20group5
 */
Route::post('/groups/{slug}','PublicAPIController@create_group'); 


//Workshops
/**
 *  lookup workshop  
 *  parameters:
 *      workshop_name (optional) - name to look up, can include %s for LIKE comparisons
 *                               if omitted return all groups
 *  returns:
 *      rows from workshops table
 */

 Route::get('/workshops','PublicAPIController@get_workshops_by_name');


//todo New Impersonate
//Use the same pass_reset methods
Route::get('/users/{unique_id}/impersonate', 'PublicAPIController@impersonate_user');


