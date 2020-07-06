<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ModuleAssignment;

class VideoController extends Controller
{
    public function get_duration(Request $request, ModuleAssignment $assignment) {
        if (!is_null($assignment)) {
            if (is_null($assignment->date_completed)) {
                return ['time'=>$assignment->duration];
            } else {
                return ['time'=>0];
            }
        }
        return response('Assignment Does Not Exist',404);
    }
    public function set_duration(Request $request, ModuleAssignment $assignment) {
        if (!is_null($assignment) && is_null($assignment->date_completed)) {
            $assignment->duration = $request->time;
            $assignment->update();
            return ['response'=>'acknowledged'];
        }
        return response('Assignment Does Not Exist',404);
    }
    public function register_statement(Request $request, ModuleAssignment $assignment) {
        if (!is_null($assignment)) {
            if ($request->verb === 'completed') {
                if (is_null($assignment->date_completed)) {
                    if (is_null($assignment->date_completed)) {
                        $assignment->date_completed = now();
                        $assignment->status = 'completed';
                    }
                }
            }
            if ($request->verb === 'in_progress') {
                if (is_null($assignment->date_started)) {
                    $assignment->date_started = now();
                    $assignment->status = 'in_progress';
                }
            }
            $assignment->updated_by_user_id = Auth::user()->id;
            $assignment->save();
        }
        return ['response'=>'acknowledged'];
    }
}
