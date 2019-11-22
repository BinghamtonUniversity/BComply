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
    public function manage_all_modules(User $user)
    {
        if(in_array('manage_modules',$user->user_permissions)){
            return true;
        }
    }
    public function manage_module(User $user, Module $module)
    {
        if(in_array('manage_modules',$user->user_permissions)
            || $module->owner_user_id===$user->id
            || in_array('manage',$user->module_permissions->{$module->id})){
            return true;
        }
    }
    public function run_report(User $user, Module $module)
    {
        if((in_array('report',$user->module_permissions->{$module->id} ))
            || in_array('manage',$user->module_permissions->{$module->id})
            || in_array('manage_modules',$user->user_permissions)
            || $module->owner_user_id===$user->id){
            return true;
        }
    }


}
