<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\SimpleUser;
use App\GroupMembership;
use App\Group;
use App\Libraries\ApiHelper;
use App\Utilities;
use App\User;
use App\Mail\AssignmentNotification;
use App\ModuleAssignment;
use App\Module;
use App\ModuleVersion;
use App\Workshop;
use App\WorkshopOffering;
use App\WorkshopAttendance;
use Carbon\Carbon;
use DateInterval;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

class PublicAPIController extends Controller
{

    private $allowed_workshop_statuses = array("not_applicable", "uncompleted", "completed");
    private $allowed_workshop_attendances = array("registered", "attended", "completed");
    private $allowed_module_statuses = array("assigned", "attended", "in_progress", "passed", "failed", "completed", "incomplete");

    public function get_user(Request $request, $unique_id){
        $user = User::where('unique_id', $unique_id);
        if ($request->has('include_memberships') && $request->include_memberships == 'true'){
            $user = $user->with('pivot_groups');
        }
        $user = $user->first();
        if ($user){
            if ($request->has('include_memberships') && $request->include_memberships == 'true'){
                $user->group_memberships = $user->pivot_groups;
                unset($user->pivot_groups);
            }
            return $user;
        }else{
            return response("User Not Found!",404);
        }
    }

    public function create_user(Request $request){
        $user = new User($request->all());
        $user->save();
        return $user;
    }

    public function update_user(Request $request, $unique_id){
        $user = User::where('unique_id', $unique_id)->first();
        if ($user){
            $user->save($request->all());
            return $user;
        }else{
            return response("User not found!",404);
        }   
    }

    /**
     * adds a user to a group (or updates the user if they already exist in the group)
     */
    public function add_group_membership(Request $request, $group_slug, $unique_id){
        $helper = new ApiHelper();
        $group = $helper->get_group_for_slug($group_slug);
        if ($group != null) {
            $user = $helper->get_user_for_unique_id($unique_id);
            if ($user != null) {
                $group_membership = GroupMembership::where('user_id', $user->id)
                    ->where("group_id", $group->id);
                if ($group_membership != null){
                    $response = ["success"=>$user->first_name." ".$user->last_name." was already a member of the group '".$group->name."'"];
                } else {
                    $group_membership = new GroupMembership([
                        "user_id"=>$user->id,
                        "group_id"=>$group->id
                    ]);
                    $group_membership->save();
                }
                $response = ["success"=>$user->first_name." ".$user->last_name." was added to the group '".$group->name."'"];
            } else {
                $response = ["error"=>"User not found"];
            }
        } else {
            $response = ["error"=>"Group not found"];
        }
        return response($response, 200);
    }
    
    /**
     * removes a user from a group
     */
    public function delete_group_membership(Request $request, $group_slug, $unique_id){
        $helper = new ApiHelper();
        $group = $helper->get_group_for_slug($group_slug);
        if ($group != null) {
            $user = $helper->get_user_for_unique_id($unique_id);
            if ($user != null) {
                GroupMembership::where('user_id',$user->id)->where("group_id",$group->id)->delete();
                $response = ["success"=>$user->first_name." ".$user->last_name." was removed from the group '".$group->name."'"];
            } else {
                $response = ["error"=>"User not found"];
            }
        } else {
            $response = ["error"=>"Group not found"];
        }
        return response($response, 200);
    }

    public function get_all_group_users(Request $request, Group $group) {
        if ($group != null) {
            $users = SimpleUser::select("users.id", "unique_id", "first_name", "last_name", "email", 
                    "supervisor", "department_id", "department_name", "division_id", "division", 
                    "title", "role_type", "active", "users.created_at", "users.updated_at", 
                    "group_memberships.group_id")
                ->leftJoin('group_memberships', 'group_memberships.user_id', 'users.id')
                ->where('group_memberships.group_id', $group->id)
                ->get();
            $response = $users;
        } else {
            $response = ["error"=>"Group not found"];
        }
        return response($response, 200);
    }

