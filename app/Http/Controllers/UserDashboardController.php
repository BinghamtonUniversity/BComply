<?php

namespace App\Http\Controllers;

use App\Module;
use App\ModuleVersion;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ModuleAssignment;

class UserDashboardController extends Controller
{
    public function home(Request $request) {
        if (!Auth::check()) {
            return redirect('/demo');
        }
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->where('date_assigned','<=',now())
            ->with('version')->get();
        return view('home',['page'=>'home','assignments'=>$assignments,'user'=>Auth::user()]);
    }
    public function my_assignments(Request $request){
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->with('version')->get();
        return view('user_history',['page'=>'assignment','assignments'=>$assignments,'user'=>Auth::user()]);
    }
    public function shop_courses(Request $request, Module $module){
//        $assignments = ModuleVersion::where('user_id',Auth::user()->id)
//            ->where('date_assigned','<=',now())
//            ->with('version')->get();

        $courses = Module::where('id');
//        // return $assignments;
        return view('shop',['page'=>'shop','ids'=>[$module->id],'title'=>'Shop Courses','user'=>Auth::user()]);
    }

    public function logout(){
//        if(!Auth::check())
            Auth::logout();
        return redirect('demo');
    }

    public function admin(){
        return redirect('home');
    }

}
