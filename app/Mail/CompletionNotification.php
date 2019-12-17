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
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.completion_notification')->with([
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'user_message'=>$this->user_message['module_name']
//                'link'=>$this->user_message['link']
            ]
        )->subject('You have successfully completed the course'.$this->user_message['module_name']);
    }
}