    private function sync_users(&$remote_users, &$local_users) {
        $status=['warnings'=>[],'updated'=>[],'added'=>[],'ignored'=>[]];
        foreach($remote_users as $remote_user) {
            $remote_user['active'] = true; // 4-19-21 Added by TJC to force activation of all synced users
            if (isset($remote_user['unique_id'])) {
                $local_user = $local_users->firstWhere('unique_id', $remote_user['unique_id']);
                if (is_null($local_user)) {
                    // User Does Not Exist... Creating
                    $local_user = new SimpleUser($remote_user);
                    try {
                        $local_user->save();
                    } catch (\Exception $e) {
                        $status['warninings'][] = 'Error Creating User... Ignoring';
                    }
                    $local_users->push($local_user);
                    $status['added'][] = $local_user->unique_id;
                } else {
                    // User Exists... Perform diff to see if anything changed
                    // Get any user changes (Only for valid user attributes)
                    $diff = array_diff_assoc(array_intersect_key($remote_user,$local_user->getAttributes()),$local_user->getAttributes());
                    if (count($diff) > 0) {
                        // User Changed ... Update User
                        try {
                            $local_user->update($remote_user);
                        } catch (\Exception $e) {
                            $status['warninings'][] = 'Error Updating User... Ignoring';
                        }    
                        $status['updated'][] = $local_user->unique_id;
                    } else {
                        // User Unchanged... Ignoring
                        $status['ignored'][] = $local_user->unique_id;
                    }
                }
            } else {
                $status['warnings'][] = 'User Missing unique_id';
            }
        }
        return $status;
    }

    private function sync_groups(&$remote_groups, &$local_users, &$local_groups) {
        $status=['warnings'=>[],'updated'=>[]];
        // Add Groups / Memberships that Don't Exist
        foreach($remote_groups as $remote_group => $group_members) {
            $local_group = $local_groups->firstWhere('name', $remote_group);
            if (is_null($local_group)) {
                // Group Does Not Exist... Creating
                $local_group = new Group(['name'=>$remote_group]);
                try {
                    $local_group->save();
                } catch (\Exception $e) {
                    $status['warninings'][] = 'Error Creating Group... Ignoring';
                }
            }
            foreach($group_members as $unique_id) {
                $current_user = $local_users->firstWhere('unique_id',$unique_id);
                if (!is_null($current_user)) {
                    $group_affil = $current_user->group_memberships->firstWhere('group_id',$local_group->id);
                    if (is_null($group_affil)) {
                        $status['added'][$remote_group][] = $current_user->id;
                        $memberships= new GroupMembership([
                            'user_id' => $current_user->id,
                            'group_id' => $local_group->id,
                            'type' => 'external',
                        ]);
                        try {
                            $memberships->save();
                        } catch (\Exception $e) {
                            $status['warninings'][] = 'Error During Group Membership Insert Detected... Ignoring';
                        }
                    }
                }
            }
        }
        // Remove Memberships that Do Exist
        foreach($local_users as $local_user) {
            foreach($local_groups as $local_group) {
                if (!array_key_exists($local_group->name,$remote_groups)) {
                    // Group Shouldn't Exist... Deleting
                    // GroupMembership::where('group_id',$local_group->id)->delete();
                    // $local_group->delete();
                } else {
                    // This isn't great.  It will delete the membership even if it doesn't exist.
                    if (!in_array($local_user->unique_id,$remote_groups[$local_group->name])) {
                        GroupMembership::where('group_id',$local_group->id)
                            ->where('user_id',$local_user->id)
                            ->where('type','external')
                            ->delete();
                    }
                }
            }
        }
        return $status;
    }

    public function sync(Request $request) {
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
        try {
            $response = [];
            $local_users = SimpleUser::with('group_memberships')->get();  
            if ($request->has('users')) {
                $remote_users = collect($request->users);
                $response['users'] = $this->sync_users($remote_users, $local_users);
            }
            if ($request->has('groups')) {
                $remote_groups = $request->groups;
                $local_groups = Group::with('group_memberships')->get();
                $response['groups'] = $this->sync_groups($remote_groups, $local_users, $local_groups);
            }
            return $response;
        } catch (\Exception $e) {
            return ['exception'=>$e->getMessage(),'line'=>$e->getLine()];
        }
    }

    public function get_user_assignments(Request $request, $unique_id) {
        $user = SimpleUser::where('unique_id', $unique_id)->first();
        if (is_null($user)) {
            return response(['error'=>'The specified user does not exist'], 404)->header('Content-Type', 'application/json');
        }
        return ModuleAssignment::where('user_id',$user->id)
            ->select('id as module_assignment_id','module_id','module_version_id','user_id', DB::raw("'$unique_id' as b_number"),'date_assigned','date_completed','date_due','date_started','status')
            ->with(['version'=>function($query){
                $query->select('id','name');
            }])->with(['module'=>function($query){
                $query->select('id','name');
            }])->get();
    }

