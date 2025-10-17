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
 * Get all the assignments that fit a user
 *  return rows from the assignments table
 * 
 * test - http://bcomplydev.local:8000/api/public/users/B00168387/assignments
 * 
 *    previously existed
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
 * 
 *  test - http://bcomplydev.local:8000/api/public/modules/2/assignments
 * 
 * previously existed
 */
Route::get('/modules/{module}/assignments','PublicAPIController@get_module_assignments');


/**
 *  lookup all the assignments data
 *   parameters:
 *      current_version (optional) - boolean to only return the current version - default to true
 *      grace_period (optional) - used only if current version is true - number of days users have to complete a new version once it's created
 *      completed_after (optional) - only return records that were completed after a specific date (formatted as 2025-04-29)
 *      status (optional) - an array that specifies which statuses should be returned
 *      unique_id (optional) - a single user's ID (bnumber)
 *      test - http://bcomplydev.local:8000/api/public/modules/2/assignment_data
 */
Route::get('/modules/{module}/assignments_data','PublicAPIController@get_module_assignments_data');


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
 * 
 *  previously existed
 */
Route::post('/groups/{group_slug}/users/{unique_id}','PublicAPIController@add_group_membership');

/**
 * removes a user from a group
 * 
 *  test - http://bcomplydev.local:8000/api/public/groups/api_group1/users/B00168387
 *  previously existed
 */
Route::delete('/groups/{group_slug}/users/{unique_id}','PublicAPIController@delete_group_membership');

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
// previously existed
Route::get('/users/{unique_id}/impersonate', 'PublicAPIController@impersonate_user');


