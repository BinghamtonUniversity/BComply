<?php

namespace App\Observers;

use App\Mail\AssignmentNotification;
use App\Mail\CompletionNotification;
use App\Mail\SendTemplates;
use App\Module;
use App\ModuleAssignment;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\RawMessage;
use Throwable;

class ModuleAssignmentObserver
{
    /**
     * Handle the module assignment "created" event.
     *
     * @param ModuleAssignment $moduleAssignment
     * @return void
     */
    public function created(ModuleAssignment $moduleAssignment)
    {
        $module = Module::where('id','=',$moduleAssignment->module_id)->first();
        // Don't send email if the assignment template is blank
        if ($module->templates->assignment != '') {
            $user = User::where('id',$moduleAssignment->user_id)->first();
            if($user->active && $user->send_email_check() && !is_null($user->email)){
                $user_messages =[
                    'module_name'=> $module['name'],
                    'link' => $moduleAssignment['id'],
                    'assignment'=>$module->templates->assignment
                ];
                try {
                    Mail::to($user)->send(new AssignmentNotification($moduleAssignment,$user,$user_messages));
                } catch (\Exception $e) {
                    Log::error('Error sending assignment email: '.$e->getMessage());
                }
            }
        }
    }

    /**
     * Handle the module assignment "saved" event.
     *
     * @param ModuleAssignment $moduleAssignment
     * @return void
     */
    public function saved(ModuleAssignment $moduleAssignment){
        if($moduleAssignment->isDirty('date_completed') && $moduleAssignment->status !=='incomplete'){
            $module = Module::where('id','=',$moduleAssignment->module_id)->first();
            // Don't send email if the completion template is blank
            if ($module->templates->completion_notification != '') {
                $user = User::where('id',$moduleAssignment['user_id'])->first();
                if($user->active && $user->send_email_check()){
                    $user_messages =[
                        'module_name'=> $module->name,
                        'link' => $moduleAssignment['id'],
                        'completion_notification'=>$module->templates->completion_notification
                    ];
                    try {
                        Mail::to($user)->send(new CompletionNotification($moduleAssignment,$user,$user_messages));
                    } catch (\Exception $e) {
                        Log::error('Error sending completion email: '.$e->getMessage());
                    }
                }
            }
        }
    }
}
