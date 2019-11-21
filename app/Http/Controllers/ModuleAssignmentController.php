<?php

namespace App\Http\Controllers;

use App\ModuleAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleAssignmentController extends Controller
{
    public function run(Request $request, ModuleAssignment $module_assignment){
        $assignment = ModuleAssignment::where('id',$module_assignment->id)->with('version')->first();
        return view('module_assignment',[
            'user'=>Auth::user(),
            'assignment' => $assignment,
        ]);

    }
}
