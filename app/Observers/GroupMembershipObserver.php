<?php

namespace App\Observers;

use App\BulkAssignment;
use App\GroupMembership;
use App\Libraries\QueryBuilder;
use App\Module;
use Carbon\Carbon;

class GroupMembershipObserver
{
    /**
     * Handle the group membership "created" event.
     *
     * @param GroupMembership $groupMembership
     * @return void
     */
    public function saved(GroupMembership $groupMembership){
        $bulk_assignments = BulkAssignment::whereJsonContains('assignment',['auto'=>true])->get();

        foreach($bulk_assignments as $bulk_assignment) {
            if(isset($bulk_assignment->assignment) && isset($bulk_assignment->assignment->auto) && $bulk_assignment->assignment->auto){
                if (($bulk_assignment->assignment->later_date && $bulk_assignment->assignment->later_assignment_date <= Carbon::today())
                    || (!$bulk_assignment->assignment->later_date)){
                    $module = Module::where('id', $bulk_assignment->assignment->module_id)->whereNotNull('module_version_id')->first();

                    $q = BulkAssignment::base_query();

                    $q->where('users.id', $groupMembership->user_id)->where('users.active', true);

                    QueryBuilder::build_where($q, $bulk_assignment->assignment);
                    $user_result = $q->select('users.id')->first();

                    if (!is_null($user_result)&&!is_null($module)) {
                        if ($bulk_assignment->assignment->date_due_format === 'relative') {
                            $date_due = Carbon::now()->addDays($bulk_assignment->assignment->days_from_now);
                        } else {
                            $date_due = $bulk_assignment->assignment->date_due;
                        }
                        $module->assign_to([
                            'user_id' => $groupMembership->user_id,
                            'date_due' => $date_due,
                            'assigned_by_user_id' => 0
                        ]);
                    }
                }
            }
        }

    }
//    /**
//     * Handle the group membership "updated" event.
//     *
//     * @param  \App\GroupMembership  $groupMembership
//     * @return void
//     */
//    public function updated(GroupMembership $groupMembership)
//    {
//        //
//    }
//
//    /**
//     * Handle the group membership "deleted" event.
//     *
//     * @param  \App\GroupMembership  $groupMembership
//     * @return void
//     */
//    public function deleted(GroupMembership $groupMembership)
//    {
//        //
//    }
//
//    /**
//     * Handle the group membership "restored" event.
//     *
//     * @param  \App\GroupMembership  $groupMembership
//     * @return void
//     */
//    public function restored(GroupMembership $groupMembership)
//    {
//        //
//    }
//
//    /**
//     * Handle the group membership "force deleted" event.
//     *
//     * @param  \App\GroupMembership  $groupMembership
//     * @return void
//     */
//    public function forceDeleted(GroupMembership $groupMembership)
//    {
//        //
//    }
}