    /**
     *  lookup all the module assignments(not necessarily with status assigned - status can be anything)
     *   parameters:
     *      assigned_after (optional) - only return records that were assigned after a specific date (formatted as 2025-04-29)
     *      updated_after (optional) - only return records that were updated after a specific date (formatted as 2025-04-29)
     *      updated_before (optional) - only return records that were updated before a specific date (formatted as 2025-04-29)
     *      latest_version (optional) - if true, then only the latest version
     *    -- all of the above are inclusive (>= or <=) so the names are slightly misleading
     * 
     */
    public function get_module_assignments(Request $request, Module $module){
        $query = ModuleAssignment::select('module_assignments.id AS assignment_id','module_id','module_assignments.module_version_id','assigned_user.unique_id AS bnumber',
                                            'date_assigned','date_completed','date_due','date_started','status', 'assigned_by_user.unique_id AS assigned_by', 
                                            'module_assignments.updated_at', 'modules.name AS module_name');
        $query = $query->leftJoin('users AS assigned_user', 'assigned_user.id', 'module_assignments.user_id')
                       ->leftJoin('users AS assigned_by_user', 'assigned_by_user.id', 'module_assignments.assigned_by_user_id')
                       ->leftJoin('modules', 'module_assignments.module_id', 'modules.id')
                       ->where ('module_assignments.module_id', $module->id);

        $helper = new ApiHelper();
        if ($request->has('updated_after')) {
            $query = $query->where('module_assignments.updated_at', '>=', $request['updated_after']);
            
        }
        if ($request->has('updated_before')) {
            $query = $query->where('module_assignments.updated_at', '<=', $request['updated_before']);
        }
        if ($request->has('assigned_after')) {
            $query = $query->where('module_assignments.date_assigned', '>=', $request['assigned_after']);
        }
        if($request->has('latest_version') && $request->latest_version=='true'){
            $query->where('module_assignments.module_version_id',$module->module_version_id);
        }

        return $query->get();
    }
    /**
     *  lookup all the assignments that have been completed
     *   parameters:
     *      version (optional) - only return completions of a specific version
     *      latest_version (optional) - boolean to only return the latest version
     *      completed_after (optional) - only return records that were completed after a specific date (formatted as 2025-04-29)
     * 
     */
    public function get_module_assignments_completed(Request $request, Module $module){
        $query = ModuleAssignment::select('module_assignments.id AS assignment_id','module_id','module_assignments.module_version_id','assigned_user.unique_id AS bnumber',
                                            'date_assigned','date_completed','date_due','date_started','status', 'assigned_by_user.unique_id AS assigned_by', 
                                            'module_assignments.updated_at', 'modules.name AS module_name');
        $query = $query->leftJoin('users AS assigned_user', 'assigned_user.id', 'module_assignments.user_id')
                       ->leftJoin('users AS assigned_by_user', 'assigned_by_user.id', 'module_assignments.assigned_by_user_id')
                       ->leftJoin('modules', 'module_assignments.module_id', 'modules.id')
                       ->where ('module_assignments.module_id', $module->id);

        $helper = new ApiHelper();
        if ($request->has('completed_after')) {
            $query = $query->where('module_assignments.date_completed', '>=', $request['completed_after']);
        } else {
            $query = $query->whereNotNull('module_assignments.date_completed');
        }
        if ($request->has('version')) {
            $version = $request['version']; 
            $query = $query->where('module_assignments.module_version_id', $version);
        }
        if($request->has('latest_version') && $request->latest_version=='true'){
            $query->where('module_assignments.module_version_id', $module->module_version_id);
        }

        return $query->get();
    }

