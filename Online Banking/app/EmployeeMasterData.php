<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeMasterData extends Model
{
    protected $fillable = [
        'branch_id',
        'employee_code',
        'last_name',
        'first_name',
        'middle_name',
        'dob',
        'address',
        'contact',
        'email',
        'position_id',
        'department_id',
        'date_employed',
        'date_resigned',
        'gender',
        'civil_status',
        'tin_no',
        'pagibig_no',
        'philhealth_no',
        'sss_no',
        'educ_attain',
        'school_year',
        'school_attended',
        'course',
        'employment_type',
        'regularization_date',
        'last_day_of_work',
        'reason_of_resignation',
        'coe_is_issued',
        'last_pay_is_issued',
        'compliance',
        'active',
    ];

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

    public function department()
    {
        return $this->hasOne('App\Department', 'id', 'department_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function files()
    {
        return $this->hasMany('App\EmployeeMasterDataFile', 'employee_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function key_performances()
    {
        return $this->hasMany('App\EmployeeKeyPerformance', 'employee_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function disciplinary_measures()
    {
        return $this->hasOne('App\EmployeeDisciplinaryMeasures', 'employee_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function employment_histories()
    {
        return $this->hasOne('App\EmployeeEmploymentHistory', 'employee_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function merit_histories()
    {
        return $this->hasOne('App\EmployeeMeritHistory', 'employee_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function trainings()
    {
        return $this->hasOne('App\EmployeeTraining', 'employee_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
