<?php

namespace App\Policies;

use App\Workshop;
use App\WorkshopAttendance;
use App\WorkshopOffering;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class WorkshopPolicy
{
    use HandlesAuthorization;
/**
     * Determine whether the user can manage workshops.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function view_in_admin(User $user){
        $is_workshop_owner = is_null(Workshop::where('owner_id',$user->id)->select('id')->first())?false:true;
        if(in_array('manage_workshops',$user->user_permissions)
        || in_array('assign_workshops',$user->user_permissions)
        || $is_workshop_owner
         ){
            return true;
        }
    }


    public function manage_all_workshops(User $user)
    {
        if(in_array('manage_workshops',$user->user_permissions) || sizeof((Array)$user->workshop_permissions)>0){
            return true;
        }
    }

    public function create_workshops(User $user){
        if(in_array('manage_workshops',$user->user_permissions)){
            return true;
        }
    }
 

    public function manage_workshops(User $user, Workshop $workshop)
    {
        if(in_array('manage_workshops',$user->user_permissions)||$workshop->owner_id === $user->id) {
            return true;
        }
        if ($workshop->owner_user_id === $user->id) {
            return true;
        }
        if (property_exists($user->workshop_permissions,$workshop->id) &&
            in_array('manage',$user->workshop_permissions->{$workshop->id})){
            return true;
        }
    }
    public function assign_workshops(User $user, String $workshop_id)
    {
        $workshop = Workshop::where('id',$workshop_id)->first();
        if(in_array('assign_workshops',$user->user_permissions)|| $workshop->owner_id === $user->id) {
            return true;
        }
    }

    public function delete_workshop(User $user){
        if(in_array('manage_workshops',$user->user_permissions)){
            return true;
        }
    }

    public function view_workshop(User $user, Workshop $workshop)
    {
        $is_report_owner = is_null(Workshop::where('owner_id',$user->id)->select('id')->first())?false:true;
        if( property_exists($user->workshop_permissions,$workshop->id) &&
            in_array('report',$user->workshop_permissions->{$workshop->id})) {
            return true;
        }
        
        if ($this->manage_workshop($user,$workshop)){
            return true;
        }
    }
    public function view_workshop_offerings(User $user){
        $is_workshop_owner = is_null(Workshop::where('owner_id',$user->id)->select('id')->first())?false:true;
        $is_instructor = is_null(WorkshopOffering::where('instructor_id',$user->id)->select('id')->first())?false:true;
        if(in_array('manage_workshops',$user->user_permissions)
        || in_array('assign_workshops',$user->user_permissions)
        || $is_workshop_owner || $is_instructor
         ){
            return true;
        }
    }
    public function create_workshop_offerings(User $user,String $workshop_id)
    {
        $workshop = Workshop::where('id',$workshop_id)->with('owner')->first();
    
        if(in_array('manage_workshops',$user->user_permissions)||$workshop->owner_id === $user->id) {
            return true;
        }
        if ($workshop->owner_id === $user->id) {
            return true;
        }
        if (property_exists($user->workshop_permissions,$workshop->id) &&
            in_array('manage',$user->workshop_permissions->{$workshop->id})){
            return true;
        }
    }
    public function manage_workshop_offerings(User $user,Workshop $workshop, WorkshopOffering $offering)
    {
        if(in_array('manage_workshops',$user->user_permissions)||$workshop->owner_id === $user->id) {
            return true;
        }
        if ($offering->instructor->id === $user->id) {
            return true;
        }
        if ($offering->workshop->owner_id === $user->id) {
            return true;
        }
        if (property_exists($user->workshop_permissions,$workshop->id) &&
            in_array('manage',$user->workshop_permissions->{$workshop->id})){
            return true;
        }
    }
    public function view_workshop_attendances(User $user,Workshop $workshop, WorkshopOffering $offering)
    {
        
    // $workshop = Workshop::where('id',$workshop_id)->with('owner')->first();
    // $offering = WorkshopOffering::where('id',$offering_id)->with('instructor')->first();
        if(in_array('manage_workshops',$user->user_permissions)||$workshop->owner_id === $user->id) {
            return true;
        }
        if ($offering->instructor->id === $user->id) {
            return true;
        }
        if ($offering->workshop->owner_id === $user->id) {
            return true;
        }
        if (property_exists($user->workshop_permissions,$workshop->id) &&
            in_array('manage',$user->workshop_permissions->{$workshop->id})){
            return true;
        }
    }
    public function create_workshop_attendances(User $user,String $workshop_id, String $offering_id)
    {
        
     $workshop = Workshop::where('id',$workshop_id)->with('owner')->first();
     $offering = WorkshopOffering::where('id',$offering_id)->with('instructor')->first();
        if(in_array('manage_workshops',$user->user_permissions)||$workshop->owner_id === $user->id) {
            return true;
        }
        if ($offering->instructor->id === $user->id) {
            return true;
        }
        if ($offering->workshop->owner_id === $user->id) {
            return true;
        }
        if (property_exists($user->workshop_permissions,$workshop->id) &&
            in_array('manage',$user->workshop_permissions->{$workshop->id})){
            return true;
        }
    }
    
    public function manage_workshop_attendances(User $user, Workshop $workshop,WorkshopOffering $offering)
    {
        
        if(in_array('manage_workshops',$user->user_permissions)||$workshop->owner_id === $user->id) {
            return true;
        }
        if ($offering->instructor->id === $user->id) {
            return true;
        }
        if ($offering->workshop->owner_id === $user->id) {
            return true;
        }
        if (property_exists($user->workshop_permissions,$workshop->id) &&
            in_array('manage',$user->workshop_permissions->{$workshop->id})){
            return true;
        }
    }
}