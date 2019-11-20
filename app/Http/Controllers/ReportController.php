<?php

namespace App\Http\Controllers;

use App\User;
use App\Group;
use App\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $tables = ['users','modules','module_versions','groups'];
    public function __construct() {
    }

    public function get_columns(Request $request) {
        $columns = [];
        foreach($this->tables as $table) {
            $table_columns = Schema::getColumnListing($table);
            foreach($table_columns as $column) {
                $columns[] = $table.'.'.$column;
            }
        }
        return $columns;
    }
    public function get_tables(Request $request) {
        return $this->tables;
    }
    public function query(Request $request) {
        $q = DB::table('users')
            ->leftJoin('module_assignments', function ($join) {
                $join->on('users.id', '=', 'module_assignments.user_id');
            })->leftJoin('module_versions', function ($join) {
                $join->on('module_assignments.module_version_id', '=', 'module_versions.id');
            })->leftJoin('modules', function ($join) {
                $join->on('module_versions.module_id', '=', 'modules.id');
            })
            ->where('modules.id','=',1)
            ->select('first_name','last_name','email','modules.name as module_name','module_versions.name as module_version_name',
            'date_assigned','date_started','date_due','date_completed','status','score','duration');
        foreach($request->block as $qblock_out) {
            foreach($qblock_out['check'] as $qblock_in) {
                $q->where($qblock_in['column'],$qblock_in['conditional'],$qblock_in['value']);
            }
        }
        $results = $q->get();
        return $results;
    }
}
