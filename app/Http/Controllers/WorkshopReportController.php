<?php

namespace App\Http\Controllers;

use App\User;
use App\Group;
use App\Workshop;
use App\WorkshopOffering;
use App\WorkshopAttendance;
use App\WorkshopReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Libraries\QueryBuilder;

use Illuminate\Http\Request;

class WorkshopReportController extends Controller
{
    private $tables = ['users','workshops','groups','workshop_offerings','workshop_attendances'];
    public function __construct() {}

    public function get_all_reports(Request $request) {
        // if(Auth::user()){
        //    if (in_array('manage_reports',Auth::user()->user_permissions)){
        //         return Report::all();
        //     }
        //     elseif(in_array('run_reports',Auth::user()->user_permissions)&& in_array('manage_reports',Auth::user()->user_permissions)){
        //         return Report::all();
        //     }
        //     else {
        //         return Report::where('owner_user_id',Auth::user()->id)->orWhereJsonContains('permissions',Auth::user()->id)->get();
        //     }
        // }
        // else{
        //     return "Failed to authenticate";
        // }
        return WorkshopReport::all();
    }

    public function get_report(Request $request, WorkshopReport $workshop_report) {
        return $workshop_report;
    }

    public function add_report(Request $request) {
        $workshop_report = new WorkshopReport($request->all());
        $workshop_report->save();
        return $workshop_report->where('id',$workshop_report->id)->with('owner')->first();
    }

    public function update_report(Request $request, WorkshopReport $workshop_report) {
        $workshop_report->update($request->all());
        return $workshop_report->where('id',$workshop_report->id)->with('owner')->first();
    }

    public function delete_report(Request $request, WorkshopReport $workshop_report) {
        $workshop_report->delete();
        return 'Success';
    }

    public function get_columns(Request $request) {
        $columns = [];
        foreach($this->tables as $table) {
            $table_columns = Schema::getColumnListing($table);
            foreach($table_columns as $column) {
                if (!in_array($column,['id','created_at','updated_at'])) {
                    $columns[] = $table.'.'.$column;
                }
                
            }
        }
        return $columns;
    }

    public function get_tables(Request $request) {
        return $this->tables;
    }
    public function execute(Request $request, WorkshopReport $workshop_report) {
        $default_columns = ['first_name','last_name','email','workshops.name as workshop_name','workshop_offerings.workshop_date as workshop_date','workshop_attendances.attendance','workshop_attendances.status'];
        $columns = array_merge($default_columns, $workshop_report->report->columns);

        $subq_user_groups = DB::table('group_memberships')
            ->leftJoin('groups', function($join) {
                $join->on('group_memberships.group_id','=','groups.id');
            })->groupBy('group_memberships.user_id')
            ->select('group_memberships.user_id', DB::raw('group_concat(`groups`.`name`) as `groups`'));
        $q = DB::table('users')
            ->leftJoin('workshop_attendances', function ($join) {
                $join->on('users.id', '=', 'workshop_attendances.user_id');
            })->leftJoin('workshops', function ($join) {
                $join->on('workshop_attendances.workshop_id', '=', 'workshops.id');
            })->leftJoin('workshop_offerings', function ($join) {
                $join->on('workshop_attendances.workshop_offering_id', '=', 'workshop_offerings.id');
            })->leftJoin('group_memberships', function ($join) {
                $join->on('users.id', '=', 'group_memberships.user_id');
            })->leftJoin('groups', function ($join) {
                $join->on('group_memberships.group_id', '=', 'groups.id');
            })
            // Join with list of user groups
            ->leftJoinSub($subq_user_groups, 'user_groups', function ($join) {
                $join->on('users.id', '=', 'user_groups.user_id');
            })
            ->distinct();
        QueryBuilder::build_where($q, $workshop_report->report);

        $results = $q->select($columns)->get();
        return $results;
    }
}
