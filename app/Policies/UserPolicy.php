<?php

namespace App\Policies;

use App\ModuleAssignment;
use App\User;
use App\Module;
use App\ModuleVersion;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function manage_users(User $user)
    {
        if (in_array('manage_users',$user->user_permissions)) {
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
    public function view_modules(User $user)
    {
//        $is_module_owner = is_null(Module::where('owner_user_id',$user->id)->select('id')->first())?false:true;
//        if($is_module_owner || in_array('manage',$user->module_perms()))
        return ($user->module_perms());

    }

    public function assign_module_version(User $user) {
        // if user has assign modules on global permissions ==> return true;
        $module_version_id = request()->module_version_id;
        $module_version = ModuleVersion::where('id',$module_version_id)->first();
        $module = Module::where('id',$module_version->module_id)->first();
        // if user has assign modules on $module->id ==> return true
        // if $module->owner_id == $user->id return true;
//        dd($module);
        if(in_array('assign_modules',$user->user_permissions)
        || in_array('assign',$user->module_permissions->{$module->id})
        || $module->owner_user_id === $user->id){
            return true;
        }
    }
    public function delete_assignment(User $user, ModuleAssignment $moduleAssignment){
        $module = Module::where('id',$moduleAssignment->module_id)->first();
        if(in_array('assign_modules',$user->user_permissions)
        || in_array('assign',$user->module_permissions->{$module->id})
        || $module->owner_user_id === $user->id){
            return true;
        }
    }
}