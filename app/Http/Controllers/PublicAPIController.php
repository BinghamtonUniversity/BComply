<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\SimpleUser;
use App\GroupMembership;
use App\Group;
use App\Utilities;
use App\User;
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
            $user->update($request->all());
            return $user;
        }else{
            return response("User not found!",404);
        }   
    }

    /**
     * adds a user to a group (or updates the user if they already exist in the group)
     */
    public function add_group_membership(Request $request, $group_slug, $unique_id){
        $group = $this->get_group_for_slug($group_slug);
        if ($group != null) {
            $user = $this->get_user_for_unique_id($unique_id);
            if ($user != null) {
                $group_membership = GroupMembership::updateOrCreate([
                    'user_id'=>$user->id,
                    "group_id"=>$group->id,
                    ],['type' => 'external']);
                
                
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
        $group = $this->get_group_for_slug($group_slug);
        if ($group != null) {
            $user = $this->get_user_for_unique_id($unique_id);
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

    public function get_module_assignments(Request $request, Module $module){
        $query = ModuleAssignment::where('module_id',$module->id)
            ->select('id','module_id','module_version_id','user_id','date_assigned','date_completed','date_due','date_started','status')
            ->with(['user'=>function($query){
                $query->select('id','unique_id','email','first_name','last_name');
            }])->with(['version'=>function($query){
                $query->select('id','name');
            }]);
        if($request->has('current_version') && $request->current_version=='true'){
            $query->where('module_version_id',$module->module_version_id);
        }
        if($request->has('users') && gettype($request->users)==='array'){
            $query->whereHas('user', function ($query) use($request) {
                $query->whereIn('unique_id', $request->users);
            });
        }
        return $query->paginate(100);
    }

    /**
     * get the status of an assignment for a user
     *  parameters:
     *      after (optional) - only return records that were assigned after a specific date
     *      status (optional) - only return records that have the status specified
     *  returns:
     *      all rows from module_assignments for student and assignment
     */

    public function get_user_module_status(Request $request, Module $module, String $unique_id) {
        $after = null;
        $curr_user = User::where('unique_id', $unique_id)->first();
        if ($curr_user != null) {
            $query = $module->select(
                    'modules.name AS module_name', 'modules.description AS module_description', 'module_assignments.status AS assignment_status', 
                    'module_assignments.date_due', 'module_assignments.date_assigned', 'module_assignments.date_completed', 'users.first_name', 
                    'users.last_name', DB::raw("'$unique_id' as b_number"), 'module_versions.id', 'module_versions.name AS version_name', 
                    'module_versions.created_at AS version_date')
                ->leftJoin('module_assignments', 'module_assignments.module_id', 'modules.id')
                ->leftJoin('users', 'module_assignments.user_id', 'users.id')
                ->leftJoin('module_versions', 'modules.module_version_id', 'module_versions.id');
            try {
                if ($request->has('after')) {
                    $after = $this->string_to_date($request['after']);
                    $query = $query->where('module_assignments.date_assigned', '>=', $after);
                }
                $query = $query->where('users.unique_id', $unique_id)
                    ->orderBy('modules.module_version_id', 'desc')
                    ->get();
                if ($query == null) {
                    if ($after != null) {
                        $error = "Module '" . $module->name."' has not been assigned or completed by the user " . $curr_user->first_name." ".$curr_user->last_name." within the time period";
                    } else {
                        $error = "Module '" . $module->name."' has not been assigned to the user " . $unique_id;
                    }
                    $response = ["error"=>$error];
                } else {
                    $version_query = ModuleVersion::where('module_id', $module->id)
                        ->orderBy('id', 'desc')->first();
                    // $query['latest_version'] = $version_query['id'] == $query['module_version_id'];
                    if ($after != null) {
                        if ($query['assignment_status'] != 'complete') {
                            $query['assignment_status'] = 'incomplete';
                        }
                    }
                    $response = $query;
                }
            } catch (Exception $e) {
                $response = ["error"=>$e];
            }
        } else {
            $response = ["error"=>"The user with the b number: ".$unique_id." was not found in BComply"];
        }
        return $response;
    }

    /**
     * //TODO: look at this
     * set that status of a module for a user
     *  parameter:
     *      status (required) - "assigned", "attended", "in_progress", "passed", "failed", "completed", "incomplete"
     *      version (optional) - will use latest version if omitted
     */
    public function update_user_module_status(Request $request, Module $module, $unique_id) {
        try {
            if ($request->has('status')){
                $status = $request['status'];
                if (in_array($status, $this->allowed_module_statuses)) {
                    $user = $this->get_user_for_unique_id($unique_id);
                    if ($user != null) {
                        $now = now()->timestamp;
                        ModuleAssignment::update([
                            'user_id' => $user->id,
                            'module_version_id' => $module->module_version_id,
                            'status' =>$status,
                            'updated_at' => $now] ,['type' => 'external'])
                            ->where('module_id', $module->id)
                            ->where('user_id', $user->id);
                        $query = ModuleAssignment::where('module_id', $module->id)
                                ->where('user_id', $user->id)->get();
                        $rosponse = json_encode($query);
                    } else {
                        $response = ["error"=>"The user you specified was not found"];    
                    }
                } else {
                    $response = ["error"=>"That status is not allowed"];
                }
            } else {
                $response = ["error"=>"You must include a status to do this"];
            }
        } catch (Exception $e) {
            $response = ["error"=>$e];
        }
        return $response;
    }

    /**
     *  gets all module assignments for the users of a group where the module id = module_id
     *  parameters:
     *      after (optional) - only return records that were assigned after a specifice date 
     *  returns: 
     *      all rows for module_assigments that fit the criteria plus the module_name, group_name, user_name, and boolean for completed
     */
    public function get_group_module_status(Request $request, $group_slug, Module $module) {
        if ($request->has('after')) {
            $after = $this->string_to_date($request['after']); 
            $date_condition_text = "< ".$after;
        } else {
            $date_condition_text = "IS NULL";
        }
        $users = ModuleAssignment::select(
                    "modules.name AS module_name", "modules.id AS module_id", "groups.name AS group_name", "groups.id AS group_id",
                    "users.unique_id AS bnumber", "module_assignments.id AS module_id", "module_assignments.module_version_id",
                    "module_assignments.date_assigned", "module_assignments.date_due", "module_assignments.date_started",
                    "module_assignments.date_completed", "module_assignments.status", "module_assignments.score",
                    "module_assignments.current_state",
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS user_name"),
                    DB::raw("(case when module_assignments.date_completed ".$date_condition_text." THEN 'False' ELSE 'True' END) AS completed"),)
            ->leftJoin("users", 'users.id', 'module_assignments.user_id')
            ->leftJoin("group_memberships", 'group_memberships.user_id', 'users.id')
            ->leftJoin("modules", "modules.id", "module_assignments.module_id")
            ->leftJoin("groups", "groups.id", "group_memberships.group_id")
            ->where('groups.slug', $group_slug)
            ->where('modules.id', $module->id);
        if ($request->has('version')) {
            $users = $users->where('version', $request['version']);
        }
        $users = $users->get();
        return $users;
    }

    /**
     * lookup a group based on the slug
     * 
     * returns null if not found
     */
    public function get_group_for_slug($group_slug) {
        $group = null;
        if (!empty($group_slug)){
            $group = Group::where('slug', $group_slug)->first();
            if (empty($group)) {
                $group = null;
            }
        }
        return $group;
    }

    /**
     * lookup user by b_number
     * 
     * returns null if not found
     */
    public function get_user_for_unique_id($unique_id) {
        $user = null;
        if (!empty($unique_id)) {
            $user = User::where('unique_id', $unique_id)->first();
            if (empty($user)){
                $user = null;
            }
        }
        return $user;
    }

    /**
     *  lookup module versions
     *  parameters:
     *      module_name (optional) - name to look up, can include %s for LIKE comparisons
     *                               if omitted return all modules
     *      
     *  returns:
     *      rows from modules table
     */

    public function get_modules_by_name(Request $request){
        try {
            if ($request->has('module_name')) {
                $modules = MODULE::select(
                    "modules.id as module_id, modules.name as module_name", "modules.description", "users.unique_id as owner_b_number", 
                    "modules.message_configuration", "modules.assignment_configuration", "modules.public", "modules.past_due", 
                    "modules.reminders", "modules.past_due_reminders", "modules.module_version_id", "modules.created_at", "modules.updated_at", 
                    "modules.deleted_at"
                )
                ->leftJoin('users', 'users.id', 'modules.owner_user_id')
                ->where('name', 'LIKE', $request['module_name'])->get();
                $response = json_encode($modules);
            } else {
                $modules = MODULE::get();
                $response = json_encode($modules);
            }
        } catch (Exception $e) {
            $response = ["error"=>$e];
        }
        return $response;
    }
    
    /**
     *  lookup groups  
     *  parameters:
     *      group_name (optional) - name to look up, can include %s for LIKE comparisons
     *                               if omitted return all groups
     *  returns:
     *      rows from groups table
     */
    public function get_groups_by_name(Request $request){
        try {
            if ($request->has('group_name')) {
                $modules = GROUP::where('name', 'LIKE', $request['group_name'])->get();
            } else {
                $modules = GROUP::get();
            }
            $response = json_encode($modules);
        } catch (Exception $e) {
            $response = ["error"=>$e];
        }
        return $response;
    }

    // /**
    //  * if the version is specified in the request return it
    //  * else return the most current version
    //  */
    // private function get_version_from_request_or_most_recent($request) {
    //     if ($request->has('version')) { 
    //         $version = $request['version'];
    //     } else {
    //         $version = $this->get_latest_module_version();
    //     }
    //     return $version;
    // }

    // private function get_current_user(Request $request){

    // }

    /**
     * Create the module assignment and add it to the table or update it if it already exists
     */
    private function add_or_update_module_assignment($user, $version, $module_id, $due_date, $current_user){
        $user_id = $user->user_id;
        $now = now()->timestamp;
        if ($user->MODULE_ASSIGNMENT_ID == null) {
            $module_assignment = ModuleAssignment::create([
                'user_id' => $user_id,
                'module_version_id' => $version,
                'module_id' => $module_id,
                'due_date' => $due_date,
                'data_assigned' => $now,
                'assigned_by_user_id' => $current_user,
                'status' => 'assigned',
                'created_at' => $now], ['type' => 'external']

            );
        } else {
            $module_assignment = ModuleAssignment::update([
                'user_id' => $user_id,
                'module_version_id' => $version,
                'module_id' => $module_id,
                'due_date' => $due_date,
                'data_assigned' => $now,
                'updated_by_user_id' => $current_user,
                'status' => 'assigned',
                'updated_at' => $now] ,['type' => 'external']

            )->where('id', $user->MODULE_ASSIGNMENT_ID);
        } 
    }

    // /**
    //  * 
    //  */
    // private function get_latest_module_version() {
    //     $mod = ModuleVersion::orderBy('created_at', 'desc')
    //         ->first();
    //     return $mod->id;
    // }

    /**
     * Create a group with the group slug
     *  parameters:
     *      group_name (optional): if not given, the group name is the same as the slug
     *  returns:
     *      the group row or a message why the group could not be created
     */
    public function create_group(Request $request, $group_slug) {
        $duplicate = $this->get_group_for_slug($group_slug);
        if (empty($duplicate)) {
            $group_name = $group_slug;
            if ($request->has('group_name')) { 
                $date_due = $request['date_due'];
                $duplicate = GROUP::where('name', $group_name)->first();
            }
            if (empty($duplicate)) {
                $group = Group::create([
                    'name' => $group_name,
                    'slug' => $group_slug,
                    'created_at' => now()->timestamp],['type' => 'external']
                );
                $response = json_encode($group);
            } else {
                $response = ["error"=>"group named '".$group_name."' already exists"];
            }
        } else {
            $response = ["error"=>"group with slug '".$group_slug."' already exists"];
        }
        return $response;
    }

    /**
     * Takes a date formatted like YYYY-MM-DD and returns a 
     * date object
     * @param String string formatted like 2025-03-25
     * @throws ValueError
     */
    public function string_to_date($s_date) {
        $format = 'Y-m-d';
        return Carbon::createFromFormat($format, $s_date);
    }


    /**
     * Assigns a module to a user
     */
    public function assign_module_to_user(Request $request, Module $module, $unique_id) {
        $user = $this->get_user_for_unique_id($unique_id);
        if ($user != null) {
            $module = Module::where('id', $module->id)
                ->first();
            if ($module != null) {
                //does the record already exist?
                $old_record = ModuleAssignment::where('module_id', $module->id)
                    ->where('user_id', $user->id)
                    ->orderBy('date_assigned', 'desc')
                    ->first();
                if ($old_record != null) {
                    $response = ['warning'=>"Module ".$module->id." has already been assigned to ".$user['first_name']." ".$user['last_name'].". Their status is '".$old_record['status'].".'"];
                } else {
                    $this->sendAssignmentToUser($module, $user);
                    $response = ['success'=>"record added"];
                }
            } else {
                $response = ['error'=>'The specified module does not exist'];
            }
        } else {
            $response = ['error'=>'The specified user does not exist'];
        }
        return $response;
    }

    public function sendAssignmentToUser($module, $user) {
        $assignment = ModuleAssignment::where('module_id', $module->id);
        $templates = $module->templates;
        //$message = $templates->assignments;
        $message = $this->getAssignmentFromTemplate($templates);
        $notify = new AssignmentNotification($assignment, $user, $message);
    }

    public function getAssignmentFromTemplate($templates) {
        $json = json_decode($templates);
        $assignment = $json->assignment;
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


    /***
     * 
     * 
     *          Unused for now below here
     * 
     */

    /**
     * Create the workshop attendance and add it to the table or update it if it already exists
     */
    private function add_or_update_workshop_attendance($user, $workshop_id, $workshop_offering_id, $attendance, $status, $current_user) {
        $user_id = $user->user_id;
        $now = now()-timestamp;
        if ($user->workshop_attendances_id == null) {
            $workshop_attendance = WorkshopAttendance::create([
                'workshop_offering_id' => $workshop_offering_id,
                'workshop_id' => $workshop_id,
                'user_id' => $user_id,
                'attendance' => $attendance,
                'status' => $status,
                'created_at' => $now
            ], ['type' => 'external']
            );
        } else {
            $workshop_attendance = WorkshopAttendance::create([
                'workshop_offering_id' => $workshop_offering_id,
                'workshop_id' => $workshop_id,
                'user_id' => $user_id,
                'attendance' => $attendance,
                'status' => $status,
                'updated_at' => $now
            ], ['type' => 'external']
            )->where('id', $user->workshop_attendance_id);
        }
    }

    /**
     * add the user to the workshop attendance table
     *  parameters:
     *      status (optional) - "not_applicable", "uncompleted", "completed" defaults to uncomplete
     *      attendance (optional) - "registered", "attended", "completed" defaults to registered
     */
    public function add_workshop_attendance_for_user(Request $request, String $workshop_id, $unique_id) {
        $user = $this->get_user_for_unique_id($unique_id);
        if ($user != null) {
            $workshop = Workshop::where('id', $workshop_id)
                ->first();
            if ($workshop != null) {
                //does the record already exist?
                $old_record = WorkshopAttendance::where('workshop_id', $workshop_id)
                    ->where('user_id', $user->id)
                    ->first();
                if ($old_record != null) {
                    $response = ['warning'=>"Workshop ".$workshop_id." has already been assigned to ".$user['first_name']." ".$user['last_name'].". Their attendance is '".$old_record['attendance'].".'"];
                } else {
                    $response = ['success'=>"record added"];
                }
            } else {
                $response = ['error'=>'The specified workshop does not exist'];
            }
        } else {
            $response = ['error'=>'The specified user does not exist'];
        }
        return $response;
    }

    /**
     *  lookup workshop  
     *  parameters:
     *      workshop_name (optional) - name to look up, can include %s for LIKE comparisons
     *                               if omitted return all groups
     *  returns:
     *      rows from workshops table
     */
    public function get_workshops_by_name(Request $request) {
        try{
            if ($request->has('workshop_name')) {
                $workshops = Workshop::where('name', 'LIKE', $request['workshop_name'])->get();
            } else {
                $workshops = Workshop::get();
            }
            $response = json_encode($workshops);
        } catch (Exception $e) {
            $response = ["error"=>$e];
        }
        return $response;
    }

    /**
     * Gets the workshop_attendance record for a user
     *  parameters:
     *      after(optional) - only return attendance records after the specified date (formated as 2025-04-29)
     */
    public function get_workshop_attendance_for_user(Request $request, String $workshop_id, String $unique_id) {
        $after = null;
        $query = Module::where('modules.id', $module_id)
            ->select(
                'workshop.name AS workshop_name', 'module_assignments.status AS assignment_status', 'module_assignments.date_due', 
                'module_assignments.date_assigned',  'module_assignments.date_completed',  'users.first_name', 'users.last_name',
                DB::raw("'$unique_id' as b_number"), 'module_versions.id', 'module_versions.name AS version_name', 
                'module_versions.created_at AS version_date')
            ->leftJoin('module_assignments', 'module_assignments.module_id', 'modules.id')
            ->leftJoin('users', 'module_assignments.user_id', 'users.id')
            ->leftJoin('module_versions', 'modules.module_version_id', 'module_versions.id');
        try {
            if ($request->has('after')) {
                $after = $this->string_to_date($request['after']);
                $query = $query->where('module_assignments.date_assigned', '>=', $after);
            }
            $query = $query->where('users.unique_id', $unique_id)
                ->where('module_assignments.module_id', $module_id)
                ->orderBy('modules.module_version_id', 'desc')
                ->first();
            if ($query == null) {
                if ($after != null) {
                    $error = "Module " . $module_id . " has not been assigned or completed by the user " . $unique_id." within the time period";
                } else {
                    $error = "The module " . $module_id . " has not been assigned to the user " . $unique_id;
                }
                $response = ["error"=>$error];
            } else {
                $version_query = ModuleVersion::where('module_id', $module_id)
                    ->orderBy('id', 'desc')->first();
                // $query['latest_version'] = $version_query['id'] == $query['module_version_id'];
                if ($after != null) {
                    if ($query['assignment_status'] != 'complete') {
                        $query['assignment_status'] = 'incomplete';
                    }
                }
                $response = $query;
            }
        } catch (Exception $e) {
            $response = ["error"=>$e];
        }
        return $response;
    }

    /**
     * set the status and attendance for a workshop and user
     *  parameters:
     *      status (required) - "not_applicable", "uncompleted", "completed"
     *      attendance (required) - "registered", "attended", "completed"
     */
    public function update_user_workshop_status_and_attendance(Request $request, $workshop_id, $user_id) {
        try {
            if ($request->has('status') && $request->has('attendance')){
                $status = $request['status'];
                $attendance = $request['attendance'];
                
                if (in_array($status, $this->allowed_workshop_statuses) && in_array($attendance, $this->allowed_workshop_attendances)) {
                    $workshop = Workshop::where('id', $workshop_id)->get();
                        if (!empty($workshop)) {
                            $user = get_user_for_unique_id($unique_id);
                            if ($user != null) {
                                $now = now()->timestamp;
                                WorkshopAttendance::update([
                                    'user_id' => $user->id,
                                    'status' => $status,
                                    'attendance' => $attendance,
                                    'updated_at' => $now] ,['type' => 'external'])
                                    ->where('workshop_id', $workshop_id)
                                    ->where('user_id', $user->id);
                                    $query = WorkshopAttendance::where('workshop_id', $workshop_id)
                                        ->where('user_id', $user->id)->get();
                                    $rosponse = json_encode($query);
                            } else {
                                $response = ["error"=>"User not found"];    
                            }
                        } else {
                            $response = ["error"=>"Unfound workshop id."];
                        }
                } else {
                    $response = ["error"=>"Invalid status"];
                }
            } else {
                $response = ["error"=>"Invalid status"];
            }
        } catch (Exception $e) {
            $response = ["error"=>$e];
        }
        return $response;
    }

    /**
     *  gets all workshop attendance for the users of a group where the workshop id = workshop_id
     *  parameters:
     *      after (optional) - only return records where the workshop date is after a specifice date 
     *  returns: 
     *      workshop, workshop_attendance, workshop_offering, group, and user data
     */
    public function get_group_users_status_for_workshops(Request $request, $workshop_id) {
        $users = WorkshopAttendance::select(
                    "workshops.name AS workshop_name", "workshops.id AS workshop_id", "groups.name AS group_name",
                    "groups.id AS group_id", "users.unique_id AS bnumber", "workshop_attendances.attendance",
                    "workshop_attendances.id AS workshop_id", "workshop_attendances.workshop_offering_id",
                    "workshop_offerings.workshop_date", "module_assignments.date_due",
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) AS user_name"))
            ->leftJoin("users", 'users.id', 'workshop_attendences.user_id')
            ->leftJoin("group_memberships", 'group_memberships.user_id', 'users.id')
            ->leftJoin("workshops", "workshops.id", "workshop_attendances.module_id")
            ->leftJoin("groups", "groups.id", "group_memberships.group_id")
            ->leftJoin("workshop_attendances", "workshop_attendances.workshop_id", "workshop.id")
            ->leftJoin("workshop_offerings", "workshop_offerings.workshop_id", "workshop.id")
            ->where('groups.slug', $group_slug)
            ->where('workshops.id', $module_id);
        if ($request->has('after')) {
            $users = $users->where('workshop_offering.workshop_date', $request['after']);
        }
        $users = $users->get();
        return $users;
    }
}