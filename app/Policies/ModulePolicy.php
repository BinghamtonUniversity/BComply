<?php

namespace App\Policies;

use App\Module;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class ModulePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage modules.
     *
     * @param  \App\User  $user
     * @return mixed
     */
//    public function view_modules(User $user){
//        $is_report_owner = is_null(Module::where('owner_user_id',$user->id)->select('id')->first())?false:true;
//        if($this->manage_module($user,$module)||)
//
//    }
    public function manage_all_modules(User $user)
    {
        if(in_array('manage_modules',$user->user_permissions) || sizeof((Array)$user->module_permissions)>0){
            return true;
        }
    }

    public function create_modules(User $user){
        if(in_array('manage_modules',$user->user_permissions)){
            return true;
        }
    }

    //To update and assign
    public function manage_module(User $user, Module $module)
    {
//        dd($module);
        if(in_array('manage_modules',$user->user_permissions)
            || $module->owner_user_id === $user->id
            || in_array('manage',$user->module_permissions->{$module->id})){
            return true;
        }
    }

    public function delete_module(User $user){
        if(in_array('manage_modules',$user->user_permissions)){
            return true;
        }
    }




    public function view_module(User $user, Module $module)
    {
        $is_report_owner = is_null(Module::where('owner_user_id',$user->id)->select('id')->first())?false:true;
        if(in_array('report',$user->module_permissions->{$module->id})
            || $this->manage_module($user,$module)){
            return true;
        }
    }
}
