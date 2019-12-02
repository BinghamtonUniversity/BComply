<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BulkAssignment extends Model
{
    protected $fillable = ['name','description','assignment'];
    protected $casts = ['assignment' => 'object'];
}
