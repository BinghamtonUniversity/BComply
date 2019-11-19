<?php

namespace App\Http\Controllers;

use App\Module;
use App\ModulePermission;
use Illuminate\Http\Request;
use App\ModuleVersion;

class ModuleController extends Controller
{
    public function get_alL_modules(){
        return Module::all();
    }
    public function get_module(Request $request, Module $module){
        return $module;
    }
    public function get_module_versions(Request $request, Module $module){
        return ModuleVersion::where('module_id', $module->id)->get();
    }
    public function add_module(Request $request,Module $module){
        $module = new Module($request->all());
        $module->save();
        return $module;
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
        $module_version->delete();
        return 'Success';
    }
    public function update_module(Request $request,Module $module){
        $module->update($request->all());
        $module->save();
        return $module;
    }
    public function delete_module(Request $request,Module $module){
        $module->delete();
        return 'Success';
    }
    public function set_permissions(Request $request, User $module)
    {
        ModulePermission::where('module_id', $module->id)->delete();
        if ($request->has('permissions')) {
            foreach ($request->permissions as $permission) {
                $permission = new ModulePermission([
                    'module_id' => $module->id,
                    'permission' => $permission
                ]);
                $permission->save();
            }
        }
    }
}
