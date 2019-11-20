<?php

namespace App\Http\Controllers;

use App\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\ModuleAssignment;
use App\UserPermission;
use App\ModuleVersion;

class UserController extends Controller
{
    public function get_all_users() {
        return User::all();
    }

    public function get_user(Request $request, User $user) {
        return $user;
    }

    public function add_user(Request $request) {
        $user = new User($request->all());
        // $user->params = (Object)$request->except(['first_name','last_name','unique_id','id','email']);
        $user->save();
        return $user;
    }

    public function update_user(Request $request, User $user) {
        $user->update($request->all());
        // $user->params = (Object)$request->except(['first_name','last_name','unique_id','id','email']);
        return $user;
    }

    public function delete_user(Request $request, User $user) {
        $user->delete();
        return "1";
    }

    public function assign_module(Request $request, User $user, ModuleVersion $module_version) {
        if ($request->has('due_date')) {
            $due_date = $request->due_date;
//            dd()
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
    public function set_assignment(Request $request, User $user) {
        $module_version = ModuleVersion::where('id',$request->module_version_id)->first();
        $module_assignment = new ModuleAssignment([
            'module_version_id' =>$module_version->id,
            'module_id' => $module_version->module_id,
            'user_id' => $user->id,
            'date_assigned' => $request->date_assigned,
            'date_due' => $request->date_due,
        ]);
        $module_assignment->save();
        return ModuleAssignment::where('id',$module_assignment->id)->with('version')->with('user')->get();;
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
            $users = User::select('id','unique_id','first_name','last_name','email','params')
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
            $users = User::select('id','unique_id','first_name','last_name','email','params')
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
            $users[$index] = array_intersect_key($user, array_flip(['id','unique_id','first_name','last_name','email','params']));
        }
        return $users;
    }


}