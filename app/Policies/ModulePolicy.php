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
    public function view_in_admin(User $user){
        $is_module_owner = is_null(Module::where('owner_user_id',$user->id)->select('id')->first())?false:true;
//        dd($user->module_perms());
//        $modules = Module::where('owner_user_id',$user->id)->select('id')->first();
//        dd();
//        dd();
        if(in_array('manage_modules',$user->user_permissions)
        || in_array('assign_modules',$user->user_permissions)
        || $is_module_owner
        || !is_null($user->module_perms()->first())
         ){
            return true;
        }
    }


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
        if(in_array('manage_modules',$user->user_permissions)) {
            return true;
        }
        if ($module->owner_user_id === $user->id) {
            return true;
        }
        if (property_exists($user->module_permissions,$module->id) &&
            in_array('manage',$user->module_permissions->{$module->id})){
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
        if( property_exists($user->module_permissions,$module->id) &&
            in_array('report',$user->module_permissions->{$module->id})) {
            return true;
        }
        
        if ($this->manage_module($user,$module)){
            return true;
        }
    }
}
