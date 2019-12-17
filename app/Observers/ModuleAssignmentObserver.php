<?php

namespace App\Observers;

use App\Mail\AssignmentNotification;
use App\Mail\CompletionNotification;
use App\Module;
use App\ModuleAssignment;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


class ModuleAssignmentObserver
{
    /**
     * Handle the module assignment "created" event.
     *
     * @param  \App\ModuleAssignment  $moduleAssignment
     * @return void
     */
    public function created(ModuleAssignment $moduleAssignment)
    {
        $module = Module::where('id','=',$moduleAssignment->module_id)->first();
        $user = User::where('id',$moduleAssignment['user_id'])->first();
        if($moduleAssignment->user_id !== $moduleAssignment->assigned_by){
            if($user->active){
                $user_messages =[
                    'module_name'=> $module['name'],
                    'link' => $moduleAssignment['id'],
                ];
                Mail::to($user)->send(new AssignmentNotification($moduleAssignment,$user,$user_messages));
            }
        }
    }
    public function saved(ModuleAssignment $moduleAssignment){
       if($moduleAssignment->isDirty('date_completed')){
        $module = Module::where('id','=',$moduleAssignment->module_id)->first();
        $user = User::where('id',$moduleAssignment['user_id'])->first();
        if($user->active){
            $user_messages =[
                'module_name'=> $module['name']
//                    'certificate' => $moduleAssignment['certificate'],
            ];
            Mail::to($user)->send(new CompletionNotification($moduleAssignment,$user,$user_messages));
        }
        }
    }
}
