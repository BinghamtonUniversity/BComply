<?php

namespace App\Policies;

use App\Module;
use App\Report;
use App\User;
use App\ModulePermission;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any reports.
     *
     * @param  \App\User  $user
     * @return mixed
     */

    public function view_reports(User $user){
        $is_report_owner = is_null(Report::where('owner_user_id',$user->id)->select('id')->first())?false:true;
//        $has_report_perm = is_null(ModulePermission::where('user_id',$user->id)->where('permission','report')->select('id')->first())?false:true;
        $has_permission = is_null(Report::whereJsonContains('permissions',$user->id)->select('id')->first())?false:true;
        if(in_array('run_reports',$user->user_permissions)
            || in_array('manage_reports',$user->user_permissions)
            || $is_report_owner
            || $has_permission
//            || $has_report_perm
        ){
            return true;
        }
    }

    public function execute_report(User $user,Report $report){
        $is_report_owner = is_null(Report::where('owner_user_id',$user->id)->where('id',$report->id)->select('id')->first())?false:true;
        $has_permission = is_null(Report::where('id',$report->id)->whereJsonContains('permissions',$user->id)->select('id')->first())?false:true;

        if(in_array('run_reports',$user->user_permissions)
            || $is_report_owner
            || $has_permission
        ){
            return true;
        }
    }

    public function manage_reports(User $user)
    {
        if(in_array('manage_reports',$user->user_permissions)){
            return true;
        }
    }

    public function update_report(User $user, Report $report){
        if($this->manage_reports($user) || $user->id===$report->owner_user_id){
            return true;
        }
    }
    // This policy will decide whether a user can see Edit Query/ Edit/ Delete buttons in "Reports" page where "update_report" policy above is enforced
    public function see_update_buttons (User $user){
        if ($this->manage_reports($user) || (is_null(Report::where('owner_user_id',$user->id)->first())?false:true)){
            return true;
        }
    }

}
