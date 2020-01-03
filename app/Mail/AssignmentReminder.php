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
        if($this->moduleAssignment->due_date > Carbon::now()){
            return $this->view('emails.reminder')->with([
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'user_message'=>$this->user_message['module_name'],
                    'due_date'=>$this->moduleAssignment->date_due->format('m/d/y'),
                    'link'=>$this->user_message['link'],
                    'hours'=>$this->user_message['hours']
                ]
            )->subject('Assignment Reminder: '.$this->user_message['module_name'].' Due Date:'.$this->moduleAssignment->date_due->format('m/d/y'));
        }
        else{
            return $this->view('emails.reminder')->with([
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'user_message'=>$this->user_message['module_name'],
                    'due_date'=>$this->moduleAssignment->date_due->format('m/d/y'),
                    'link'=>$this->user_message['link'],
                    'hours'=>-1
                ]
            )->subject('Assignment Reminder: '.$this->user_message['module_name'].' Overdue Date:'.$this->moduleAssignment->date_due->format('m/d/y'));
        }
    }
}
