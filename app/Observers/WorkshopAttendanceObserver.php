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

            // if($user->active && $user->send_email_check()){
                if($user->active ){
                $user_messages =[
                    'workshop_name'=>$workshop->name,
                    'offering_date' =>$offering->workshop_date,
                    'notification'=> $workshop->config->notification
                ];
               
                try {
                   
                    Mail::to($user)->send(new WorkshopNotification($attendance,$user,$user_messages));
                } catch (\Exception $e) {
                    dd($e);
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
        echo('im here');
        // if($attendance->deleted_at != NULL){
            $offering = WorkshopOffering::where('id',$attendance->workshop_offering_id)->first();
            $workshop = Workshop::where('id',$attendance->workshop_id)->first();
        
            // Don't send email if the assignment template is blank
            if ($workshop->config != '') {
            
                $user = User::where('id',$attendance->user_id)->first();

                // if($user->active && $user->send_email_check()){
                    if($user->active ){
                    $user_messages =[
                        'workshop_name'=>$workshop->name,
                        'offering_date' =>$offering->workshop_date,
                        'notification'=> $workshop->config->unregister
                    ];
                
                    try {
                    
                        Mail::to($user)->send(new WorkshopNotification($attendance,$user,$user_messages));
                    } catch (\Exception $e) {
                        dd($e);
                    }
                }
            }
       // }
        
    }

    // /**
    //  * Handle the module assignment "saved" event.
    //  *
    //  * @param ModuleAssignment $moduleAssignment
    //  * @return void
    //  */
    // public function saved(ModuleAssignment $moduleAssignment){
    //     if($moduleAssignment->isDirty('date_completed') && $moduleAssignment->status !=='incomplete'){
    //         $module = Module::where('id','=',$moduleAssignment->module_id)->first();
    //         // Don't send email if the completion template is blank
    //         if ($module->templates->completion_notification != '') {
    //             $user = User::where('id',$moduleAssignment['user_id'])->first();
    //             if($user->active && $user->send_email_check()){
    //                 $user_messages =[
    //                     'module_name'=> $module->name,
    //                     'link' => $moduleAssignment['id'],
    //                     'completion_notification'=>$module->templates->completion_notification
    //                 ];
    //                 try {
    //                     Mail::to($user)->send(new CompletionNotification($moduleAssignment,$user,$user_messages));
    //                 } catch (\Exception $e) {
    //                     // keep going
    //                 }
    //             }
    //         }
    //     }
    // }
}
