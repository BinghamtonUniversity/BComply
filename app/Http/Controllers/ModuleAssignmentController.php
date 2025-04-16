<?php

namespace App\Http\Controllers;

use App\Module;
use App\ModuleAssignment;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class ModuleAssignmentController extends Controller
{
    public function run(Request $request, ModuleAssignment $module_assignment){
        $assignment = ModuleAssignment::where('id',$module_assignment->id)
            ->where('user_id',Auth::user()->id)
            ->with('version')
            ->with('module')
            ->first();
        return view('module_assignment',[
            'user'=>Auth::user(),
            'assignment' => $assignment,
            'page'=>"other"
        ]);
    }
    public function check_complete(Request $request, ModuleAssignment $module_assignment){
        if (is_null($module_assignment->date_completed)) {
            if (is_null($module_assignment->date_started)) {
                if ($request->specify_start_date) {
                    $module_assignment->date_started = $request->date_started;
                } else{
                    $module_assignment->date_started=now();
                }
            }
            if ($request->specify_completed_date){
                $module_assignment->date_completed = $request->date_completed;
            } else{
                $module_assignment->date_completed = now();
            }

            $module_assignment->status = $request->status;
            $module_assignment->updated_by_user_id = Auth::user()->id;
            $module_assignment->score = $request->score;

            $module_assignment->save();
            return $module_assignment;
        } else {
            return response(['error'=>'The user has already completed this module'], 409)->header('Content-Type', 'application/json');
        }
    }
    public function certificate(Request $request, ModuleAssignment $module_assignment){
        if (!is_null(Auth::user())){
            if (($module_assignment->status==='completed')||($module_assignment->status==='passed')){
                $pdf = \App::make('dompdf.wrapper');
                $pdf->loadHTML($this->convert_to_html($module_assignment));
                return $pdf->stream();
            } else {
                return response(['error'=>'The user has not completed this module'], 409)->header('Content-Type', 'application/json');
            }
        } else {
            return view('demo_login',['page'=>'demo','error'=>'The '.$request->accountId.' guest user account is not authorized']);
        }
    }

    public function convert_to_html(ModuleAssignment $module_assignment){
        PDF::setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        $module_version = $module_assignment->version()->first();
        $module = Module::where('id',$module_version->module_id)->first();
        if(isset($module->templates->certificate) && !is_null($module->templates->certificate)){
            $m = new \Mustache_Engine;
            $custom_certificate = $m->render($module->templates->certificate,
                [
                    'user'=>[
                        'first_name'=> $module_assignment->user->first_name,
                        'last_name'=>$module_assignment->user->last_name,
                    ],
                    'module'=>[
                        'name'=>$module->name,
                        'version_name'=>$module_version->name
                    ],
                    'assignment'=>[
                        'date_completed'=>$module_assignment->date_completed->format('m/d/y')
                    ]
                ]);
            $output = '
            <div class="container-fluid" 
            style="border: solid; 
            border-color:#005a43; 
            text-align: center;">
                <div class="container">
                    <div class="row">
                        '.$custom_certificate.'
                        <img style="max-width: 150px" src="'.config('app.certificate_img_url').'">
                        <h3>SUNY Binghamton</h3>
                    </div>
                </div>
            </div>';
        } else {
            return abort(404);
        }
        return $output;
    }
}
