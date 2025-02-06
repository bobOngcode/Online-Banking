<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'division_id', 'active'];

    public function division()
    {
        return $this->hasOne('App\Division', 'id', 'division_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
