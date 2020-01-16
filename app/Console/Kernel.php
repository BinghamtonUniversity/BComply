<?php

namespace App\Console;

use App\BulkAssignment;
use App\Libraries\QueryBuilder;
use App\Mail\AssignmentReminder;
use App\Module;
use App\ModuleAssignment;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use function foo\func;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Assignment Reminder Scheduler
        $schedule->call(function(){
            $modules = Module::all();
            $module_assignments = ModuleAssignment::whereNull('date_completed')->with('user')->get();
            //Checks all of the assignment due dates to send emails to users
            foreach($module_assignments as $assignment){
                $module = $modules->where('id',$assignment->module_id)->first();
                $user = $assignment->user()->where('id',$assignment->user_id)->first();
                if($user->active && $user->send_email_check()){
                    $differenceInDays = $assignment->date_due->diffInDays(Carbon::now());
                    if($differenceInDays>0){
                        if($module->past_due){
                            if(in_array($differenceInDays,$module->reminders) || in_array($differenceInDays,$module->past_due_reminders) ){
                                $user_messages =[
                                    'module_name'=> $module['name'],
                                    'link' => $assignment['id'],
                                    'hours'=> 0
                                ];
                                Mail::to($user)->send(new AssignmentReminder($assignment,$user,$user_messages));
                            }
                        }else{
                            if(($assignment->date_due>Carbon::now()) && in_array($differenceInDays,$module->reminders)){
                                $user_messages =[
                                    'module_name'=> $module['name'],
                                    'link' => $assignment['id'],
                                    'hours'=> 0
                                ];
                                Mail::to($user)->send(new AssignmentReminder($assignment,$user,$user_messages));
                            }
                        }
                    }
                    else{
                        if($assignment->date_due > Carbon::now()){
                            $user_messages =[
                                'module_name'=> $module['name'],
                                'link' => $assignment['id'],
                                'hours'=> 'Today'
                            ];
                            Mail::to($user)->send(new AssignmentReminder($assignment,$user,$user_messages));
                        }
                    }
                }
            }
        })->dailyAt(config('app.daily_task'))->timezone('America/New_York');
        //Bulk Assignment Scheduler
        $schedule->call(function(){
            $bulkAssignments = BulkAssignment::whereJsonContains('assignment',['later_date'=>true])->get();
            if(!is_null($bulkAssignments)){
                foreach($bulkAssignments as $bulkAssignment){
                    if(Carbon::parse($bulkAssignment->assignment->later_assignment_date)->isToday()){
                        $module = Module::where('id',$bulkAssignment->assignment->module_id)->with('current_version')->first();
                        if (is_null($module->module_version_id)) {
                            // Do Nothing
                            continue;
                        }
                        $q = BulkAssignment::base_query();
                        QueryBuilder::build_where($q, $bulkAssignment->assignment);
                        $users = $q->select('users.id','unique_id','first_name','last_name')->get();

                        foreach ($users as $user) {
                            if ($bulkAssignment->assignment->date_due_format === 'relative') {
                                $date_due = Carbon::now()->addDays($bulkAssignment->assignment->days_from_now);
                            } else {
                                $date_due = $bulkAssignment->assignment->date_due;
                            }
                            $module->assign_to([
                                'user_id' => $user->id,
                                'date_due' => $date_due,
                                'assigned_by_user_id' => 0,
                            ]);
                        }
                    }

                }
            }
        })->dailyAt(config('app.bulk_assignment_scheduler'))->timezone('America/New_York');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
