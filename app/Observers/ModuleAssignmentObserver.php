<?php

namespace App\Observers;

use App\Mail\AssignmentNotification;
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

        $user_messages =[
            'module_name'=> $module['name'],
            'link' => $moduleAssignment['id'],
        ];

        Mail::to($user)->send(new AssignmentNotification($moduleAssignment,$user,$user_messages));
    }
}
