<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeMasterDataFile extends Model
{
    protected $fillable = [
        'employee_id',
        'file_name',
        'file_path',
        'file_type',
        'title',
    ];
}
