<?php

namespace App\Observers;

use App\BulkAssignment;
use App\GroupMembership;
use App\Libraries\QueryBuilder;
use App\Module;

class GroupMembershipObserver
{
    /**
     * Handle the group membership "created" event.
     *
     * @param  \App\GroupMembership  $groupMembership
     * @return void
     */
//    public function created(GroupMembership $groupMembership)
//    {
//        //
//    }
    public function saved(GroupMembership $groupMembership){
        $bulk_assignments = BulkAssignment::all();

        foreach($bulk_assignments as $bulk_assignment) {
            if($bulk_assignment->assignment->auto){
                $module = Module::where('id',$bulk_assignment->assignment->module_id)->first();
                $q = BulkAssignment::base_query();
                $q->where('users.id',$groupMembership->user_id);
                QueryBuilder::build_where($q, $bulk_assignment->assignment);
                $user_result = $q->select('users.id')->first();

                if (!is_null($user_result)) {
                    $module->assign_to([
                        'user_id' => $groupMembership->user_id,
                        'date_due' => $bulk_assignment->date_due,
                        'assigned_by_user_id'=>0
                    ]);
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
