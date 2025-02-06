<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SapDatabase extends Model
{
    protected $fillable = ['server', 'database', 'username', 'password', 'active'];

    public function sap_db_branches()
    {
        return $this->hasMany('App\SapDbBranch', 'sap_database_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}

