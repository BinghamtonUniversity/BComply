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
        if(is_null($moduleAssignment->date_completed)){
            $moduleAssignment->update(
                [
                    'date_started'=>Carbon::now(),
                    'date_completed'=>Carbon::now(),
                    'score'=>1,
                    'status'=>'passed',
                    'updated_by_user_id'=>Auth::user()->id,
                    'duration'=>0]
            );
            $moduleAssignment->save();
        }
        else{
            return response(['error'=>'The user has already completed this module'], 409)->header('Content-Type', 'application/json');
        }
    }
}
