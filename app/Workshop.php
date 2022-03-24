<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
class Workshop extends Model
{
    //use HasFactory;
    use SoftDeletes;
    protected $fillable = ['name','description','icon','owner_id','config','files','duration','public'];
    protected $casts = ['config' => 'object','files'=>'array'];
  
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(){
        return $this->belongsTo(User::class,'owner_id');
    }
       /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workshop_offerings(){
        return $this->hasMany(WorkshopOffering::class);
    }

         /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function workshop_attendace(){
        return $this->hasMany(WorkshopAttendance::class);
    }
}
