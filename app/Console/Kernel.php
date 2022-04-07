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
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        //Workshop Reminder Scheduler



        // Assignment Reminder Scheduler
        $schedule->call(function(){
            $modules = Module::all();
            $module_assignments = ModuleAssignment::whereNull('date_completed')->with('user')->get();

            //Checks all of the assignment due dates to send emails to users
            foreach($module_assignments as $assignment){
                try{
                    $module = $modules->where('id', $assignment->module_id)->first();
                    $user = $assignment->user()->where('id', $assignment->user_id)->first();
                    if ($user->active && $user->send_email_check()) {
                        if (isset($assignment->date_due) && !is_null($assignment->date_due)) {
                            $differenceInDays = $assignment->date_due->diffInDays(Carbon::now());
                            $user_message = null;
                            if ($assignment->date_due <= Carbon::now() && $module->past_due && in_array($differenceInDays, $module->past_due_reminders) && $module->templates->past_due_reminder != '') {
                                $user_message = [
                                    'module_name' => $module['name'],
                                    'link' => $assignment['id'],
                                    'reminder' => $module->templates->past_due_reminder
                                ];
                            } else if ($assignment->date_due > Carbon::now() && in_array($differenceInDays, $module->reminders) && $module->templates->reminder != '') {
                                $user_message = [
                                    'module_name' => $module['name'],
                                    'link' => $assignment['id'],
                                    'reminder' => $module->templates->reminder
                                ];
                            }
                            if (!is_null($user_message)) {
                                Mail::to($user)->send(new AssignmentReminder($assignment, $user, $user_message));
                            }
                        }
                    }
                }catch(\Exception $exception){
                    //Keep going
                }
            }
        })->name('assignment_reminder_task')->dailyAt(config('app.assignment_reminder_task'))->timezone('America/New_York')->onOneServer();

        //Bulk Assignment Scheduler
        $schedule->call(function(){
            $bulkAssignments = BulkAssignment::whereJsonContains('assignment',['later_date'=>true])->get();
            if(!is_null($bulkAssignments)){
                foreach($bulkAssignments as $bulkAssignment){
                    try{
                        if (Carbon::parse($bulkAssignment->assignment->later_assignment_date)->isToday()) {
                            $module = Module::where('id', $bulkAssignment->assignment->module_id)->with('current_version')->first();
                            if (is_null($module) || is_null($module->module_version_id)) {
                                // Do Nothing
                                continue;
                            }
                            $q = BulkAssignment::base_query();
                            QueryBuilder::build_where($q, $bulkAssignment->assignment);
                            $users = $q->select('users.id', 'unique_id', 'first_name', 'last_name')->get();

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
                    }catch(\Exception $e){
                        //Keep going
                    }
                }
            }
        })->name('bulk_assignment_scheduler')->dailyAt(config('app.bulk_assignment_scheduler'))->timezone('America/New_York')->onOneServer();
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
