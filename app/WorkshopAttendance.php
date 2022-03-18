<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class WorkshopAttendance extends Model
{
    //use HasFactory;
    use SoftDeletes;

    protected $fillable = ['workshop_offering_id','workshop_id','user_id','status'];
   

    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(){
        return $this->belongsTo(User::class,'user_id');
    }

        /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workshop(){
        return $this->belongsTo(workshop::class,'workshop_id');
    }
}
