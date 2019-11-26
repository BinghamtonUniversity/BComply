<?php

namespace App\Policies;

use App\Module;
use App\Report;
use App\User;
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
//        $is_module_report = is_null(Module::where(''))
        if(in_array('run_reports',$user->user_permissions)
            || in_array('manage_reports',$user->user_permissions)
            || $is_report_owner
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
        if($this->manage_reports($user,$report)|| $user->id===$report->owner_user_id){
            return true;
        }
    }
    public function run_reports(User $user, Report $report){
        $module = Module::where('owner_user_id',$user->id)->get();

        if(in_array('run_reports',$user->user_permissions)
        || $report->owner_user_id === $user->id
        || in_array('run_report')
        ){
            return true;
        }
    }

}
