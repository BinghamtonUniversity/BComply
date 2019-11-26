<?php

namespace App\Http\Controllers;

use App\User;
use App\Group;
use App\Module;
use App\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $tables = ['users','modules','module_versions','module_assignments','groups'];
    public function __construct() {}

    // Route::get('/reports','ReportController@get_all_reports');
    // Route::get('/reports/{report}','ReportController@get_report');
    // Route::post('/reports','ReportController@add_report');
    // Route::put('/reports/{report}','ReportController@update_report');
    // Route::delete('/reports/{report}','ReportController@delete_report');

    public function get_all_reports(Request $request) {
        return Report::all();
    }

    public function get_report(Request $request, Report $report) {
        return $report;
    }

    public function add_report(Request $request) {
        $report = new Report($request->all());
        $report->save();
        return $report;
    }

    public function update_report(Request $request, Report $report) {
        $report->update($request->all());
        return Report::where('id',$report->id)->with('owner')->first();
    }

    public function delete_report(Request $request, Report $report) {
        $report->delete();
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
    private function qblock_and(&$q, $qblock) {
        if ($qblock->conditional === 'contains') {
            $q->where($qblock->column,'like','%'.$qblock->value.'%');
        } else if ($qblock->conditional === 'is_null') {
            $q->whereNull($qblock->column);
        } else if ($qblock->conditional === 'not_null') {
            $q->whereNotNull($qblock->column);
        } else {    
            $q->where($qblock->column,$qblock->conditional,$qblock->value);
        }
    }
    private function qblock_or(&$q, $qblock) {
        if ($qblock->conditional === 'contains') {
            $q->orWhere($qblock->column,'like','%'.$qblock->value.'%');
        } else if ($qblock->conditional === 'is_null') {
            $q->orWhereNull($qblock->column);
        } else if ($qblock->conditional === 'not_null') {
            $q->orWhereNotNull($qblock->column);
        } else {    
            $q->orWhere($qblock->column,$qblock->conditional,$qblock->value);
        }
    }
    public function execute(Request $request, Report $report) {
        $default_columns = ['first_name','last_name','email','modules.name as module_name','module_versions.name as version_name','date_assigned'];
        $columns = array_merge($default_columns, $report->report->columns);

        $subq_user_groups = DB::table('group_memberships')
            ->leftJoin('groups', function($join) {
                $join->on('group_memberships.group_id','=','groups.id');
            })->groupBy('group_memberships.user_id')
            ->select('group_memberships.user_id', DB::raw('group_concat(groups.name) as groups'));
        $q = DB::table('users')
            ->leftJoin('module_assignments', function ($join) {
                $join->on('users.id', '=', 'module_assignments.user_id');
            })->leftJoin('module_versions', function ($join) {
                $join->on('module_assignments.module_version_id', '=', 'module_versions.id');
            })->leftJoin('modules', function ($join) {
                $join->on('module_versions.module_id', '=', 'modules.id');
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
        $q->where(function ($q) use ($report){
            foreach($report->report->block as $qblock_out) {
                if ($report->report->and_or === 'or') {
                    $q->orWhere(function ($q) use ($qblock_out){
                        foreach($qblock_out->check as $qblock_in) {
                            if ($qblock_out->and_or === 'and') {
                                $this->qblock_and($q,$qblock_in);
                            } else if ($qblock_out->and_or === 'or') {
                                $this->qblock_or($q,$qblock_in);
                            }
                        }
                    });
                } else if ($report->report->and_or === 'and') {
                    $q->where(function ($q) use ($qblock_out) {
                        foreach($qblock_out->check as $qblock_in) {
                            if ($qblock_out->and_or === 'and') {
                                $this->qblock_and($q,$qblock_in);
                            } else if ($qblock_out->and_or === 'or') {
                                $this->qblock_or($q,$qblock_in);
                            }
                        }
                    });
                }
            }
        });
        $results = $q->select($columns)->get();
        return $results;
    }
}
