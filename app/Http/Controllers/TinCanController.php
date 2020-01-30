<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ModuleAssignment;

class TinCanController extends Controller
{
    public function get_state(Request $request) {
        $assignment = ModuleAssignment::where('user_id',Auth::user()->id)
            ->where('id',$request->activityId)->first();
        if (!is_null($assignment)) {
            if (isset($assignment->current_state->state)) {
                return $assignment->current_state->state;
            } else {
                return '';
            }
        }
        return response('Assignment Does Not Exist',404);
    }
    public function set_state(Request $request) {
        $assignment = ModuleAssignment::where('user_id',Auth::user()->id)
            ->where('id',$request->activityId)->first();
        if (!is_null($assignment)) {
            $assignment->current_state = (Object)[
                'state' => $request->getContent()
            ];
            $assignment->update();
            return ['response'=>'acknowledged'];
        }
        return response('Assignment Does Not Exist',404);
    }
    public function register_statement(Request $request) {
        $activity_id_arr = explode('/',$request->object['id']);
        $activity_id = $activity_id_arr[0];
        $assignment = ModuleAssignment::where('user_id',Auth::user()->id)->where('id',$activity_id)->sharedLock()->first();
        if (!is_null($assignment)) {
            if (!is_null($assignment->date_started) && is_null($assignment->date_completed)) {
                $assignment->duration = $assignment->date_started->diffInSeconds();
            }
            if ($request->verb['id'] === 'http://adlnet.gov/expapi/verbs/passed') {
                if (is_null($assignment->date_completed)) {
                    $assignment->date_completed = now();
                    $assignment->score = $request->result['score']['scaled'];
                    $assignment->status = 'passed';
                }
            }
            if ($request->verb['id'] === 'http://adlnet.gov/expapi/verbs/failed') {
                if (is_null($assignment->date_completed)) {
                    $assignment->date_completed = now();
                    $assignment->score = $request->result['score']['scaled'];
                    $assignment->status = 'failed';
                }
            }
            if ($request->verb['id'] === 'http://adlnet.gov/expapi/verbs/experienced') {
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
