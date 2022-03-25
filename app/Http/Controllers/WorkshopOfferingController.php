<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\User;
use App\Workshop;
use App\WorkshopAttendance;
use App\WorkshopOffering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkshopOfferingController extends Controller
{

    public function get_workshop_attendances(Request $request,WorkshopOffering $offering){
       
        return WorkshopAttendance::where('workshop_offering_id',$offering->id)
            ->select('id','workshop_id','workshop_offering_id','user_id','status')
            ->with(['attendee'=>function($query){
                $query->select('id','first_name','last_name');
            }])->get();
    }
}
