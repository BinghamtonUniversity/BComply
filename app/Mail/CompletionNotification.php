<?php

namespace App\Mail;

use App\ModuleAssignment;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompletionNotification extends Mailable
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
        $this->content = $m->render($user_message['completion_notification'],
            [
                'user'=>[
                    'first_name'=> $user->first_name,
                    'last_name'=>$user->last_name,
                    ],
                'module'=>[
                    'name'=>$user_message['module_name'],
                    'due_date'=>$moduleAssignment->date_due->format('m/d/y')
                ],
                'link'=>url('/assignment/'.$user_message['link'].'/certificate')
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
        if(($this->moduleAssignment->status==='completed') || ($this->moduleAssignment->status==='passed')|| ($this->moduleAssignment->status==='attended') ){
            $subject_message = 'You have successfully completed the course'.$this->user_message['module_name'];
        }
        else if($this->moduleAssignment->status==='failed'){
            $subject_message = 'You have completed the course'.$this->user_message['module_name'];
        }
        return $this->view('emails.rawData')
            ->with(['content'=>$this->content])
            ->subject('New Course Assignment: '.
                $this->user_message['module_name'].' Due Date:'.
                $this->moduleAssignment->date_due
                    ->format('m/d/y'))->subject($subject_message);
    }
}
