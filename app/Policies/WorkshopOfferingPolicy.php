<?php

namespace App\Policies;

use App\Workshop;
use App\WorkshopAttendance;
use App\WorkshopOffering;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\PublicAPIAuth;
class WorkshopOfferingPolicy
{
    use HandlesAuthorization;

    public function view_offering(User $user,String $offering){
        $workshop_offering = WorkshopOffering::where('id',$offering)->first();
        $is_public = $workshop_offering->workshop->public;
        $workshop_attendance =  WorkshopAttendance::where('workshop_offering_id',$workshop_offering->id)->where('user_id',$user->id)->first();
       
        if($is_public || $workshop_attendance){
            return true;
        }
    }
    public function register(User $user,WorkshopOffering $offering){
      
        $is_public = $offering->workshop->public;
        $is_past = false;
    
        if(time() >= strtotime($offering->workshop_date)){  
            $is_past= true;
        }
        $seats_remaining = $offering->max_capacity  -$offering->workshop_attendance->count(); 
        if($is_public
        && !$is_past && $seats_remaining ){
            return true;
        }
    }
    public function cancel_registration(User $user,WorkshopOffering $offering){
        
        $is_past = false;
        $is_user = is_null($user);
        if(time() >= strtotime($offering->workshop_date)){  
            $is_past= true;
        }   
        if(!$is_past && !$is_user 
         ){
            return true;
        }
    }
    public function create_workshop_offering(User $user, String $workshop_id){
        $workshop = Workshop::where('id',$workshop_id)->first();
        if(in_array('manage_workshops',$user->user_permissions)||$workshop->owner_id === $user->id) {
            return true;
        }
    }



}