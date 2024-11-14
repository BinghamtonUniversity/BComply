<?php

namespace App\Http\Controllers;

use App\Group;
use App\GroupMembership;
use App\ModulePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\ModuleAssignment;
use App\UserPermission;
use App\ModuleVersion;
use App\Module;
use Carbon\Carbon;

class UserController extends Controller
{

    public function get_all_users() {
        return User::all();
    }

    public function get_user(Request $request, User $user) {
        return User::where('id',$user->id)
            ->with('pivot_groups')
            ->with('pivot_module_permissions')
            ->with('owned_modules')
            ->with('pivot_module_assignments')
            ->first();
    }

    public function add_user(Request $request) {
        $user = new User($request->all());
        $user->save();
        return $user;
    }

    public function update_user(Request $request, User $user) {
        $user->update($request->all());
        return $user;
    }

    public function delete_user(Request $request, User $user) {
        ModulePermission::where('user_id',$user->id)->delete();
        ModuleAssignment::where('user_id',$user->id)->delete();
        GroupMembership::where('user_id',$user->id)->delete();
        UserPermission::where('user_id',$user->id)->delete();
        $user->delete();
        return "1";
    }

    public function merge_user(Request $request, User $source_user, User $target_user) {
        $errors = [];
        try {
            ModulePermission::where('user_id',$source_user->id)->update(['user_id'=>$target_user->id]);
        } catch (\Exception $e) { $errors[] = 'Unable to Migrate Module Permissions due to conflict';}
        try {
            ModuleAssignment::where('user_id',$source_user->id)->update(['user_id'=>$target_user->id]);
        } catch (\Exception $e) { $errors[] = 'Unable to Migrate Module Assignments due to conflict';}
        try {
            GroupMembership::where('user_id',$source_user->id)->update(['user_id'=>$target_user->id]);
        } catch (\Exception $e) { $errors[] = 'Unable to Migrate Group Memberships due to conflict';}
        try {
            UserPermission::where('user_id',$source_user->id)->update(['user_id'=>$target_user->id]);
        } catch (\Exception $e) { $errors[] = 'Unable to Migrate User Permissions due to conflict';}
        if (count($errors)>0) {
            if ($request->has('delete') && $request->delete == 'true') {
                $errors[] = 'Refusing to delete source user due to errors';
            } 
            return ['success'=>false,'errors'=>$errors];
        } else {
            if ($request->has('delete') && $request->delete == 'true') {
                $source_user->delete();
            }    
            return ['success'=>true];
        }
    }

    public function login_user(Request $request, User $user) {
        Auth::login($user);
        return "1";
    }

    public function assign_module(Request $request, User $user, ModuleVersion $module_version) {
        if ($request->has('due_date')) {
            $due_date =$request->due_date;
        } else {
            $due_date = null;
        }
        $module_assignment = new ModuleAssignment([
            'user_id' => $user->id,
            'module_version_id' => $module_version->id,
            'module_id' => $module_version->module_id,
            'date_assigned' => now(),
            'date_due' => $due_date,
            'assigned_by_user_id' => $user->id,
        ]);
        $module_assignment->save();
        return $module_assignment;
    }

    public function set_permissions(Request $request, User $user) {
        $request->validate([
            'permissions' => 'array',
        ]);
        UserPermission::where('user_id',$user->id)->delete();
        foreach($request->permissions as $permission) {
            $permission = new UserPermission([
                'user_id' =>$user->id,
                'permission' => $permission
            ]);
            $permission->save();
        }
        return $request->permissions;
    }
    public function get_permissions(Request $request, User $user) {
        return $user->user_permissions;
    }

    public function get_assignments(Request $request, User $user) {
        return ModuleAssignment::where('user_id',$user->id)->with('version')->with('user')->get();
    }
    public function set_assignment(Request $request, User $user, Module $module) {
        $assignment = $module->assign_to([
            'user_id' => $user->id,
            'date_due' => $request->date_due,
            'assigned_by_user_id' => Auth::user()->id,
        ]);
        if ($assignment === false) {
            return response(['error'=>'The specified module does not have a current version'], 404)->header('Content-Type', 'application/json');
        } else if (is_null($assignment)) {
            return response(['error'=>'The user is already assigned to this module'], 409)->header('Content-Type', 'application/json');
        }
//        else if($assignment !== $module->current_version()){
//            return response(['error']=>'This is not the current version')
//        }
        else {
            return ModuleAssignment::where('id',$assignment->id)->with('version')->with('user')->first();
        }
    }

    public function self_assignment(Request $request, Module $module){
        $assignment = $module->assign_to([
            'user_id'=>Auth::user()->id,
            'date_due'=>Carbon::now()->addDays(1), // Make due date the next day!
            'assigned_by_user_id'=>Auth::user()->id
        ]);
        if ($assignment === false) {
            return response(['error'=>'The specified module does not have a current version'], 404)->header('Content-Type', 'application/json');
        } else if (is_null($assignment)) {
            return response(['error'=>'The user is already assigned to this module'], 409)->header('Content-Type', 'application/json');
        } else {
            return ModuleAssignment::where('id',$assignment->id)->with('version')->with('user')->first();
        }
    }

    public function delete_assignment(Request $request, User $user, ModuleAssignment $module_assignment) {
        $module_assignment->delete();
        return "Success";
    }

    public function search($search_string='') {
        $search_elements_parsed = preg_split('/[\s,]+/',strtolower($search_string));
        $search = []; $users = [];
        if (count($search_elements_parsed) === 1 && $search_elements_parsed[0]!='') {
            $search[0] = $search_elements_parsed[0];
            $users = User::select('id','unique_id','first_name','last_name','email')
                ->where(function ($query) use ($search) {
                    $query->where('unique_id',$search[0])
                        ->orWhere('id',$search[0])
                        ->orWhere('first_name','like',$search[0].'%')
                        ->orWhere('last_name','like',$search[0].'%')
                        ->orWhere('email','like',$search[0].'%');
                })->orderBy('first_name', 'asc')->orderBy('last_name', 'asc')
                    ->limit(25)->get()->toArray();
        } else if (count($search_elements_parsed) > 1) {
            $search[0] = $search_elements_parsed[0];
            $search[1] = $search_elements_parsed[count($search_elements_parsed)-1];
            $users = User::select('id','unique_id','first_name','last_name','email')
                ->where(function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('first_name','like',$search[0].'%')
                            ->where('last_name','like',$search[1].'%');
                    })->orWhere(function ($query) use ($search) {
                        $query->where('first_name','like',$search[1].'%')
                            ->where('last_name','like',$search[0].'%');
                    });
                })->orderBy('first_name', 'asc')->orderBy('last_name', 'asc')
                    ->limit(25)->get()->toArray();
        }
        foreach($users as $index => $user) {
            $users[$index] = array_intersect_key($user, array_flip(['id','unique_id','first_name','last_name','email']));
        }

        return $users;
    }

    public function bulk_inactivate(Request $request){
        $unique_ids = array_unique(preg_split('/(,|\n| )/',$request->unique_ids,-1, PREG_SPLIT_NO_EMPTY));
        $users = User::select('id','unique_id','first_name','last_name')->whereIn('unique_id',$unique_ids)->get();
        User::whereIn('unique_id',$unique_ids)->update(['active'=>0]);
        return ['count'=>count($users),'users'=>$users];
    }

}