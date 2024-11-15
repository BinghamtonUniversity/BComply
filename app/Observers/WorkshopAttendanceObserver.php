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
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\RawMessage;


class WorkshopAttendanceObserver
{
    /**
     * Handle the workshop attendance "created" event.
     *
     * @param WorkshopAttendance $attendance
     * @return void
     */
    public function created(WorkshopAttendance $attendance)
    {
      
        $offering = WorkshopOffering::where('id',$attendance->workshop_offering_id)->first();
        $workshop = Workshop::where('id',$attendance->workshop_id)->first();
        // Don't send email if the assignment template is blank
        if ($workshop->config != '') {
          
            $user = User::where('id',$attendance->user_id)->first();
            if($user->active ){
                $user_messages =[
                    'workshop_name'=>$workshop->name,
                    'offering_date' =>$offering->workshop_date,
                    'notification'=> $workshop->config->notification
                ];
               
                try {
                    Mail::to($user)->send(new WorkshopNotification($attendance,$user,$user_messages));
                } catch (\Exception $e) {
                    Log::error('Error sending workshop notification email: '.$e->getMessage());
                }
            }
        }
    }
     /**
     * Handle the workshop attendance "saved" event.
     *
     * @param WorkshopAttendance $attendance
     * @return void
     */
    public function deleted(WorkshopAttendance $attendance)
    {
        $offering = WorkshopOffering::where('id',$attendance->workshop_offering_id)->first();
        $workshop = Workshop::where('id',$attendance->workshop_id)->first();
    
        // Don't send email if the assignment template is blank
        if ($workshop->config != '') {
        
            $user = User::where('id',$attendance->user_id)->first();

            if($user->active ){
                $user_messages =[
                    'workshop_name'=>$workshop->name,
                    'offering_date' =>$offering->workshop_date,
                    'notification'=> $workshop->config->unregister
                ];
            
                try {
                    Mail::to($user)->send(new WorkshopNotification($attendance,$user,$user_messages));
                } catch (\Exception $e) {
                    Log::error('Error sending workshop notification email: '.$e->getMessage());
                }
            }
        }
    }

    /**
     * Handle the module assignment "updated" event.
     *
     * @param WorkshopAttendance $attendance
     * @return void
     */
    public function updated(WorkshopAttendance $attendance){

        //when user status changes to completed
        if($attendance->status == 'completed'){
            $offering = WorkshopOffering::where('id',$attendance->workshop_offering_id)->first();
            $workshop = Workshop::where('id',$attendance->workshop_id)->first();
        
            // Don't send email if the assignment template is blank
            if ($workshop->config != '') {
            
                $user = User::where('id',$attendance->user_id)->first();
                if($user->active ){
                    $user_messages =[
                        'workshop_name'=>$workshop->name,
                        'offering_date' =>$offering->workshop_date,
                        'notification'=> $workshop->config->completion
                    ];
                
                    try {
                        Mail::to($user)->send(new WorkshopNotification($attendance,$user,$user_messages));
                    } catch (\Exception $e) {
                        Log::error('Error sending workshop notification email: '.$e->getMessage());
                    }
                }
            }
        }
    }
}
