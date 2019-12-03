<?php

namespace App\Http\Controllers;

use App\BulkAssignment;
use App\User;
use App\Group;
use App\Module;
use App\ModuleVersion;
use App\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;


use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct() {
    }
    public function admin(Request $request) {
        return view('default.admin',['page'=>null,'ids'=>[],'title'=>'Admin']);
    }
    public function users(Request $request) {
        $user = Auth::user();
        return view('default.admin',['page'=>'users','ids'=>[],'title'=>'Manage Users',
            'actions' => [
                ["name"=>"create","label"=>"Add User"],
                '',
                ["name"=>"edit","label"=>"Edit User"],
                $user->can('manage_user_permissions','App\User')?["label"=>"Edit Permissions","name"=>"edit_perm","min"=>1,"max"=>1,"type"=>"default"]:'',
                ["label"=>"Manage Assignments","name"=>"assignments","min"=>1,"max"=>1,"type"=>"default"],
                '',
                ["name"=>"delete","label"=>"Delete User"]
            ],
            'help'=>
                'Use this page to manage users within the BComply Application.  You may add/remove existing users, 
                modify user administrative permissions, and assign training modules to existing users.'
        ]);
    }
    public function user_assignments(Request $request, User $user) {
        return view('default.admin',['page'=>'users_assignments','ids'=>[$user->id],'title'=>$user->first_name.' '.$user->last_name.' Module Assignments','help'=>
            'Use this page to manage training module assignments for the current user.  You may add new
            modules, view a status report for currently assigned modules, and remove assigned modules.'
        ]);
    }
    public function groups(Request $request) {
        return view('default.admin',['page'=>'groups','ids'=>[],'title'=>'Manage Groups','help'=>
            'Use this page to manage groups within the BComply Application.  You may add/remove exsting groups, 
            rename groups, and manage group memeberships.'
        ]);
    }
    public function group_members(Request $request, Group $group) {
        return view('default.admin',['page'=>'groups_members','ids'=>[],'title'=>$group->name.' Memberships','help'=>
            'Use this page to add / remove users from the current group.'
        ]);
    }
    public function modules(Request $request) {
        $user = Auth::user();
        return view('default.admin',['page'=>'modules','ids'=>[],'title'=>'Manage Modules',
            'actions' => [
                $user->can('manage_modules','App\Module')?["name"=>"create","label"=>"Create New Module"]:'',
                '',
                ["name"=>"edit","label"=>"Update Existing Module"],
                ["label"=>"Manage Versions","name"=>"manage_versions","min"=>1,"max"=>1,"type"=>"default"],
                ["label"=>"Admin Permissions","name"=>"manage_admins","min"=>1,"max"=>1,"type"=>"default"],
                '',
                $user->can('manage_modules','App\Module')?["name"=>"delete","label"=>"Delete Module"]:''
            ],
            'help'=>
                'Use this page to manage modules within the BComply Application.  You may create new
                modules, manage administrative permissions for modudles, and manage module versions.'
        ]);
    }
    public function module_versions(Request $request, Module $module) {
        return view('default.admin',['page'=>'modules_versions','ids'=>[$module->id],'title'=>$module->name.' Versions','help'=>
            'Use this page to manage module versions.  You may create, modify, 
            delete, upload, and configure module version.'
        ]);
    }
    public function module_permissions(Request $request, Module $module) {
        return view('default.admin',['page'=>'modules_permissions','ids'=>[$module->id],'title'=>$module->name.' Admin Permissions','help'=>
            'Use this page to manage module permissions within the BComply Application.  You may 
            add new permissions for a specified user, or remove existing permissions for that use.'
        ]);
    }
    public function module_assignments(Request $request, Module $module, ModuleVersion $module_version) {
        return view('default.admin',['page'=>'modules_versions_assignments','ids'=>[$module->id, $module_version->id],'title'=>$module_version->name.' Assignments','help'=>
            'Use this page to manage training module assignments for the current module version.  You may add new
            users, view a status report for currently assigned users, and remove assigned users.'
        ]);
    }
    public function reports(Request $request) {
        $user = Auth::user();
        return view('default.admin',['page'=>'reports','ids'=>[],'title'=>'Reports',
            'actions' => [
                $user->can('manage_reports','App\Report')?["name"=>"create","label"=>"Create New Report"]:'',
                '',
                ["name"=>"edit","label"=>"Edit Description"],
                ["label"=>"Configure Query","name"=>"configure_query","min"=>1,"max"=>1,"type"=>"default"],
                ["label"=>"Run Report","name"=>"run_report","min"=>1,"max"=>1,"type"=>"warning"],
                '',
                ["name"=>"delete","label"=>"Delete Report"]
            ],
            'help'=>
                'Build and Manage Reports'
        ]);
    }
    public function run_report(Request $request, Report $report,Module $module) {
        return view('default.admin',['page'=>'reports_execute','ids'=>[$report->id],'title'=>$report->name,'help'=>$report->description
        ]);
    }
    public function bulk_assignments(Request $request){
        return view('default.admin',['page'=>'bulk_assignments','id'=>null,'title'=>'Bulk Assignments','help'=>'Use this page to manage assignments within the BComply Application.  You may create new
            bulk assignment rules'
        ]);

    }
//    public function run_assignment(Request $request,BulkAssignment $bulkAssignment) {
//        return view('default.admin',['page'=>'BulkAssignments_execute','id'=>$bulkAssignment->id,'module'=>$bulkAssignment,'title'=>$bulkAssignment->name,'help'=>$bulkAssignment->description
//        ]);
//    }


}
