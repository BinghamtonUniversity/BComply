<?php

namespace App\Http\Controllers;

use App\User;
use App\Module;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct() {
    }

    public function admin(Request $request) {
        return view('default.admin',['page'=>null,'id'=>null,'title'=>'Admin']);
    }
    public function users(Request $request) {
        return view('default.admin',['page'=>'users','id'=>null,'title'=>'Users']);
    }
    public function groups(Request $request) {
        return view('default.admin',['page'=>'groups','id'=>null,'title'=>'Groups']);
    }
    public function modules(Request $request) {
        return view('default.admin',['page'=>'modules','id'=>null,'title'=>'Modules']);
    }
    public function module_versions(Request $request, Module $module) {
        return view('default.admin',['page'=>'module_versions','id'=>$module->id,'title'=>'Module "'.$module->name.'" Versions']);
    }


}