    /**
     * Get all assignments for all users
     *  parameters: 
     *      assigned_after (optional) - only return records that were assigned after a specific date (formatted as 2025-04-29)
     *      updated_after (optional) - only return records that were updated after a specific date (formatted as 2025-04-29)
     *      updated_before (optional) - only return records that were updated before a specific date (formatted as 2025-04-29)
     *      latest_version (optional) - if true, then only the latest version
     *    -- all of the above are inclusive (>= or <=) so the names are slightly misleading
     * 
     */
    public function get_all_assignments(Request $request){
        $completed_date_condition_text = null;
        $assigned_date_condition_text = null;
        $query = ModuleAssignment::select('module_assignments.id AS assignment_id','module_id','module_assignments.module_version_id','assigned_user.unique_id AS bnumber',
                                            'date_assigned','date_completed','date_due','date_started','status', 'assigned_by_user.unique_id AS assigned_by', 
                                            'module_assignments.updated_at', 'modules.name AS module_name');
        $query = $query->leftJoin('users AS assigned_user', 'assigned_user.id', 'module_assignments.user_id')
                       ->leftJoin('users AS assigned_by_user', 'assigned_by_user.id', 'module_assignments.assigned_by_user_id')
                       ->leftJoin('modules', 'module_assignments.module_id', 'modules.id');

        $helper = new ApiHelper();
        if ($request->has('updated_after')) {
            $query = $query->where("module_assignments.updated_at", ">=", $request['updated_after']);
        }
        if ($request->has('updated_before')) {
            $query = $query->where("module_assignments.updated_at", "<=", $request['updated_before']);
        }
        if ($request->has('assigned_after')) {
            $query = $query->where("module_assignments.date_assigned", ">=", $request['assigned_after']);
        }
        if($request->has('latest_version') && $request->latest_version=='true'){
            $query = $query->where('modules.module_version_id', 'module_assignments.module_version_id');
        }
        // return $query->toSql();
        return $query->get();
    }

    /**
     *  lookup all completed assignments
     *   parameters:
     *      version(optional) - return only completions of a specific version
     *      latest_version (optional) - boolean to only return the latest version
     *      completed_after (optional) - only return records that were completed after a specific date (formatted as 2025-04-29)
     * 
     *  test - http://bcomplydev.local:8000/api/public/assignments_completed
     */
    public function get_all_assignments_completed(Request $request){
        $completed_date_condition_text = null;
        $assigned_date_condition_text = null;
        $query = ModuleAssignment::select('module_assignments.id AS assignment_id','module_id','module_assignments.module_version_id','assigned_user.unique_id AS bnumber',
                                            'date_assigned','date_completed','date_due','date_started','status', 'assigned_by_user.unique_id AS assigned_by', 
                                            'module_assignments.updated_at', 'modules.name AS module_name');
        $query = $query->leftJoin('users AS assigned_user', 'assigned_user.id', 'module_assignments.user_id')
                       ->leftJoin('users AS assigned_by_user', 'assigned_by_user.id', 'module_assignments.assigned_by_user_id')
                       ->leftJoin('modules', 'module_assignments.module_id', 'modules.id');

        $helper = new ApiHelper();
        if ($request->has('completed_after')) {
            $query = $query->where('module_assignments.date_completed', '>=', $request['completed_after']);
        } else {
            $query = $query->whereNotNull("module_assignments.date_completed");
        }
        if ($request->has('version')) {
            $version = $request['version']; 
            $query = $query->where('module_assignments.module_version_id', $version);
        }
        if ($request->has('latest_version')) {
            if ($request['latest_version'] == true) {
                $query = $query->where('modules.module_version_id', 'module_assignments.module_version_id');
            }
        }
        return $query->get();
    }

/**
 * get the status of an assignment for a user
 *  parameters:
 *      assigned_after (optional) - when returning the assigned boolean the specific date (formatted as 2025-04-29)
 *      completed_after (optional) - when returning the completed boolean the specific date (formatted as 2025-04-29) is considered
 *  returns:
 *      all rows from module_assignments for student and assignment
 */

