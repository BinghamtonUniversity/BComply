<?php

namespace App\Mail;

use App\ModuleAssignment;
use App\User;
use App\SimpleUser;
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
    public function __construct(ModuleAssignment $moduleAssignment, SimpleUser $user, Array $user_message)
    {
        $due_date = isset($moduleAssignment->date_due)?$moduleAssignment->date_due->format('m/d/y'):'N/A';
        $email_data = [
            'user'=>[
                'first_name'=> $user->first_name,
                'last_name'=>$user->last_name,
            ],
            'module'=>[
                'name'=>$user_message['module_name'],
                'due_date'=>$due_date,
                'assignment_date'=>$moduleAssignment->date_assigned->format('m/d/y')
            ],
            'link'=>url('/assignment/'.$user_message['link'])
        ];
        for($days=1;$days<=60;$days++) {
            $email_data['module']['assignment_date_plus_'.$days]= floor($moduleAssignment->date_assigned->addDays($days));
        }
        $m = new \Mustache_Engine;
        $this->content = $m->render($user_message['reminder'],$email_data);
        $this->moduleAssignment = $moduleAssignment;
        $this->user = $user;
        $this->user_message = $user_message;
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
