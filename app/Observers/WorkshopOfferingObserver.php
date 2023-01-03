<?php

namespace App\Observers;

use App\Mail\WorkshopNotification;
use App\Mail\CompletionNotification;
use App\Mail\SendTemplates;
use App\Workshop;
use App\WorkshopOffering;
use App\WorkshopAttendance;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\RawMessage;


class WorkshopOfferingObserver
{

     /**
     * Handle the WorkshopOffering  "updated" event.
     *
     * @param WorkshopOffering $offering
     * @return void
     */
    public function updated(WorkshopOffering $offering){

        //when offering status changes to cancelled or reactive
        
      if($offering->status == 'cancelled' || $offering->status == 'reactive'){
       
        $workshop = Workshop::where('id',$offering->workshop_id)->first();
    
        // Don't send email if the assignment template is blank
        if ($workshop->config != '') {
        
            $attendances = WorkshopAttendance::where("workshop_offering_id",$offering->id)->with('attendee')->get();

            foreach( $attendances as $attendance){
                    $user = $attendance->attendee;
                 // if($user->active && $user->send_email_check()){
                    if($user->active ){
                        $user_messages =[
                            'workshop_name'=>$workshop->name,
                            'offering_date' =>$offering->workshop_date,
                            'notification'=> $workshop->config->update,
                            'status'=> $offering->status
                        ];
                    
                        try {
                        
                            Mail::to($user)->send(new WorkshopNotification($attendance,$user,$user_messages));
                        } catch (\Exception $e) {
                            dd($e);
                        }
                    }
            }
           
        }
    }
 
}
}