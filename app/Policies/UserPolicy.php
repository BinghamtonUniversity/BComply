<?php

namespace App\Policies;

use App\ModuleAssignment;
use App\User;
use App\Module;
use App\ModuleVersion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    use HandlesAuthorization;
    public function view_admin_dashboard(User $user)
    {
        $is_module_owner = is_null(Module::where('owner_user_id',$user->id)->select('id')->first())?false:true;
        return (!is_null($user->module_perms()->first())
            || !is_null($user->user_perms()->first()) ||
            $is_module_owner
        );

    }
    public function view_in_admin(User $user){
        if(in_array('manage_user_permissions',$user->user_permissions)
            || $this->manage_users($user)
        ){
            return true;
        }
    }
    public function manage_users(User $user)
    {
        if (in_array('manage_users',$user->user_permissions)) {
            return true;
        }
    }
    public function impersonate_users(User $user) {
        if (in_array('impersonate_users',$user->user_permissions)) {
            return true;
        }
    }
    public function manage_user_permissions(User $user){
        if(in_array('manage_user_permissions',$user->user_permissions)){
            return true;
        }
    }
//    public function assign_modules(User $user){
//        if(in_array('assign_modules',$user->user_permissions)
//            || in_array('assign',$user->module_permissions->{$module->id})
//            || $module->owner_user_id===$user->id ){
//            return true;
//        }
//    }


    public function assign_module(User $user, Module $module) {
        if(in_array('assign_modules',$user->user_permissions)){
            return true;
        }
        // Must first check if the property exists!
        if (property_exists($user->module_permissions,$module->id) &&
            in_array('assign',$user->module_permissions->{$module->id})) {
            return true;
        }
        if ($module->owner_user_id === $user->id){
            return true;
        }
        if(Auth::user()== $user){
            return true;
        }
    }
    public function delete_assignment(User $user, ModuleAssignment $moduleAssignment){
        $module = Module::where('id',$moduleAssignment->module_id)->first();
        if(in_array('assign_modules',$user->user_permissions)){
            return true;
        }
        // Must first check if the property exists!
        if (property_exists($user->module_permissions,$module->id) &&
            in_array('assign',$user->module_permissions->{$module->id})) {
            return true;
        }
        if ($module->owner_user_id === $user->id){
            return true;
        }
    }
}
