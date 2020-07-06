<?php

namespace App\Mail;

use App\ModuleAssignment;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssignmentReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param ModuleAssignment $moduleAssignment
     * @param User $user
     * @param array $user_message
     */
    public function __construct(ModuleAssignment $moduleAssignment, User $user, Array $user_message)
    {
        $m = new \Mustache_Engine;
        $this->content = $m->render($user_message['reminder'],[
            'user'=>[
                'first_name'=> $user->first_name,
                'last_name'=>$user->last_name,
            ],
            'module'=>[
                'name'=>$user_message['module_name'],
                'due_date'=>$moduleAssignment->date_due->format('m/d/y'),
                'assignment_date'=>$moduleAssignment->date_assigned->format('m/d/y')
            ],
            'link'=>url('/assignment/'.$user_message['link'])
        ]);
        $this->moduleAssignment = $moduleAssignment;
        $this->user = $user;
        $this->user_message = $user_message;
//
//        $this->moduleAssignment = $moduleAssignment;
//        $this->user = $user;
//        $this->user_message = $user_message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->moduleAssignment->date_due > Carbon::now()){
            $this->view('emails.rawData')
                ->with(['content'=>$this->content])
                ->subject('BComply "'.$this->user_message['module_name'].'" Training Module Reminder');
        }
        else{
            $this->view('emails.rawData')
                ->with(['content'=>$this->content])
                ->subject('BComply "'.$this->user_message['module_name'].'" Training Module PAST DUE');
        }
    }
}
