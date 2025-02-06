<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeMasterDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_master_data', function (Blueprint $table) {
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
            $table->string('position_id');
            $table->integer('department_id');
            $table->date('date_employed');
            $table->date('date_resigned')->nullable();
            $table->string('gender');
            $table->string('civil_status');
            $table->string('tin_no')->nullab();
            $table->string('pagibig_no')->nullable();
            $table->string('philhealth_no')->nullable();
            $table->string('sss_no')->nullable();
            $table->string('educ_attain');
            $table->string('school_attended');
            $table->string('course')->nullable();
            $table->boolean('active');
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
        Schema::dropIfExists('employee_master_data');
    }
}
