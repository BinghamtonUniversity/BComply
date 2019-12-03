<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class BulkAssignment extends Model
{
    protected $fillable = ['name','description','assignment'];
    protected $casts = ['assignment' => 'object'];

    static public function base_query() {
        return DB::table('users')
        ->leftJoin('group_memberships', function ($join) {
            $join->on('users.id', '=', 'group_memberships.user_id');
        })->leftJoin('groups', function ($join) {
            $join->on('group_memberships.group_id', '=', 'groups.id');
        })
        ->distinct();
    }
}
