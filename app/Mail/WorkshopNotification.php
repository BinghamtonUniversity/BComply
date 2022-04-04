<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Workshop;
use App\WorkshopOffering;
use App\WorkshopAttendance;

class WorkshopNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param ModuleAssignment $moduleAssignment
     * @param User $user
     * @param array $user_message
     */
    public function __construct(WorkshopAttendance $attendance, User $user, Array $user_message)
    {
        $m = new \Mustache_Engine;
        $this->content = $m->render($user_message['notification'],[
            'user'=>[
                'first_name'=> $user->first_name,
                'last_name'=>$user->last_name,
            ],
            'workshop'=>[
                'name'=>$user_message['workshop']->name,
                'workshop_date'=>$user_message['offering']->workshop_date,
            ],
            'link'=>url('/workshops/'.$user_message['workshop']->id.'/offerings'.$user_message['offering']->id)
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
            ->subject('Binghamton University BComply Training Workshop');

    }
}
