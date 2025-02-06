<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RequiredEmployeeMap extends Model
{
    protected $fillable = ['branch_id', 'position_id', 'quantity'];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function position()
    {
        return $this->hasOne('App\Position', 'id', 'position_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
