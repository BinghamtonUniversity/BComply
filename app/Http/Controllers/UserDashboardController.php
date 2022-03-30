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
            $minutes_to_add =  $workshop_attendance->workshop->duration;
            $s =$workshop_attendance->workshop_offering->workshop_date;
           // $time =  getDate(strtotime($workshop_attendance->workshop_offering->workshop_date));
           $date = strtotime($s);
           $new_date =date('d/M/Y H:i:s', $date);
            dd($new_date);
            $new_date->add(new DateInterval('PT' . $minutes_to_add . 'M'));
          
            if((time()-(60*60*24)) < strtotime($time)){
                dd('im little');
            }
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
        $attendances = WorkshopAttendance::where('user_id',Auth::user()->id)->with('workshop')->with('workshop_offering')->get();
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
    public function create_calendar(Request $request){
        $events = array();
        $workshops = Workshop::where('public',true)->with('owner')->get();
        foreach($workshops as $index => $workshop){


            $workshop_offerings =WorkshopOffering::select('id','workshop_id','instructor_id','max_capacity','locations','workshop_date','type')
            ->where('workshop_id',$workshop->id)
                ->with('instructor')->get();
            foreach($workshop_offerings as $index => $workshop_offering){
                $instructor_name =$workshop_offering->instructor->first_name . ' '.  $workshop_offering->instructor->last_name;
                $description = $workshop->description .' To Sign Up Please click following link: http://bcomplydev.local:8000/workshops/1/offerings/4';
                $organizer = new Organizer(
                    new EmailAddress( $workshop_offering->instructor->email),
                    $instructor_name,
                    new Uri('ldap://example.com:6666/o=ABC%20Industries,c=US???(cn=Jim%20Dolittle)'),
                    new EmailAddress('sender@example.com')
                );
                $location = new Location($workshop_offering->locations);

                $event = (new Event())
                ->setSummary($workshop->name)
                ->setDescription($description)
                ->setOrganizer($organizer)
                ->setLocation($location)
                ->setOccurrence(new SingleDay( new Date(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $workshop_offering->workshop_date), true)));
               
                 array_push($events,$event);
            } 
        }
        $calendar = new Calendar($events);
        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);
    //    dd($calendar);
       return response($calendarComponent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="cal.ics"');
        // return view('calendar',['page'=>'calendar','events'=>$events,'user'=>Auth::user()]);
    }
    
}
