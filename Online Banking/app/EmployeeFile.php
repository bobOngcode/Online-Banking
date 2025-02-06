<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeFile extends Model
{
    protected $fillable = [
        'branch_id',
        'file_name',
        'file_path',
        'file_type',
        'title',
        'file_upload_log_id',
    ];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
