<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\SimpleUser;
use App\GroupMembership;
use App\Group;
use App\ModuleAssignment;
use App\Module;

class PublicAPIController extends Controller
{
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

     //todo New Impersonate
     public function impersonate_user(User $user){
        $encryption_obj = [
            'unique_id'=>$user->unique_id,
            'timestamp'=>now()->timestamp
        ];
        return url('/manage/'.Crypt::encrypt(json_encode($encryption_obj)));
    }
}