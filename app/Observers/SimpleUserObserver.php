<?php

namespace App\Observers;

use App\BulkAssignment;
use App\Libraries\QueryBuilder;
use App\Module;
use App\ModuleAssignment;
use App\SimpleUser;
use Carbon\Carbon;
use http\Exception\BadQueryStringException;
use Illuminate\Support\Facades\Mail;

class SimpleUserObserver
{
    public function saved(SimpleUser $user)
    {
        $bulk_assignments = BulkAssignment::all();
        foreach($bulk_assignments as $bulk_assignment) {
            if($bulk_assignment->assignment->auto){
                $module = Module::where('id',$bulk_assignment->assignment->module_id)->first();
                $q = BulkAssignment::base_query();
                $q->where('users.id',$user->id);
                QueryBuilder::build_where($q, $bulk_assignment->assignment);
                $user_result = $q->select('users.id')->first();

                if (!is_null($user_result)) {
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
