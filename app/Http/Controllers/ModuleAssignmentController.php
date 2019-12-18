<?php

namespace App\Http\Controllers;

use App\ModuleAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleAssignmentController extends Controller
{
    public function run(Request $request, ModuleAssignment $module_assignment){
        $assignment = ModuleAssignment::where('id',$module_assignment->id)->with('version')->first();
        if($module_assignment->module_version_id)
        return view('module_assignment',[
            'user'=>Auth::user(),
            'assignment' => $assignment,
            'page'=>"other"
        ]);
    }
    public function check_complete(Request $request, ModuleAssignment $moduleAssignment){
//        $activity_id_arr = explode('/',$request->object['id']);
//        $activity_id = $activity_id_arr[0];
//        $assignment = ModuleAssignment::where('user_id',Auth::user()->id)->where('id',$activity_id)->first();
//
////        $assignment->
        if(is_null($moduleAssignment->date_completed)){
            $moduleAssignment->date_started = now();
            $moduleAssignment->date_completed = now();
            $moduleAssignment->score = 0;
            $moduleAssignment->status = 'passed';
            $moduleAssignment->updated_by_user_id = Auth::user()->id;
            $moduleAssignment->save();
        }
        else{
            return response(['error'=>'The user has already completed this module'], 409)->header('Content-Type', 'application/json');
        }
    }
}
