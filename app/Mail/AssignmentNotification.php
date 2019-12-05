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
     * @return void
     */
    public function __construct(ModuleAssignment $moduleAssignment, User $user, Array $user_message)
    {
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
        return $this->view('emails.assignment')->with([
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'user_message'=>$this->user_message['module_name'],
                'due_date'=>$this->moduleAssignment->date_due,
                'link'=>$this->user_message['link']
            ]
        )->subject('New Course Assignment: '.$this->user_message['module_name'].' Due Date:'.$this->moduleAssignment->date_due->toDateString());
    }
}
