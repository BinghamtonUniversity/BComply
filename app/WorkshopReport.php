<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkshopReport extends Model
{
    protected $fillable = ['name','description','report','owner_user_id','permissions'];
    protected $casts = ['report' => 'object','permissions'=>'object'];
    protected $with = ['owner'];

    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function owner(){
        return $this->belongsTo('App\User','owner_user_id');
    }
    public function getPermissionsAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }
        return json_decode($value);
    }

}
