<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModuleActivityLog extends Model
{
    protected $fillable = [
        'module_id',
        'document_id',
        'user_id',
        'description',
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function module()
    {
        return $this->hasOne('App\AccessModule', 'id', 'module_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
