<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApprovedLog extends Model
{
    protected $fillable = [
        'module_id',
        'document_id',
        'approver_id',
        'level',
    ];

    public function approver()
    {
        return $this->hasOne('App\User', 'id', 'approver_id');
        // ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }




}
