<?php

namespace App\Http\Controllers;

use App\Module;
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
            ->where('date_assigned','<=',now())->orderBy('date_assigned','desc')
            ->with('version')->get()->unique('module_id');

        return view('home',['page'=>'home','assignments'=>$assignments,'user'=>Auth::user()]);
    }

    public function my_assignments(Request $request){
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)->with('version')->get();
        $current_assignments=(Array)($this->home($request)['assignments']);
        $user_assignments = [];
        foreach ($assignments as $assignment){
            if(!in_array($assignment, $current_assignments)){
                $user_assignments[] = $assignment;
            }
        }

        return view('user_history',['page'=>'assignment','assignments'=>$user_assignments,'user'=>Auth::user()]);
    }
    public function shop_courses(Request $request, Module $module){
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
