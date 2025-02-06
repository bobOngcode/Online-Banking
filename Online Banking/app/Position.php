<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['name'];

    public function product()
    {   
        return $this->belongsTo('App\User', 'position_id','id');
        //                 ( <Model>, <id_of_specified_Model>, id_of_this_model>  )
    }

    public function rank()
    {
        return $this->hasOne('App\Rank', 'id', 'rank_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function required_employees()
    {
        return $this->hasMany('App\RequiredEmployeeMap', 'position_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
