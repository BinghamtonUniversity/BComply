<?php

namespace App\Http\Controllers;

use App\BulkAssignment; //might need to be deleted
use App\Report; //might need to be deleted
use Illuminate\Support\Facades\Storage;
use App\User;
use App\Workshop;
use App\WorkshopAttendance;
use App\WorkshopOffering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkshopController extends Controller
{
    public function get_all_workshops(){
        return Workshop::with('owner')->get();
        // TODO
        // if (in_array('manage_modules',Auth::user()->user_permissions) ||
        //     in_array('assign_modules',Auth::user()->user_permissions)) {

        //     // If user can manage modules, return all modules
        //     return Workshop::with('owner')->get();
        // }
        // else {
        //     // Only return modules where the user has admin permissions
        //     return Workshop::whereIn('id',array_keys((Array)(Auth::user()->module_permissions)))
        //         ->orWhere('owner_user_id','=',Auth::user()->id)->with('owner')->with('current_version')->get();
        // }
    }
    public function add_workshop(Request $request){
        $workshop = new Workshop($request->all());
        $workshop->save();

        return $workshop->where('id',$workshop->id)->with('owner')->first();
    }
    public function update_workshop(Request $request,Workshop $workshop){
        $workshop->update($request->all());
        $workshop->save();

        return $workshop->where('id',$workshop->id)->with('owner')->first();
    }
    public function delete_workshop(Request $request,Workshop $workshop){
      
        WorkshopOffering::where('workshop_id',$workshop->id)->delete();
        WorkshopAttendance::where('workshop_id',$workshop->id)->delete();
        $workshop->delete();
        //Delete bulk assignment
        //Delete module assingments
        //Module version
        //Set current version to null -> We are already deleting the module with all of its versions, why assigning the current version to null?
        return 'Success';
    }

    public function get_workshop_offerings(Request $request,Workshop $workshop){
        return WorkshopOffering::where('workshop_id',$workshop->id)
            ->select('id','workshop_id','instructor_id','max_capacity','locations','workshop_date','type')
            ->with(['user'=>function($query){
                $query->select('id','first_name','last_name');
            }])->get();
    }
    public function add_workshop_offering(Request $request,WorkshopOffering $workshop_offering){
        $workshop_offering = new WorkshopOffering($request->all());
        $workshop_offering->save();

        return $workshop_offering->where('id',$workshop_offering->id)->with('owner')->first();
    }
}
