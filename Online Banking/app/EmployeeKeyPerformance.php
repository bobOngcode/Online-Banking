<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeKeyPerformance extends Model
{
    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'grade',
    ];
}
