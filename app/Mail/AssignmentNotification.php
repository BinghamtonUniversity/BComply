<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\ModuleAssignment;

class AssignmentNotification extends Mailable
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
        $due_date = isset($moduleAssignment->date_due)?$moduleAssignment->date_due->format('m/d/y'):'N/A';
        $m = new \Mustache_Engine;
        $this->content = $m->render($user_message['assignment'],[
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
        ]);
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
        return $this->view('emails.rawData')
            ->with(['content'=>$this->content])
            ->subject('Binghamton University BComply Training Module Assignment');

    }
}
