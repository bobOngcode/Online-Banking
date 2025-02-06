<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileUploadLog extends Model
{   
    protected $fillable = [
        'branch_id',
        'docdate',
        'docname',
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function employees()
    {
        return $this->hasMany('App\Employee', 'file_upload_log_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function employee_loans()
    {
        return $this->hasMany('App\EmployeeLoan', 'file_upload_log_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function employee_premiums()
    {
        return $this->hasMany('App\EmployeePremium', 'file_upload_log_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function employee_attlogs()
    {
        return $this->hasMany('App\EmployeeAttlog', 'file_upload_log_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

}
