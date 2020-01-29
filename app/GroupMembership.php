<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GroupMembership extends Model
{
    protected $fillable = ['group_id','user_id','type'];
    //

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(){
        return $this->belongsTo('App\Group');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(){
        return $this->belongsTo('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function simple_user(){
        return $this->belongsTo('App\SimpleUser','user_id')
            ->select('id','unique_id','first_name','last_name','email');
    }
}
