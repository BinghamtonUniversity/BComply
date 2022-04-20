<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class WorkshopOffering extends Model
{
   // use HasFactory;
   use SoftDeletes;

   protected $fillable = ['workshop_id','max_capacity','locations','instructor_id','workshop_date','type','is_multi_day','multi_days'];
   protected $casts = ['multi_days' => 'array'];
   protected function serializeDate(\DateTimeInterface $date) {
       return $date->format('Y-m-d H:i:s');
   }
      /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workshop_attendance(){
        return $this->hasMany(WorkshopAttendance::class);
    }
   /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
   public function workshop(){
       return $this->belongsTo(Workshop::class,'workshop_id');
   }
   public function instructor(){
    return $this->belongsTo(User::class,'instructor_id');
}
}
