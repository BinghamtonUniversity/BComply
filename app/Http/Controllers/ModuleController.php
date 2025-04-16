<?php

namespace App\Http\Controllers;

use App\BulkAssignment;
use App\Report;
use Illuminate\Support\Facades\Storage;
use App\Module;
use App\User;
use App\ModulePermission;
use Illuminate\Http\Request;
use App\ModuleVersion;
use App\ModuleAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class ModuleController extends Controller
{
    public function get_all_modules(){
        if (in_array('manage_modules',Auth::user()->user_permissions) ||
            in_array('assign_modules',Auth::user()->user_permissions)) {

            // If user can manage modules, return all modules
            return Module::with('owner')->with('current_version')->get();
        }
        else {
            // Only return modules where the user has admin permissions
            return Module::whereIn('id',array_keys((Array)(Auth::user()->module_permissions)))
                ->orWhere('owner_user_id','=',Auth::user()->id)->with('owner')->with('current_version')->get();
        }
    }
    public function get_public_module_versions(){
        return Module::where('public',true)->where('module_version_id','<>',null)->with('current_version')->get();
    }
    public function get_module(Request $request, Module $module){
        return $module->with('owner')->first();
    }
    public function get_user_modules(Request $request, Module $module, User $user){
//        dd($request);
        return Module::where('owner_user_id','=',Auth::user()->id) ->get();
    }
    public function get_module_versions(Request $request, Module $module=null){
        if (!is_null($module)) {
            return ModuleVersion::where('module_id', $module->id)->get();
        } else {
            return ModuleVersion::get();
        }
    }
    public function add_module(Request $request){
        $module = new Module($request->all());
        $module->save();

        return $module->where('id',$module->id)->with('owner')->first();
    }
    public function add_module_version(Request $request,Module $module){
        $module_version = new ModuleVersion($request->all());
        $module_version->module_id = $module->id;
        $module_version->save();
        return $module_version;
    }
    public function update_module_version(Request $request,Module $module, ModuleVersion $module_version){
        $module_version->update($request->all());
        $module_version->save();
        return $module_version;
    }
    public function delete_module_version(Request $request,Module $module, ModuleVersion $module_version){
        // Delete all existing assignments for this module
        ModuleAssignment::where('module_version_id',$module_version->id)->delete();
        $module_version->delete();
        return 'Success';
    }
    public function get_module_assignments(Request $request,Module $module){
        // TJC -- Written like this to be faster and more efficient 6/21/23
        $assignments = DB::table('module_assignments')
            ->select(
                'module_assignments.id as id',
                'module_versions.name as version',
                'users.id as user_id',
                'users.first_name as first', 
                'users.last_name as last',
                'module_assignments.status',
                'module_assignments.score',
                'module_assignments.duration',
                'module_assignments.date_assigned as assigned', 
                'module_assignments.date_due as due', 
                'module_assignments.date_started as started', 
                'module_assignments.date_completed as completed')
            ->leftJoin('module_versions','module_assignments.module_version_id','=','module_versions.id')
            ->leftJoin('users','module_assignments.user_id','=','users.id')
            ->where('module_assignments.module_id',$module->id)->get();
        return $assignments;
    }
    public function update_module(Request $request,Module $module){
        $module->update($request->all());
        $module->save();

        return $module->where('id',$module->id)->with('owner')->first();
    }
    public function delete_module(Request $request,Module $module){
        BulkAssignment::whereJsonContains('assignment',['module_id'=>(String)$module->id])->delete();
        ModuleAssignment::where('module_id',$module->id)->delete();
        ModuleVersion::where('module_id',$module->id)->delete();
        $module->delete();
        //Delete bulk assignment
        //Delete module assingments
        //Module version
        //Set current version to null -> We are already deleting the module with all of its versions, why assigning the current version to null?
        return 'Success';
    }
    public function get_module_permissions(Request $request, Module $module) {
        return ModulePermission::where('module_id', $module->id)
            ->with('user')->get();        
    }
    public function set_module_permission(Request $request, Module $module) {
        $request->validate(['permission' => 'required']);
        $permission = new ModulePermission([
            'user_id' =>$request->user_id,
            'permission' => $request->permission,
            'updated_by_user_id' => Auth::user()->id,
        ]);
        $permission->module_id = $module->id;
        $permission->save();
        return $permission->where('id',$permission->id)->with('user')->first();
    }
    public function delete_module_permission(Request $request, Module $module, ModulePermission $module_permission) {
        return ModulePermission::where('module_id', $module->id)->where('id',$module_permission->id)->delete();   
        return 'Success';     
    }
}
