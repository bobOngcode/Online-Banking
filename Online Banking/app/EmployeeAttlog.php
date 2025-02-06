<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttlog extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'date_time',
        'device_id',
        'status',
        'verifying_type',
        'work_code',
        'file_upload_log_id',
    ];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function file_upload_log()
    {
        return $this->hasOne('App\FileUploadLog', 'id', 'file_upload_log_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
