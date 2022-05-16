<?php

namespace App\Http\Controllers;


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
        return 'Success';
    }
    public function get_instructor_offerings(Request $request){
        
          $is_instructor = is_null(WorkshopOffering::where('instructor_id',Auth::user()->id)->select('id')->first())?false:true;
      
          if($is_instructor){
              return WorkshopOffering::where('instructor_id',Auth::user()->id)
              ->with(['instructor'=>function($query){
                  $query->select('id','first_name','last_name');
              }])->get();
          }
          else{
              return [];
          }
      
  }

    public function get_workshop_offerings(Request $request,Workshop $workshop){
          if (in_array('manage_workshops',Auth::user()->user_permissions) ||
            in_array('assign_workshops',Auth::user()->user_permissions)) {

                return WorkshopOffering::where('workshop_id',$workshop->id)
                ->with(['instructor'=>function($query){
                    $query->select('id','first_name','last_name');
                }])->get();
        }
        else{
            $is_instructor = is_null(WorkshopOffering::where('workshop_id',$workshop->id)->where('instructor_id',Auth::user()->id)->select('id')->first())?false:true;
        
            if($is_instructor){
                return WorkshopOffering::where('workshop_id',$workshop->id)
                ->where('instructor_id',Auth::user()->id)
                ->with(['instructor'=>function($query){
                    $query->select('id','first_name','last_name');
                }])->get();
            }
            else{
                return [];
            }
        }


       

        
    }
    public function add_recurring_workshop_offering(Request $request, Workshop $workshop){
            $workshop_offering = new WorkshopOffering($request->all());
            $daysOfMonth =array();
            $first_week = [1,2,3,4,5,6,7];
            $second_week = [8,9,10,11,12,13,14];
            $third_week = [15,16,17,18,19,20,21];
            $fourth_week = [22,23,24,25,26,27,28];
            $fifth_week = [29,30,31];
            $start_date =  date('Y-m-d H:i:s', strtotime($request->recurring_start_date ));
            $end_date =  date('Y-m-d H:i:s', strtotime($request->recurring_end_date ));
            if(in_array(0,$request->repeat_every_placement)){
                array_push( $daysOfMonth,...$first_week);
            }
            if(in_array(1,$request->repeat_every_placement)){
                array_push( $daysOfMonth,...$second_week);
            }
            if(in_array(2,$request->repeat_every_placement)){
                array_push( $daysOfMonth,...$third_week);
            }
            if(in_array(3,$request->repeat_every_placement)){
                array_push( $daysOfMonth,...$fourth_week);
            }
            if(in_array(4,$request->repeat_every_placement)){
                array_push( $daysOfMonth,...$fifth_week);
            }
            $flag = 0;
            $first_id;
            while($start_date <= $end_date){
                
                $dayofmonth = date('d', strtotime($start_date));
                $dayofweek = date('w', strtotime($start_date));
                if(in_array($dayofmonth ,$daysOfMonth) && in_array($dayofweek,$request->repeat_every_on)){
                        
                       
                         $new_offering =   WorkshopOffering::create( [
                                'workshop_id' => $workshop_offering->workshop_id,
                                'instructor_id' => $workshop_offering->instructor_id,
                                'max_capacity' => $workshop_offering->max_capacity,
                                'locations' => $workshop_offering->locations,
                                'workshop_date' => $start_date,
                                'type' => $workshop_offering->type,
                                'is_multi_day' =>false,
                                'multi_days' => [],
                            ]);
                        if ($flag ==0){
                            $first_id = $new_offering->id;
                        }
                        $flag = 1;

                    
                }
               
                $start_date= date('Y-m-d H:i:s', strtotime($start_date . " + 1 day"));
            }
            
           
        return WorkshopOffering::where('id','>=',$first_id )->with('instructor')->get();
        
        
    }

    public function add_workshop_offering(Request $request){
     
        $workshop_offering = new WorkshopOffering($request->all());

        //handle null array
        if($workshop_offering->multi_days == NULL){
            $workshop_offering->multi_days=[];
        }
      
        $workshop_offering->save();

        return $workshop_offering->where('id',$workshop_offering->id)->with('instructor')->first();
    }
    public function update_workshop_offering(Request $request,Workshop $workshop,WorkshopOffering $offering){
        $offering->update($request->all());
        $offering->save();

        return $offering->where('id',$offering->id)->with('instructor')->first();
    }
    public function delete_workshop_offering(Request $request,Workshop $workshop,WorkshopOffering $offering){

        $offering->delete();
        return $offering->id;
    }
    public function get_workshop_attendances(Request $request,Workshop $workshop,WorkshopOffering $offering){
        return WorkshopAttendance::where('workshop_id',$workshop->id)->where('workshop_offering_id',$offering->id)
            ->with(['attendee'=>function($query){
                $query->select('id','first_name','last_name');
            }])->get();
    }
    public function add_workshop_attendances(Request $request){
        $attendance = new WorkshopAttendance($request->all());
        $attendance->save();

        return $attendance->where('id',$attendance->id)->with('attendee')->first();
    }
    public function update_workshop_attendances(Request $request,Workshop $workshop,WorkshopOffering $offering,WorkshopAttendance $attendance){
        $attendance->update($request->all());
        $attendance->save();

        return $attendance->where('id',$attendance->id)->with('attendee')->first();
    }
    public function delete_workshop_attendances(Request $request,Workshop $workshop,WorkshopOffering $offering,WorkshopAttendance $attendance){
      
       $attendance->delete();
       return $attendance->id;
   }
   public function get_workshop_files(Request $request,Workshop $workshop){
    $files = array();
    $counter = 0;
    foreach($workshop->files as $file){
        $obj = (object) array('id'=>$counter,'name' => $file);
        $counter ++;
        array_push($files,$obj);
    }
    return  $files;
    }
}
