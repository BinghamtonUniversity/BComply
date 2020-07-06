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
        $module_version->reference = ['filename'=>'story.html'];
        $module_version->save();
        return $module_version;
    }
    public function update_module_version(Request $request,Module $module, ModuleVersion $module_version){
        $module_version->update($request->all());
        $module_version->save();
        return $module_version;
    }
    public function delete_module_version(Request $request,Module $module, ModuleVersion $module_version){

        $module_version->delete();
        return 'Success';
    }
    public function get_module_assignments(Request $request,Module $module){
        return ModuleAssignment::where('module_id',$module->id)
            ->select('id','module_id','module_version_id','user_id','date_assigned','date_completed','date_due','date_started')
            ->with(['user'=>function($query){
                $query->select('id','first_name','last_name');
            }])
            ->with(['version'=>function($query){
                $query->select('id','name');
            }])->get();
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
