<?php

namespace App\Http\Controllers;

use App\BulkAssignment;
use App\Module;
use App\ModuleAssignment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Libraries\QueryBuilder;

class BulkAssignmentController extends Controller
{
    private $tables = ['users','groups'];
    public function __construct() {}

    public function get_all_bulk_assignments(BulkAssignment $bulkAssignment) {
        return BulkAssignment::all();
    }

    public function get_bulk_assignment(Request $request, BulkAssignment $bulkAssignment) {
        return $bulkAssignment;
    }

    public function add_bulk_assignment(Request $request) {
        $bulkAssignment = new BulkAssignment($request->all());
        $bulkAssignment->save();
        return $bulkAssignment;
    }

    public function update_bulk_assignment(Request $request, BulkAssignment $bulkAssignment) {
        $bulkAssignment->update($request->all());
        return $bulkAssignment;
    }

    public function delete_bulk_assignment(Request $request, BulkAssignment $bulkAssignment) {
        $bulkAssignment->delete();
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

    public function execute(Request $request, BulkAssignment $bulkAssignment, $test=null) {
        $testonly = is_null($test)?false:true;
        $module = Module::where('id',$bulkAssignment->assignment->module_id)->with('current_version')->first();
        if (is_null($module->module_version_id)) {
            return response(['error'=>'The specified module does not have a current version'], 404)->header('Content-Type', 'application/json');
        }
        $q = BulkAssignment::base_query();
        QueryBuilder::build_where($q, $bulkAssignment->assignment);
        $users = $q->select('users.id','unique_id','first_name','last_name')->get();
        $assign_users = []; $skip_users = [];

        foreach ($users as $user) {
            $assignment = $module->assign_to([
                'user_id' => $user->id,
                'date_assigned' => $bulkAssignment->date_assigned,
                'date_due' => $bulkAssignment->date_due,
            ],$testonly);
            if (is_null($assignment)) {
                $skip_users[] = $user;
            } else {
                $assign_users[] = $user;
            }    
        }
        $results = [
            'assign_users' => $assign_users,
            'skip_users' => $skip_users,
            'module' => $module,
        ];
        return $results;
    }
}
