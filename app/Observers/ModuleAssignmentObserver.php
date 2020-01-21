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
        $user = User::where('id',$moduleAssignment->user_id)->first();
        $default_assignment_email_text = "
                        <h3> Hello {{user.first_name}} {{user.last_name}}</h3>
                        <br>
                        <p style='font-size:16px;'>You are assigned to {{module.name}}</p>
                        <br>
                        <p style='font-size:16px;'>Access to Assignment: 
                            <a href='{{link}}'>{{module.name}}</a>
                        </p>";
        if($moduleAssignment->user_id !== $moduleAssignment->assigned_by){
            if($user->active && $user->send_email_check()){
                $user_messages =[
                    'module_name'=> $module['name'],
                    'link' => $moduleAssignment['id'],
                    'assignment'=>$module->templates->assignment?$module->templates->assignment:$default_assignment_email_text
                ];
                Mail::to($user)->send(new AssignmentNotification($moduleAssignment,$user,$user_messages));
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
        if($moduleAssignment->isDirty('date_completed')){
            $module = Module::where('id','=',$moduleAssignment->module_id)->first();
            $user = User::where('id',$moduleAssignment['user_id'])->first();
            $default_completion_notification_text = "
                            <h3> Hello {{user.first_name}} {{user.last_name}}</h3>
                            <br>
                            <p style='font-size:16px;'>You completed the {{module.name}} course</p>
                            <br>
                            <p style='font-size:16px;'>Certificate Link: 
                                <a href='{{link}}'>Certificate</a>
                            </p>";
            if($user->active && $user->send_email_check()){
                $user_messages =[
                    'module_name'=> $module->name,
                    'link' => $moduleAssignment['id'],
                    'completion_notification'=>$module->templates->completion_notification?$module->templates->completion_notification:$default_completion_notification_text
                ];
                Mail::to($user)->send(new CompletionNotification($moduleAssignment,$user,$user_messages));
            }
        }
    }
}
