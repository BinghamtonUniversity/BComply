<?php

namespace App\Http\Controllers;

use App\Module;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use App\ModuleAssignment;
use App\User;
use App\Workshop;
use App\WorkshopOffering;
use App\WorkshopAttendance;
use DateInterval;
use DateTimeImmutable;
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\DateTime;
use Eluceo\iCal\Domain\ValueObject\TimeSpan;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\Organizer;
use Eluceo\iCal\Domain\ValueObject\Uri;
use Eluceo\iCal\Domain\ValueObject\EmailAddress;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;
class UserDashboardController extends Controller
{
    public function my_assignments(Request $request) {
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->where('date_assigned','<=',now())
            ->whereRaw('DATE_ADD(date_assigned, INTERVAL 365 DAY) >= NOW()')
            ->whereNull('date_completed')
            ->with('version')
            ->with('module')
            ->orderBy('date_assigned','desc')
            ->get();
        foreach($assignments as $index => $assignment) {
            if (is_null($assignment->module->icon) || $assignment->module->icon=='') {$assignments[$index]->module->icon='book-open';}
        }
        return view('my_assignments',['page'=>'my_assignments','assignments'=>$assignments,'user'=>Auth::user()]);
    }

    public function my_workshops(Request $request) {
        $workshop_attendances = WorkshopAttendance::where('user_id',Auth::user()->id)->get();
        $attendances = array();
        foreach($workshop_attendances as $index => $workshop_attendance) {
            $minutes_to_add =  $workshop_attendance->workshop->duration;
            $s =$workshop_attendance->workshop_offering->workshop_date;
           
           $date = date('Y-m-d H:i:s', strtotime( $s. '+'.$minutes_to_add.' minutes'));       
            if((time()) <= strtotime($date)){
                array_push($attendances, $workshop_attendance); 
            }              
        }

        return view('my_workshops',['page'=>'my_workshops','attendances'=>$attendances,'user'=>Auth::user()]);
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
        $attendances = array(); 
        $attendances_db = WorkshopAttendance::where('user_id',Auth::user()->id)->with('workshop')->with('workshop_offering')->get();
        foreach($attendances_db as $index => $attendance) {
            $minutes_to_add =  $attendance->workshop->duration;
            $s =$attendance->workshop_offering->workshop_date;
        

           $date = date('Y-m-d H:i:s', strtotime( $s. '+'.$minutes_to_add.' minutes'));
            //  dd( "Today:  ".time()-(60*60*24).  "---Old Date: ".$s ."----New Date :". strtotime($date)); 
            //dd(date('Y-m-d H:i:s',time()))   ;     
             if((time()) > strtotime($date)){
               
                    array_push($attendances, $attendance);        
                
            }    
        }
        return view('history',['page'=>'history','assignments'=>$assignments,'attendances'=>$attendances,'user'=>Auth::user()]);
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

    public function impersonate(Request $request,$pass){
        try{
            $result = json_decode(Crypt::decrypt($pass));
            
            if((now()->timestamp - $result->timestamp)/60 <= 10){
                $user = User::where('unique_id',$result->unique_id)->first();
               
                if (!is_null($user)) {
                    Auth::login($user);
                    $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
                        ->where('date_assigned','<=',now())->orderBy('date_assigned','desc')
                        ->whereNull('date_completed')
                        ->with('version')
                        ->with('module')->get();
                    foreach($assignments as $index => $assignment) {
                        if (is_null($assignment->module->icon) || $assignment->module->icon=='') {$assignments[$index]->module->icon='book-open';}
                    }
                    return view('my_assignments',['page'=>'my_assignments','assignments'=>$assignments,'user'=>Auth::user()]);
                } else {
                    return abort(401);
                }
            }
            else{
                return abort(401);
            }
        }catch (\Exception $e){
            return abort(401);
        }


    }
    
}
