<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\SimpleUser;
use App\GroupMembership;
use App\Group;
use App\User;
use App\ModuleAssignment;
use App\Module;
use App\Workshop;
use App\WorkshopOffering;
use App\WorkshopAttendance;
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
    public function add_group_membership(Request $request, $group_slug, $unique_id){
        $group = Group::where("slug",$group_slug)->first();
        if(!isset($group) || is_null($group)){
            return response("Group not found!",404);
        }
        $user = User::where("unique_id",$unique_id)->first();
        
        if(!isset($user) || is_null($user)){
            return response("User not found!",404);
        }
        $group_membership = new GroupMembership([
            'user_id'=>$user->id,
            "group_id"=>$group->id,
            'type' => 'external'
        ]);
        $group_membership->save();
        return response("Successfully added to the group",200);
    }
    
    public function delete_group_membership(Request $request, $group_slug, $unique_id){
        $group = Group::where("slug",$group_slug)->first();
        $user = User::where("unique_id",$unique_id)->first();
        GroupMembership::where('user_id',$user->id)->where("group_id",$group->id)->delete();
        return response("Successfully removed from the group",200);
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
        $user = SimpleUser::where('unique_id',$unique_id)->first();
        if (is_null($user)) {
            return response(['error'=>'The specified user does not exist'], 404)->header('Content-Type', 'application/json');
        }
        return ModuleAssignment::where('user_id',$user->id)
            ->select('id','module_id','module_version_id','user_id','date_assigned','date_completed','date_due','date_started','status')
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
                $occurence;
            
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
}