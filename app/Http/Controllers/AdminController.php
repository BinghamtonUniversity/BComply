<?php

namespace App\Http\Controllers;

use App\BulkAssignment;
use App\User;
use App\Group;
use App\Module;
use App\Workshop;
use App\WorkshopOffering;
use App\WorkshopAttendance;
use App\ModuleVersion;
use App\Report;
use App\WorkshopReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct() {
    }

    /**
     * Renders default admin pages
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function admin(Request $request) {
        return view('default.admin',['page'=>'dashboard','ids'=>[Auth::user()->id],'title'=>'Admin']);
    }

    /**
     * Renders users page.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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

    /**
     * Handle the user "created" event.
     *
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function user_assignments(Request $request, User $user) {
        return view('default.admin',['page'=>'users_assignments','ids'=>[$user->id],'title'=>$user->first_name.' '.$user->last_name.' Module Assignments','help'=>
            'Use this page to manage training module assignments for the current user.  You may add new
            modules, view a status report for currently assigned modules, and remove assigned modules.'
        ]);
    }


    /**
     * Returns/ renders groups page
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function groups(Request $request) {
        return view('default.admin',['page'=>'groups','ids'=>[],'title'=>'Manage Groups','help'=>
            'Use this page to manage groups within the BComply Application.  You may add/remove exsting groups, 
            rename groups, and manage group memeberships.'
        ]);
    }
    /**
     * Handle the user "created" event.
     *
     * @param Request $request
     * @param Group $group
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function group_members(Request $request, Group $group) {
        return view('default.admin',['page'=>'groups_members','ids'=>[$group->id],'title'=>$group->name.' Memberships','help'=>
            'Use this page to add / remove users from the current group.'
        ]);
    }

    /**
     * Handle the user "created" event.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function modules(Request $request) {
        $user = Auth::user();
        return view('default.admin',['page'=>'modules','ids'=>[],'title'=>'Manage Modules',
            'actions' => [
                $user->can('create_modules','App\Module')?["name"=>"create","label"=>"Create New Module"]:'',
                '',
                ["name"=>"edit","label"=>"Update Existing Module"],
                ["label"=>"Manage Versions","name"=>"manage_versions","min"=>1,"max"=>1,"type"=>"default"],
                ["label"=>"Admin Permissions","name"=>"manage_admins","min"=>1,"max"=>1,"type"=>"default"],
                ["label"=>"Manage Assignments","name"=>"manage_assignments","min"=>1,"max"=>1,"type"=>"default"],
                ["label"=>"URL","name"=>"get_url","min"=>1,"max"=>1,"type"=>"default"],
                '',
                $user->can('delete_module','App\Module')?["name"=>"delete","label"=>"Delete Module"]:''
            ],
            'help'=>
                'Use this page to manage modules within the BComply Application.  You may create new
                modules, manage administrative permissions for modules, and manage module versions.'
        ]);
    }
 /**
     * Handle the user "created" event.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function workshops(Request $request) {
        $user = Auth::user();
        return view('default.admin',['page'=>'workshops','ids'=>[],'title'=>'Manage Workshops',
            'actions' => [
                ["name"=>"create","label"=>"Create New Workshop"],           
                ["name"=>"edit","label"=>"Update Existing Workshop"],
                ["name"=>"upload_file","label"=>"Upload Files","min"=>1,"max"=>1,"type"=>"default"],
                ["name"=>"manage_files","label"=>"Manage Files","min"=>1,"max"=>1,"type"=>"default"],
                ["name"=>"manage_offerings","label"=>"Manage Workshop Offerings","min"=>1,"max"=>1,"type"=>"default"],
                ["name"=>"delete","label"=>"Delete Workshop"],
            ],
            'help'=>
                'Use this page to manage workshops within the BComply Application.  You may create new
                workshops.'
        ]);
    }
     /**
     * Handle the user "created" event.
     *
     * @param Request $request
     * @param Workshop $workshop
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function workshop_files(Request $request,Workshop $workshop) {
        $user = Auth::user();

        return view('default.admin',['page'=>'workshops_files','ids'=>[$workshop->id],'title'=>'Manage Files',
            'actions' => [
                ["name"=>"upload_file","label"=>"Upload / Change File","type"=>"success","min"=>0], 
                ["name"=>"edit","label"=>"Change File Name"],
                ["name"=>"delete","label"=>"Delete File"],
         
            ],
            'help'=>
                'Use this page to manage workshop files within the BComply Application.  You may upload new
                file, change the existing file, or delete the file.'
        ]);
    }
         /**
     * Handle the user "created" event.
     *
     * @param Request $request
     * @param Workshop $workshop
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function workshop_offerings(Request $request,Workshop $workshop) {
        return view('default.admin',['page'=>'workshops_offerings','ids'=>[$workshop->id],'title'=>$workshop->name.' Offerings',          
        'actions' => [
            ["name"=>"create","label"=>"Create New Workshop Offering"],           
            ["name"=>"edit","label"=>"Update Existing Workshop Offering"], 
            ["name"=>"manage_attendance","label"=>"Manage Workshop Offerings Attendance","min"=>1,"max"=>1,"type"=>"default"],
            ["name"=>"delete","label"=>"Delete Workshop Offering"],
            '',
            ["name"=>"create_recurring","label"=>"Create Recurring Workshop Offering","type"=>"warning"],
        ],'help'=>
            'Use this page to manage training workshop offerings.  You may add new
            users, view a status report for currently assigned users, and remove assigned users.'
        ]);
    }

         /**
     * Handle the user "created" event.
     *
     * @param Request $request
     * @param Workshop $workshop
     * @param WorkshopOffering $offering
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function workshop_attendances(Request $request,WorkshopOffering $offering) {
        //TODO
        return view('default.admin',['page'=>'workshops_offerings_attendances','ids'=>[$offering->id],'title'=>$offering->name.' Offerings',          
        'actions' => [
            ["name"=>"create","label"=>"Create New Workshop Attendance"],           
            ["name"=>"edit","label"=>"Update Existing Workshop Attendance"],
            ["name"=>"delete","label"=>"Delete Workshop Attendance"],
        ],'help'=>
            'Use this page to manage training workshop attendances.'
        ]);
    }
             /**
     * Handle the user "created" event.
    * @param Request $request
    * @param Workshop $workshop
    * @param WorkshopOffering $offering
    * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    */
   public function workshop_offering_attendances(Request $request,Workshop $workshop,WorkshopOffering $offering) {
       //TODO
       return view('default.admin',['page'=>'workshops_offerings_attendances','ids'=>[$workshop->id,$offering->id],'title'=>$workshop->name.' Attendances',          
       'actions' => [
           ["name"=>"create","label"=>"Create New Workshop Attendance"],           
           ["name"=>"edit","label"=>"Update Existing Workshop Attendance"],
           ["name"=>"delete","label"=>"Delete Workshop Attendance"],
       ],'help'=>
           'Use this page to manage training workshop attendances.'
       ]);
   }
    /**
     * @param Request $request
     * @param Module $module
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function module_versions(Request $request, Module $module) {
        return view('default.admin',['page'=>'modules_versions','ids'=>[$module->id],'title'=>$module->name.' Versions','help'=>
            'Use this page to manage module versions.  You may create, modify, 
            delete, upload, and configure module version.'
        ]);
    }

    /**
     * @param Request $request
     * @param Module $module
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function module_permissions(Request $request, Module $module) {
        return view('default.admin',['page'=>'modules_permissions','ids'=>[$module->id],'title'=>$module->name.' Admin Permissions','help'=>
            'Use this page to manage module permissions within the BComply Application.  You may 
            add new permissions for a specified user, or remove existing permissions for that use.'
        ]);
    }

    /**
     * @param Request $request
     * @param Module $module
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function module_assignments(Request $request, Module $module) {
        return view('default.admin',['page'=>'modules_assignments','ids'=>[$module->id],'title'=>$module->name.' Assignments','help'=>
            'Use this page to manage training module assignments for the current module version.  You may add new
            users, view a status report for currently assigned users, and remove assigned users.'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reports(Request $request) {
        $user = Auth::user();
        return view('default.admin',['page'=>'reports','ids'=>[],'title'=>'Reports',
            'actions' => [
                $user->can('manage_reports','App\Report')?["name"=>"create","label"=>"Create New Report"]:'',
                '',
                $user->can('see_update_buttons','App\Report')?["name"=>"edit","label"=>"Edit Description"]:'',
                $user->can('see_update_buttons','App\Report')?["label"=>"Configure Query","name"=>"configure_query","min"=>1,"max"=>1,"type"=>"default"]:'',
                ["label"=>"Run Report","name"=>"run_report","min"=>1,"max"=>1,"type"=>"warning"],
                '',
                $user->can('see_update_buttons','App\Report')?["name"=>"delete","label"=>"Delete Report"]:""
            ],
            'help'=>
                'Build and Manage Reports'
        ]);
    }

    /**
    
     * @param Request $request
     * @param Report $report
     * @param Module $module
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function run_report(Request $request, Report $report, Module $module) {
        return view('default.admin',['page'=>'reports_execute','ids'=>[$report->id],'title'=>$report->name,'help'=>$report->description
        ]);
    }

        /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function workshop_reports(Request $request) {
        $user = Auth::user();
        return view('default.admin',['page'=>'workshop_reports','ids'=>[],'title'=>'Workshop Reports',
        'actions' => [
            $user->can('manage_reports','App\WorkshopReport')?["name"=>"create","label"=>"Create New Workshop Report"]:'',           
            $user->can('see_update_buttons','App\Report')?["name"=>"edit","label"=>"Update Existing Workshop Report"]:'',
            $user->can('see_update_buttons','App\Report')?["label"=>"Configure Query","name"=>"configure_query","min"=>1,"max"=>1,"type"=>"default"]:'',
            ["label"=>"Run Report","name"=>"run_report","min"=>1,"max"=>1,"type"=>"warning"],
            '',
            $user->can('see_update_buttons','App\Report')?["name"=>"delete","label"=>"Delete Workshop Report"]:'',
      
        ],
            'help'=>
                'Build and Manage Workshop Reports'
        ]);

    }

       /**
     * @param Request $request
     * @param WorkshopReport $workshop_report
     * @param Workshop $workshop
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function run_workshop_report(Request $request, WorkshopReport $workshop_report, Workshop $workshop) {
        return view('default.admin',['page'=>'workshop_reports_execute','ids'=>[$workshop_report->id],'title'=>$workshop_report->name,'help'=>$workshop_report->description
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function bulk_assignments(Request $request){
        return view('default.admin',['page'=>'bulkAssignments','ids'=>[],'title'=>'Bulk Assignments','help'=>'Use this page to manage assignments within the BComply Application.  You may create new
            bulk assignment rules'
        ]);

    }

    // Don't do this! Hella Bad and loses all data!
    public function refresh_db(Request $request) {
        if (config('app.env')==='development' || config('app.env')==='dev') {
            $response = Artisan::call('migrate:refresh',['--seed'=>null]);
            return ['msg'=>'Running php artisan migrate:refresh --seed','ret'=>$response];
        } else {
            return ['msg'=>'App In Production, Not Allowed','ret'=>false];
        }
    }


}
