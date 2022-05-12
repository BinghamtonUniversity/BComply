<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkshopPermission extends Model
{
    protected $fillable = ['user_id','permission', 'workshop_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function workshop(){
        return$this->belongsTo(Workshop::class);
    }
}
