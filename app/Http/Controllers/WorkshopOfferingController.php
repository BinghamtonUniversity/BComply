<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\User;
use App\Workshop;
use App\WorkshopAttendance;
use App\WorkshopOffering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkshopOfferingController extends Controller
{
    public function run(Request $request,String $workshop_id,String $offering_id){
     
        $workshop_offering = WorkshopOffering::where('id',$offering_id)
          //  ->where('user_id',Auth::user()->id)
            ->with('workshop')
            ->with('instructor')
            ->first();

        $workshop = Workshop::where('id',$workshop_id)
        //  ->where('user_id',Auth::user()->id)
          ->first();
        $workshop_attendance = WorkshopAttendance::Where('workshop_id',$workshop_id)
         ->where('user_id',Auth::user()->id)
         ->where('workshop_offering_id',$offering_id)
          ->first();  
        $seats_remaining = $workshop_offering->max_capacity  -$workshop_offering->workshop_attendance->count();
        $is_past = false;
      // dd('Time:   '.time().'   ----Workshop date : '.strtotime($workshop_offering->workshop_date));
        if(time() >= strtotime($workshop_offering->workshop_date)){
        
                $is_past= true;
        }   
            
        return view('offering',[
            'user'=>Auth::user(),
            'offering' => $workshop_offering,
            'workshop'=>$workshop,
            'attendance' =>$workshop_attendance,
            'seats_remaining'=>$seats_remaining,
            'is_past'=>$is_past,
            'page'=>"offering"
        ]);
    }
    public function assign(Request $request,Workshop $workshop,WorkshopOffering $offering){
       
         $attendance = new WorkshopAttendance();
         $attendance->attendance = 'registered';
         $attendance->user_id = Auth::user()->id;
         $attendance->workshop_id =$workshop->id;
         $attendance->workshop_offering_id =$offering->id;
         $attendance->save();
         return redirect('/workshops/'.$workshop->id.'/offerings/'.$offering->id);
        //  return view('offering',[
        //     'user'=>Auth::user(),
        //     'offering' => $offering,
        //     'workshop'=>$workshop,
        //     'attendance' =>$attendance,
        //     'page'=>"offering"
        // ]);
        
    }
    public function cancelRegistration(Request $request,Workshop $workshop,WorkshopOffering $offering){
 
        $attendance= WorkshopAttendance::Where('workshop_id',$workshop->id)
        ->where('user_id',Auth::user()->id)
        ->where('workshop_offering_id',$offering->id)->first();


         $attendance->delete(); 
        
        return redirect('/workshops/'.$workshop->id.'/offerings/'.$offering->id);
       //  return view('offering',[
       //     'user'=>Auth::user(),
       //     'offering' => $offering,
       //     'workshop'=>$workshop,
       //     'attendance' =>$attendance,
       //     'page'=>"offering"
       // ]);
       
   }    

    public function get_workshop_attendances(Request $request,WorkshopOffering $offering){
       
        return WorkshopAttendance::where('workshop_offering_id',$offering->id)
            ->select('id','workshop_id','workshop_offering_id','user_id','status')
            ->with(['attendee'=>function($query){
                $query->select('id','first_name','last_name');
            }])->get();
    }
}
