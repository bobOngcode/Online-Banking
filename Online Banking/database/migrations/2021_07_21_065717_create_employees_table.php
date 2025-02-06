<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->string('employee_code');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->date('dob');
            $table->string('address');
            $table->string('contact');
            $table->string('email')->nullable();
            $table->string('class');
            $table->string('rank');
            $table->string('department');
            $table->string('cost_center_code');
            $table->string('job_description');
            $table->date('date_employed');
            $table->string('gender');
            $table->string('civil_status');
            $table->string('tax_status');
            $table->string('tin_no');
            $table->string('tax_branch_code');
            $table->string('pagibig_no');
            $table->string('philhealth_no');
            $table->string('sss_no');
            $table->string('time_schedule');
            $table->string('restday');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
