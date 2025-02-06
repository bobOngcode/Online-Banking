<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeAttlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_attlogs', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->bigInteger('user_id')->unsigned();
            $table->dateTime('date_time');
            $table->integer('device_id');
            $table->integer('status');
            $table->integer('verifying_type');
            $table->integer('work_code');
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
        Schema::dropIfExists('employee_attlogs');
    }
}
