<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SapDbBranch extends Model
{
    protected $fillable = ['branch_id', 'sap_database_id'];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function sap_database()
    {   
        return $this->hasOne('App\SapDatabase', 'id', 'sap_database_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    
}
