<?php

namespace App\Http\Controllers;

use App\Module;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\ModuleAssignment;

class UserDashboardController extends Controller
{
    public function my_assignments(Request $request) {
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->where('date_assigned','<=',now())->orderBy('date_assigned','desc')
            ->whereNull('date_completed')
            ->with('version')
            ->with('module')->get();
        foreach($assignments as $index => $assignment) {
            if (is_null($assignment->icon) || $assignment->icon=='') {$assignments[$index]->icon='book-open';}
        }
        return view('my_assignments',['page'=>'my_assignments','assignments'=>$assignments,'user'=>Auth::user()]);
    }

    public function assignment_history(Request $request){
        $completed_assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->whereNotNull('date_completed')
            ->with('version')
            ->with('module')
            ->orderBy('date_completed','desc')->get();
        foreach($assignments as $index => $assignment) {
            if (is_null($assignment->icon) || $assignment->icon=='') {$assignments[$index]->icon='book-open';}
        }    
        return view('history',['page'=>'history','assignments'=>$completed_assignments,'user'=>Auth::user()]);
    }

    public function shop_courses(Request $request, Module $module){
        return view('shop',['page'=>'shop','ids'=>[$module->id],'title'=>'Shop Courses','user'=>Auth::user()]);
    }

    public function logout(Request $request){
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            if (cas()->checkAuthentication()) {
                cas()->logout();
            }
        } else {
            return response('You are not logged in.', 401);
        }
    }

    public function admin(){
        return redirect('my_assignments');
    }

}