     public function get_user_module_status(Request $request, Module $module, String $unique_id) {
        $after = null;
        $curr_user = User::where('unique_id', $unique_id)->first();
        $assigned_date_condition_text = null;
        $completed_date_condition_text = null;
        if ($curr_user != null) {
            $helper = new ApiHelper();
            if ($request->has('completed_after')) {
                $completed_after = $helper->string_to_date($request['completed_after']); 
                $completed_date_condition_text = "module_assignments.date_completed < '$completed_after' ";
                $add_assigned_after = true;
            }
            if ($request->has('assigned_after')) {
                $assigned_after = $helper->string_to_date($request['assigned_after']);
                $assigned_date_condition_text = "module_assignments.date_assigned < '$assigned_after' ";

            }
            if ($request->has('latest_version')) {
                $latest_version = $request['latest_version'] == "true";
            } else {
                $latest_version = false;
            }
            $select_fields = ['modules.name AS module_name', 'modules.description AS module_description', 'module_assignments.status AS assignment_status', 
                    'module_assignments.date_due', 'module_assignments.date_assigned', 'module_assignments.date_completed', 'users.first_name', 
                    'users.last_name', DB::raw("'$unique_id' as b_number"), 'module_versions.id AS module_version_id', 'module_versions.name AS version_name', 
                    'module_versions.created_at AS version_date'];
            if ($completed_date_condition_text != null) {
                $completed_where = DB::raw("case when $completed_date_condition_text THEN 0 ELSE 1 END");
            }
            if ($assigned_date_condition_text != null) {
                $assigned_where = DB::raw("case when $assigned_date_condition_text THEN 0 ELSE 1 END");
            }
            $query = $module->select($select_fields);
            try {
                $query = $query->leftJoin('module_assignments', 'module_assignments.module_id', 'modules.id')
                    ->leftJoin('users', 'module_assignments.user_id', 'users.id')
                    ->leftJoin('module_versions', 'modules.module_version_id', 'module_versions.id');
                $query = $query->where('users.unique_id', $unique_id)
                    ->where('module_assignments.module_id', $module->id);
                if ($completed_date_condition_text != null) {
                    $query = $query->where($completed_where, 1);
                }
                if ($assigned_date_condition_text != null) {
                    $query = $query->where($assigned_where, 1);
                }
                if ($latest_version) {
                    $query = $query->where('module_assignments.module_version_id', 'modules.id');
                }
                $query = $query->orderBy('modules.module_version_id', 'desc')
                    ->get();
                $response = $query;
            } catch (Exception $e) {
                $response = ["error"=>$e];
            }
        } else {
            $response = ["error"=>"The user with the b number: ".$unique_id." was not found in BComply"];
        }
        return $response;
    }

    /**
     *  gets all module assignments for the users of a group where the module id = module_id
     *  parameters:
     *      assigned_after (optional) - when returning the assigned boolean the specific date (formatted as 2025-04-29)
     *      completed_after (optional) - when returning the completed boolean the specific date (formatted as 2025-04-29) is considered
     *      version (optional) - only return records that have the specified version
     *  returns: 
     *      all rows for module_assigments that fit the criteria plus the module_name, group_name, user_name, and boolean for completed
     */
    public function get_group_module_status(Request $request, $group_slug, Module $module) {
        $helper = new ApiHelper();
        if ($request->has('completed_after')) {
            $completed_after = $helper->string_to_date($request['completed_after']); 
            $completed_date_condition_text = "module_assignments.date_completed < '$completed_after' OR module_assignments.date_completed IS NULL;";
        } else {
            $completed_date_condition_text = "module_assignments.date_completed IS NULL";
        }
        if ($request->has('assigned_after')) {
                $assigned_after = $helper->string_to_date($request['assigned_after']);
                $assigned_date_condition_text = "module_assignments.date_assigned < '$assigned_after' OR module_assignments.date_assigned IS NULL";
            } else {
                $assigned_date_condition_text = "module_assignments.date_assigned IS NULL";
            }
        $select_fields = ["modules.name AS module_name", "modules.id AS module_id", "groups.name AS group_name", "groups.id AS group_id",
                    "users.unique_id AS bnumber", "module_assignments.id AS module_id", "module_assignments.module_version_id",
                    "module_assignments.date_assigned", "module_assignments.date_due", "module_assignments.date_started",
                    "module_assignments.date_completed", "module_assignments.status", "module_assignments.score",
                    "module_assignments.current_state",
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS user_name"),
                    DB::raw("(case when $completed_date_condition_text THEN 0 ELSE 1 END) AS completed_within_range"),
                    DB::raw("(case when $assigned_date_condition_text THEN 0 ELSE 1 END) AS assigned_within_range")];
        $users = ModuleAssignment::select($select_fields)
            ->leftJoin("users", 'users.id', 'module_assignments.user_id')
            ->leftJoin("group_memberships", 'group_memberships.user_id', 'users.id')
            ->leftJoin("modules", "modules.id", "module_assignments.module_id")
            ->leftJoin("groups", "groups.id", "group_memberships.group_id")
            ->where('groups.slug', $group_slug)
            ->where('modules.id', $module->id);
        if ($request->has('assigned_after')) {
            $assignment_date = $helper->string_to_date($request['assigned_after']); 
            $users = $users->where('module_assignments.date_assigned', '>=', $assignment_date);
        }
        if ($request->has('version')) {
            $users = $users->where('version', $request['version']);
        }
        $users = $users->get();
        return $users;
    }

    

    

