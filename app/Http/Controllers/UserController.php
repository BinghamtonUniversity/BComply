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
        $users = User::all();
        return $users;
    }

    public function get_user(Request $request, User $user) {
        return $user;
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
            'assigned_by_user_id' => 2,
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

}