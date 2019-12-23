<?php

namespace App\Http\Controllers;

use App\Module;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ModuleAssignment;

class UserDashboardController extends Controller
{
    public function my_assignments(Request $request) {
        if (!Auth::check()) {
            return redirect('/demo');
        }
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->where('date_assigned','<=',now())->orderBy('date_assigned','desc')
//            ->whereNull('date_completed')
            ->with('version')->get()->unique('module_id');
        $elected_assignments=[];
        $modules = Module::all();

        foreach ($assignments as $assignment){
            if(is_null($assignment->date_completed)){
                $module = $modules->where('id',$assignment->module_id)->first();
                if((!$module->past_due && $assignment->date_due>Carbon::now()) || ($module->past_due)){
                    $elected_assignments[]=$assignment;
                }
            }
        }
        return view('my_assignments',['page'=>'my_assignments','assignments'=>$elected_assignments,'user'=>Auth::user()]);
    }

    public function assignment_history(Request $request){
        $current_assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->where('date_assigned','<=',now())->orderBy('date_assigned','desc')
            ->with('version')->get()->unique('module_id');
        $modules = Module::all();
        $elected_assignments=[];
        foreach ($current_assignments as $assignment){
            if(is_null($assignment->date_completed)){
                $module = $modules->where('id',$assignment->module_id)->first();
                if((!$module->past_due && $assignment->date_due>Carbon::now()) || ($module->past_due)){
                    $elected_assignments[]=$assignment->id;
                }
            }
        }
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->with('version')->whereNotIn('id',$elected_assignments)->orderBy('date_assigned','desc')->get();

        return view('history',['page'=>'history','assignments'=>$assignments,'user'=>Auth::user()]);
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
        return redirect('my_assignments');
    }

}