    /**
     *  lookup module versions
     *  parameters:
     *      module_name (required) - name to look up, can include %s for LIKE comparisons
     *                               if omitted return all modules
     *      
     *  returns:
     *      rows from modules table
     */

    public function get_modules_by_name(Request $request){
        try {
            if ($request->has('module_name')) {
                $modules = Module::select(
                    "modules.id as module_id", "modules.name as module_name", "modules.description", "users.unique_id as owner_b_number", 
                    "modules.message_configuration", "modules.assignment_configuration", "modules.public", "modules.past_due", 
                    "modules.reminders", "modules.past_due_reminders", "modules.module_version_id", "modules.created_at", "modules.updated_at", 
                    "modules.deleted_at"
                )
                ->leftJoin('users', 'users.id', 'modules.owner_user_id')
                ->where('name', 'LIKE', $request['module_name'])->get();
                $response = $modules;
            } else {
                $response = ['error'=>'Please provide the parameter module_name'];
            }
        } catch (Exception $e) {
            $response = ["error"=>$e];
        }
        return $response;
    }
    
    /**
     *  lookup groups  
     *  parameters:
     *      group_name (required) - name to look up, can include %s for LIKE comparisons
     *                               if omitted return all groups
     *  returns:
     *      rows from groups table
     */
    public function get_groups_by_name(Request $request){
        try {
            if ($request->has('group_name')) {
                $modules = Group::where('name', 'LIKE', $request['group_name'])->get();
                $response = $modules;
            } else {
                $response = ['error'=>'Please provide the parameter group_name'];
            }
        } catch (Exception $e) {
            $response = ["error"=>$e];
        }
        return $response;
    }

    /**
     * can we get the current user from the request?
     */
    public function get_current_user() {
        return 0;
    }



    /**
     * Create a group with the group slug
     *  parameters:
     *      group_name (optional): if not given, the group name is the same as the slug
     *  returns:
     *      the group row or a message why the group could not be created
     */
    public function create_group(Request $request, $group_slug) {
        $helper = new ApiHelper();
        $duplicate = $helper->get_group_for_slug($group_slug);
        if (empty($duplicate)) {
            $group_name = $group_slug;
            if ($request->has('group_name')) {
                $group_name = $request['group_name'];
            } else {
                $group_name = $group_slug;
            }
            $date_due = $request['date_due'];
            $duplicate = GROUP::where('name', $group_name)->first();
            if (empty($duplicate)) {
                $group = new Group([
                    'name' => $group_name,
                    'slug' => $group_slug]
                );
                $group->save();
                $response = $group;
            } else {
                $response = ["error"=>"group named '".$group_name."' already exists"];
            }
        } else {
            $response = ["error"=>"group with slug '".$group_slug."' already exists"];
        }
        return $response;
    }

    /**
     * Assigns a module to a user
     *  parameters:
     *      due_date (optional) - (formated as 2025-04-29) - null if omitted
     */
    public function assign_module_to_user(Request $request, Module $module, $unique_id) {
        $helper = new ApiHelper();
        $user = $helper->get_user_for_unique_id($unique_id);
        if ($user != null) {
            if ($module != null) {
                if ($request->has('due_date')) {
                    $helper= $helper->string_to_date($request['due_date']);
                } else {
                    $due_date = null;
                }
                //does the record already exist?
                $assignment_record = ModuleAssignment::where('module_id', $module->id)
                    ->where('module_version_id', $module->module_version_id)
                    ->where('user_id', $user->id)
                    ->where(function($query) {
                        $query->where('status', 'assigned')
                        ->orWhere('status', 'attended')
                        ->orWhere('status', 'in_progress')
                        ->orWhere('status', 'passed')
                        ->orWhere('status', 'completed');
                    })
                    ->orderBy('date_assigned', 'desc')
                    ->first();
                if ($assignment_record != null) {
                    if (($assignment_record->status == 'assigned' || $assignment_record->status == 'in_progress') && $due_date != null) {
                        $assignment_record->date_due = $due_date;
                        $assignment_record->save();
                    }
                    $response = ['warning'=>"Module ".$module->id." has already been assigned to ".$user['first_name']." ".$user['last_name'].". Their status is '".$assignment_record['status'].".'"];
                } else {
                    $new_record = new ModuleAssignment([
                        'user_id' => $user->id,
                        'module_version_id' => $module->module_version_id,
                        'module_id' => $module->id,
                        'date_assigned' => now(),
                        'due_date' => $due_date,
                        'status' => 'assigned'
                    ]);
                    $new_record->save();
                    $response = $new_record;
                }
            } else {
                $response = ['error'=>'The specified module does not exist'];
            }
        } else {
            $response = ['error'=>'The specified user does not exist'];
        }
        return $response;
    }

