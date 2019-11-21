<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['name','description','report','owner_user_id'];
    protected $casts = ['report' => 'object'];
    protected $with = ['owner'];

    public function owner(){
        return $this->belongsTo('App\User','owner_user_id');
    }
}
