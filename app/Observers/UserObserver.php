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
     * Handle the user "created" event.
     *
     * @param  \App\User  $user
     * @return void
     */

    public function saved(User $user)
    {
        $bulk_assignments = BulkAssignment::all();

        foreach($bulk_assignments as $bulk_assignment) {
            $module = Module::where('id',$bulk_assignment->assignment->module_id)->first();
            $q = BulkAssignment::base_query();
            $q->where('users.id',$user->id);
            QueryBuilder::build_where($q, $bulk_assignment->assignment);
            $user_result = $q->select('users.id')->first();

            if (!is_null($user_result)) {
                $module_assignments = ModuleAssignment::where('module_id',$bulk_assignment->assignment->module_id)
                    ->where('user_id',$user->id)
                    ->whereNull('date_completed')->get();
                try{
                    if (is_null($module_assignments->firstWhere('user_id',$user->id))) {
                        // Assign user $user to module $module (version $module->module_version_id)
                        $module_assignment = new ModuleAssignment([
                            'user_id'=>$user->id,
                            'module_version_id'=>$module->module_version_id,
                            'module_id'=>$module->id,
                            'date_assigned' => Carbon::now(),
                            'date_due' => $bulk_assignment->assignment->date_due
                        ]);
                        $module_assignment->save();
                    }
                }
                catch (Exception $e){
                    dd(e);
                    continue;
                }

            }
        }
    }
//    /**
//     * Handle the user "updated" event.
//     *
//     * @param  \App\User  $user
//     * @return void
//     */
//    public function updated(User $user)
//    {
//        dd("User updated");
//
//    }
//
//    /**
//     * Handle the user "deleted" event.
//     *
//     * @param  \App\User  $user
//     * @return void
//     */
    public function deleted(User $user)
    {
        dd("User deleted");
    }
//
//    /**
//     * Handle the user "restored" event.
//     *
//     * @param  \App\User  $user
//     * @return void
//     */
//    public function restored(User $user)
//    {
//        dd("User restored");
//
//    }
//
//    /**
//     * Handle the user "force deleted" event.
//     *
//     * @param  \App\User  $user
//     * @return void
//     */
//    public function forceDeleted(User $user)
//    {
//        //
//    }
}