    public function impersonate_user(String $unique_id){
        $encryption_obj = [
            'unique_id'=>$unique_id,
            'timestamp'=>now()->timestamp
        ];
        return url('/auth/token/'.Crypt::encrypt(json_encode($encryption_obj)));
    }

    public function create_calendar(Request $request){
        $events = array();
        $workshops = Workshop::where('public',true)->with('owner')->get();
        foreach($workshops as $index => $workshop){
            $workshop_offerings =WorkshopOffering::where('workshop_id',$workshop->id)->with('instructor')->get();
            foreach($workshop_offerings as $index => $workshop_offering){
                $instructor_name =$workshop_offering->instructor->first_name . ' '.  $workshop_offering->instructor->last_name;
                $description = $workshop->description ."\n".'To sign up, please click the following link: '.url('/workshops/'. $workshop_offering->workshop->id .'/offerings/'.$workshop_offering->id);
                $organizer = new Organizer(
                    new EmailAddress( $workshop_offering->instructor->email),
                    $instructor_name,
                );
                $location = new Location($workshop_offering->locations);
                $minutes_to_add =  $workshop_offering->workshop->duration;
                $occurence = null;
            
                if($workshop_offering->is_multi_day){
                    $counter = 1;
                    foreach($workshop_offering->multi_days as $day){
                        $workshop_end_time = date('Y-m-d H:i:s', strtotime( $day. '+'.$minutes_to_add.' minutes'));
                        $occurence =new TimeSpan(
                            new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $day), true),
                            new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $workshop_end_time), true)
                        );
                                        
                        $event = (new Event())
                        ->setSummary($workshop->name. ' Day '. $counter . ' ('. $workshop_offering->type. ')')
                        ->setDescription($description)
                        ->setOrganizer($organizer)
                        ->setLocation($location)
                        ->setOccurrence($occurence);
                    
                        array_push($events,$event);
                        $counter = $counter+1;
                    }
                }
                else{
                    $workshop_end_time = date('Y-m-d H:i:s', strtotime( $workshop_offering->workshop_date. '+'.$minutes_to_add.' minutes'));
                    $occurence =new TimeSpan(
                        new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $workshop_offering->workshop_date), true),
                        new DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $workshop_end_time), true)
                    );
                                    
                    $event = (new Event())
                    ->setSummary($workshop->name . ' ('. $workshop_offering->type. ')')
                    ->setDescription($description)
                    ->setOrganizer($organizer)
                    ->setLocation($location)
                    ->setOccurrence($occurence);
                
                    array_push($events,$event);
                }
            } 
        }
        $calendar = new Calendar($events);
        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);
        return response($calendarComponent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="cal.ics"');
    }    

    /**
     *  lookup workshop  
     *  parameters:
     *      workshop_name (required) - name to look up, can include %s for LIKE comparisons
     *                               if omitted return all groups
     *  returns:
     *      rows from workshops table
     */
    public function get_workshops_by_name(Request $request) {
        try{
            if ($request->has('workshop_name')) {
                $workshops = Workshop::where('name', 'LIKE', $request['workshop_name'])->get();
                $response = json_encode($workshops);
            } else {
                $response = ['error'=>'Please provide the parameter workshop_name'];
            }
            
        } catch (Exception $e) {
            $response = ["error"=>$e];
        }
        return $response;
    }
}