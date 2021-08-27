<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuleAssignment extends Model
{
    protected $fillable = ['user_id','module_version_id','module_id','date_assigned','date_due','updated_by_user_id','assigned_by_user_id','current_state'];
    protected $casts = ['current_state' => 'object','date_started'=>'datetime','date_assigned'=>'datetime','date_completed'=>'datetime','date_due'=>'datetime'];

    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function user(){
        return $this->belongsTo('App\SimpleUser','user_id');
    }
    public function version(){
        return $this->belongsTo('App\ModuleVersion','module_version_id');
    }
    public function module(){
        return $this->belongsTo('App\Module','module_id');
    }

}
