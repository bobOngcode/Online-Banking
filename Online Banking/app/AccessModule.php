<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessModule extends Model
{
    protected $fillable = ['name'];

    public function approver_per_level()
    {
        return $this->hasMany('App\ApproverPerLevel', 'module_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
