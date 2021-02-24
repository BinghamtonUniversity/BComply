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
            if (is_null($assignment->module->icon) || $assignment->module->icon=='') {$assignments[$index]->module->icon='book-open';}
        }
        return view('my_assignments',['page'=>'my_assignments','assignments'=>$assignments,'user'=>Auth::user()]);
    }

    public function assignment_history(Request $request){
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->whereNotNull('date_completed')
            ->where('status','!=','incomplete')
            ->with('version')
            ->with('module')
            ->orderBy('date_completed','desc')->get();
        foreach($assignments as $index => $assignment) {
            if (is_null($assignment->module->icon) || $assignment->module->icon=='') {$assignments[$index]->module->icon='book-open';}
        }    
        return view('history',['page'=>'history','assignments'=>$assignments,'user'=>Auth::user()]);
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

    public function module_redirect(Request $request, Module $module){
        $assignment = $module->assign_to([
            'user_id'=>Auth::user()->id,
            'date_due'=>Carbon::now()->addDays(1), // Make due date the next day!
            'assigned_by_user_id'=>Auth::user()->id
        ]);
        if ($assignment === false) {
            return abort(404,'The specified module does not have a current version');
        } else if (is_null($assignment)) {
            // The user has already been assigned this module, just redict them to their current assignment.
            $assignment = ModuleAssignment::where('user_id',Auth::user()->id)
                ->where('module_id',$module->id)
                ->where('module_version_id',$module->module_version_id)
                ->first();
            return redirect('/assignment/'.$assignment->id);
        } else {
            return redirect('/assignment/'.$assignment->id);
        }
    }

}
