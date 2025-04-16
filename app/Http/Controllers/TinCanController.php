<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\ModuleAssignment;

class TinCanController extends Controller
{   
    public function get_state(Request $request) {
        $assignment = ModuleAssignment::where('user_id',Auth::user()->id)->where(function (Builder $query) use ($request) {
            $query->orWhere('id',$request->id)
                ->where('id',$request->activityId)
                ->orWhere('id',$request->registration);
        })->first();
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
        $assignment = ModuleAssignment::where('user_id',Auth::user()->id)->where(function (Builder $query) use ($request) {
            $query->orWhere('id',$request->id)
                ->where('id',$request->activityId)
                ->orWhere('id',$request->registration);
        })->first();
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
        $assignment = ModuleAssignment::where('user_id',Auth::user()->id)->where('id',$request->context['registration'])->sharedLock()->first();
        if (!is_null($assignment)) {
            if (!is_null($assignment->date_started) && is_null($assignment->date_completed)) {
                $assignment->duration = $assignment->date_started->diffInSeconds();
            }
            if ($request->verb['id'] === 'http://adlnet.gov/expapi/verbs/passed') {
                if (is_null($assignment->date_completed)) {
                    $assignment->date_completed = now();
                    if (isset($request->result['score']) && isset($request->result['score']['scaled'])) {
                        $assignment->score = $request->result['score']['scaled'];
                    }
                    $assignment->status = 'passed';
                }
            }
            if ($request->verb['id'] === 'http://adlnet.gov/expapi/verbs/failed') {
                if (is_null($assignment->date_completed)) {
                    $assignment->date_completed = now();
                    if (isset($request->result['score']) && isset($request->result['score']['scaled'])) {
                        $assignment->score = $request->result['score']['scaled'];
                    }
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
            return ['response'=>'acknowledged'];
        }
        return response('Assignment Does Not Exist',404);
    }
}
