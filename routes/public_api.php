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
 */
Route::get('/users/{unique_id}/assignments','PublicAPIController@get_user_assignments');

// Modules
/**
 *  lookup module versions
 *  parameters:
 *      module_name (required) - name to look up, can include %s for LIKE comparisons
 *                               if omitted return all modules
 *      
 *  returns:
 *      rows from modules table
 */
Route::get('/modules','PublicAPIController@get_modules_by_name');

/**
 *  lookup all the users that have a module assigned
 */
Route::get('/modules/{module}/assignments','PublicAPIController@get_module_assignments');

/**
 * get the status of an assignment for a user
 *  parameters:
 *      assigned_after (optional) - only return records that were assigned after a specific date (formatted as 2025-04-29)
 *      completed_after (optional) - only return records that were completed after a specific date (formatted as 2025-04-29)
 *      status (optional) - only return records that have the status specified
 *  returns:
 *      all rows from module_assignments for student and assignment
 */
Route::get('/modules/{module}/users/{unique_id}','PublicAPIController@get_user_module_status');

/**
 * set that status of a module for a user
 *  parameter:
 *      status (required) - "assigned", "attended", "in_progress", "passed", "failed", "completed", "incomplete"
 */

Route::put('/modules/{module}/users/{unique_id}', 'PublicAPIController@update_user_module_status');

/**
 * Assigns a module to a user
 *  parameters:
 *      due_date (optional) - (formated as 2025-04-29) - null if omitted
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
 */
Route::get('/groups', 'PublicAPIController@get_groups_by_name');

/**
 * adds a user to a group (or updates the user if they already exist in the group)
 */
Route::post('/groups/{slug}/users/{unique_id}','PublicAPIController@add_group_membership');

/**
 * removes a user from a group
 */
Route::delete('/groups/{slug}/users/{unique_id}','PublicAPIController@delete_group_membership');

/**
 *  gets all module assignments for the users of a group where the module id = module
 *  parameters:
 *      assigned_after (optional) - only return records that were assigned after a specifice date (formated as 2025-04-29)
 *      completed_after (optional) - returns the completed status after the date for all of the users of the group 
 *      version (optional) 
 *  returns: 
 *      all rows for module_assigments that fit the criteria plus the module_name, group_name, user_name, and boolean for completed
 */
Route::get('/groups/{slug}/modules/{module}','PublicAPIController@get_group_module_status');

/**
 * Assign module for everyone in a group
 *  parameters:
 *      not_completed_after (optional): don't assign it to anyone that has complete the module after date (fomatted like after=2025-05-01)
 *      version (optional): assign a specific version, if not passed the latest version is used
 *      due_date (optional): enter a due date for the assignment specified like 2025-05-01
 *  returns:
 *      the module that was assigned or an error message
 */

Route::post('/groups/{slug}/modules/{module}','BulkAssignmentController@assign_module_to_group_members'); 

/**
 * Gets all users that are in a group
 *  parameters:
 *      none
 *  returns:
 *      the users in the group
 */
Route::get('/groups/{group}','PublicAPIController@get_all_group_users'); 

/**
 * Create a group with the group slug
 *  parameters:
 *      group_name (optional): if not given, the group name is the same as the slug
 *  returns:
 *      the group row or a message why the group could not be created
 */
Route::post('/groups/{slug}','PublicAPIController@create_group'); 

//Bulk Assignment
/**
 * Create a bulk assignment
 *  parameters:
 *      assignment: json of the assignment
 *      description (optional): assignment description, defaults to ''
 *  returns:
 *      the assignment row that was created or an error message
 */

 Route::post('/bulkAssignments/{assignmentName}', 'BulkAssignments@create_bulk_assignments');

//Workshops
/**
 *  lookup workshop  
 *  parameters:
 *      workshop_name (optional) - name to look up, can include %s for LIKE comparisons
 *                               if omitted return all groups
 *  returns:
 *      rows from workshops table
 */

// Route::get('/workshops','PublicAPIController@get_workshops_by_name');

/**
 *  gets all workshop attendance for the users of a group where the workshop id = workshop_id
 *  parameters:
 *      after (optional) - only return records where the workshop date is after a specifice date (formated as 2025-04-29)
 *  returns: 
 *      workshop, workshop_attendance, workshop_offering, group, and user data
 */
Route::get('/groups/{slug}/workshops/{workshop_id}','PublicAPIController@get_group_users_status_for_workshops');

/**
 * Add workshop_attendance record for everyone in a group
 *  parameters:
 *      status (optional) - "not_applicable", "uncompleted", "completed" defaults to uncomplete
 *      attendance (optional) - "registered", "attended", "completed" defaults to registered
 */

Route::post('/groups/{slug}/workshops/{workshop_id}', 'BulkAssignment@add_workshop_attendence_to_group_members');

/**
 * Gets the workshop_attendance record for a user
 *  parameters:
 *      after(optional) - only return attendance records after the specified date (formated as 2025-04-29)
 */
Route::get('/workshops/{workshop_id}/users/{unique_id}','PublicAPIController@get_workshop_attendance_for_user');

/**
 * set the status and attendance for a workshop and user
 *  parameters:
 *      status (required) - "not_applicable", "uncompleted", "completed"
 *      attendance (required) - "registered", "attended", "completed"
 */
Route::put('/workshops/{workshop_id}/users/{unique_id}', 'PublicAPIController@update_user_workshop_status_and attendance');


/**
 * add the user to the workshop attendance table
 *  parameters:
 *      status (optional) - "not_applicable", "uncompleted", "completed" defaults to uncomplete
 *      attendance (optional) - "registered", "attended", "completed" defaults to registered
 */
Route::post('/workshops/{workshop_id}/users/{unique_id}', 'PublicAPIController@add_workshop_attendance_for_user');


//todo New Impersonate
//Use the same pass_reset methods
Route::get('/users/{unique_id}/impersonate', 'PublicAPIController@impersonate_user');