<?php

namespace App\Observers;

use App\BulkAssignment;
use App\Libraries\QueryBuilder;
use App\Module;
use App\ModuleAssignment;
use App\User;
use Carbon\Carbon;
use http\Exception\BadQueryStringException;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    /**
     * Handle the user "saved" event.
     *
     * @param User $user
     * @return void
     */

    public function saved(User $user)
    {
        $bulk_assignments =  BulkAssignment::whereJsonContains('assignment',['auto'=>true])->get();

        foreach($bulk_assignments as $bulk_assignment) {
            if($bulk_assignment->assignment->auto){
                if (($bulk_assignment->assignment->later_date && $bulk_assignment->assignment->later_assignment_date <= Carbon::today())
                    || (!$bulk_assignment->assignment->later_date)){
                    $module = Module::where('id',$bulk_assignment->assignment->module_id)->whereNotNull('module_version_id')->first();
//                    dd($module);
                    $q = BulkAssignment::base_query();

                    $q->where('users.id',$user->id)->where('users.active',true);
                    QueryBuilder::build_where($q, $bulk_assignment->assignment);
                    $user_result = $q->select('users.id')->first();

                    if (!is_null($user_result)&&!is_null($module)) {
                        if ($bulk_assignment->assignment->date_due_format === 'relative') {
                            $date_due = Carbon::now()->addDays($bulk_assignment->assignment->days_from_now);
                        } else {
                            $date_due = $bulk_assignment->assignment->date_due;
                        }
                        $module->assign_to([
                            'user_id' => $user->id,
                            'date_due' => $date_due,
                            'assigned_by_user_id'=>0
                        ]);
                    }
                }
            }
        }

    }
}
