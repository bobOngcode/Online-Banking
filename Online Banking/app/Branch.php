<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{

    protected $fillable = ['code', 'name', 'company_id'];

    public function employees()
    {
        return $this->hasMany('App\Employee', 'branch_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function employee_loans()
    {
        return $this->hasMany('App\EmployeeLoan', 'branch_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function employee_premiums()
    {
        return $this->hasMany('App\EmployeePremium', 'branch_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function employee_attlogs()
    {
        return $this->hasMany('App\EmployeeAttlog', 'branch_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function company()
    {
        return $this->hasOne('App\Company', 'id', 'company_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function file_upload_logs()
    {
        return $this->hasMany('App\FileUploadLog', 'branch_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function sap_db_branches()
    {
        return $this->hasMany('App\SapDbBranch', 'sap_database_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function whse_codes()
    {
        return $this->hasMany('App\WarehouseCode', 'branch', 'name');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function inventory_reconciliations()
    {
        return $this->hasMany('App\InventoryReconciliation', 'branch_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function required_employees()
    {
        return $this->hasMany('App\RequiredEmployeeMap', 'branch_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

}
