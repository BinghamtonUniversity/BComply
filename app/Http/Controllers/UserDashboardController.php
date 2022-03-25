<?php

namespace App\Http\Controllers;

use App\Module;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\ModuleAssignment;
use App\Workshop;
use App\WorkshopOffering;
use App\WorkshopAttendance;
//todo new librarys
use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Domain\ValueObject\Date;
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
            ->where('date_assigned','<=',now())->orderBy('date_assigned','desc')
            ->whereNull('date_completed')
            ->with('version')
            ->with('module')->get();
        foreach($assignments as $index => $assignment) {
            if (is_null($assignment->module->icon) || $assignment->module->icon=='') {$assignments[$index]->module->icon='book-open';}
        }
        return view('my_assignments',['page'=>'my_assignments','assignments'=>$assignments,'user'=>Auth::user()]);
    }

    public function my_workshops(Request $request) {
        $workshop_attendances = WorkshopAttendance::where('user_id',Auth::user()->id)->get();
        $workshops = array();
        foreach($workshop_attendances as $index => $workshop_attendance) {
            
            $tmps = Workshop::where('id',$workshop_attendance->workshop_id)->get();
            foreach($tmps as $index => $tmp) {
                if (is_null($tmp->icon) || $tmp->icon=='') {$tmps[$index]->icon='book-open';}
                array_push($workshops, $tmp);        
            }
              
        }

        return view('my_workshops',['page'=>'my_workshops','workshops'=>$workshops,'user'=>Auth::user()]);
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
    public function create_calendar(Request $request){
        $events = array();
        $workshop_offerings =WorkshopOffering::select('id','workshop_id','instructor_id','max_capacity','locations','workshop_date','type')
        ->with(['instructor'=>function($query){
            $query->select('id','first_name','last_name');
        }])->get();
        foreach($workshop_offerings as $index => $workshop_offering){


            $workshop = Workshop::where('id',$workshop_offering->workshop_id)->with('owner')->get();
         
            $organizer = new Organizer(
                new EmailAddress('test@example.org'),
                $workshop[0]->owner_id,
                new Uri('ldap://example.com:6666/o=ABC%20Industries,c=US???(cn=Jim%20Dolittle)'),
                new EmailAddress('sender@example.com')
            );
            $location = new Location($workshop_offering->locations);
            $event = (new Event())
            ->setSummary($workshop[0]->name)
            ->setDescription($workshop_offering->locations)
            ->setOrganizer($organizer)
            ->setLocation($location)
            ->setOccurrence(new SingleDay(new Date()));
            
             array_push($events,$event);
        }
        $calendar = new Calendar($events);
        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);
        echo $calendarComponent;
       dd($calendarComponent);
        return view('calendar',['page'=>'calendar','events'=>$events,'user'=>Auth::user()]);
    }
    
}
