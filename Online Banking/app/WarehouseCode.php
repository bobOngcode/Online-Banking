<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseCode extends Model
{
    protected $fillable = ['branch', 'code'];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'name', 'branch');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
