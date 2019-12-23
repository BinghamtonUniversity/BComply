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
     * @return void
     */
    public function __construct(ModuleAssignment $moduleAssignment, User $user, Array $user_message)
    {
        $this->moduleAssignment = $moduleAssignment;
        $this->user = $user;
        $this->user_message = $user_message;
        $this->module_name = $user_message['module_name'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if(($this->moduleAssignment->status==='completed') || ($this->moduleAssignment->status==='passed')|| ($this->moduleAssignment->status==='attended') ){
            $subject_message = 'You have successfully completed the course'.$this->module_name;
        }
        else if($this->moduleAssignment->status==='failed'){
            $subject_message = 'You have completed the course'.$this->module_name;
        }
        return $this->view('emails.completion_notification')->with([
                'assignment'=>$this->moduleAssignment,
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'user_message'=>$this->module_name
            ]
        )->subject($subject_message);
    }
}
