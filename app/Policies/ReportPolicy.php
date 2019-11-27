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
        $has_report_perm = is_null(ModulePermission::where('user_id',$user->id)->where('permission','report')->select('id')->first())?false:true;
        if(in_array('run_reports',$user->user_permissions)
            || in_array('manage_reports',$user->user_permissions)
            || $is_report_owner
            || $has_report_perm
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

}
