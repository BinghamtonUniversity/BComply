<?php

namespace App\Http\Controllers;

use App\User;
use App\Group;
use App\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;


use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct() {
    }
    public function admin(Request $request) {
        return view('default.admin',['page'=>null,'id'=>null,'title'=>'Admin']);
    }
    public function users(Request $request) {
        return view('default.admin',['page'=>'users','id'=>null,'title'=>'Manage Users','help'=>
            'Use this page to manage users within the BComply Application.  You may add/remove existing users, 
            modify user administrative permissions, and assign training modules to existing users.'
        ]);
    }
    public function user_assignments(Request $request, User $user) {
        return view('default.admin',['page'=>'users_assignments','id'=>$user->id,'title'=>$user->first_name.' '.$user->last_name.' Module Assignments','help'=>
            'Use this page to manage training module assignments for the current user.  You may add new
            modules, view a status report for currently assigned modules, and remove assigned modules.'
        ]);
    }
    public function groups(Request $request) {
        return view('default.admin',['page'=>'groups','id'=>null,'title'=>'Manage Groups','help'=>
            'Use this page to manage groups within the BComply Application.  You may add/remove exsting groups, 
            rename groups, and manage group memeberships.'
        ]);
    }
    public function group_members(Request $request, Group $group) {
        return view('default.admin',['page'=>'groups_members','id'=>$group->id,'title'=>$group->name.' Memberships','help'=>
            'Use this page to add / remove users from the current group.'
        ]);
    }
    public function modules(Request $request) {
        return view('default.admin',['page'=>'modules','id'=>null,'title'=>'Manage Modules','help'=>
            'Use this page to manage modules within the BComply Application.  You may create new
            modules, manage administrative permissions for modudles, and manage module versions.'
        ]);
    }
    public function module_versions(Request $request, Module $module) {
        return view('default.admin',['page'=>'modules_versions','id'=>$module->id,'title'=>$module->name.' Versions','help'=>
            'Use this page to manage module versions.  You may create, modify, 
            delete, upload, and configure module version.'
        ]);
    }
    public function module_permissions(Request $request, Module $module) {
        return view('default.admin',['page'=>'modules_permissions','id'=>$module->id,'title'=>$module->name.' Admin Permissions','help'=>
            'Use this page to manage module permissions within the BComply Application.  You may 
            add new permissions for a specified user, or remove existing permissions for that use.'
        ]);
    }
    public function reports(Request $request) {
        return view('default.admin',['page'=>'reports','id'=>null,'title'=>'Reports','help'=>
            'Build Reports.'
        ]);
    }


}
