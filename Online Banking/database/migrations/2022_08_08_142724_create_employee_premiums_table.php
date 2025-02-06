<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeePremiumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_premiums', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->date('dob');
            $table->date('date_hired');
            $table->string('or_number');
            $table->string('sss_no');
            $table->decimal('sss_ee', 12, 2);
            $table->decimal('sss_er', 12, 2);
            $table->decimal('sss_ec', 12, 2);
            $table->decimal('sss_total', 12, 2);
            $table->string('philhealth_no');
            $table->decimal('philhealth_ee', 12, 2);
            $table->decimal('philhealth_er', 12, 2);
            $table->decimal('philhealth_total', 12, 2);
            $table->string('pagibig_no');
            $table->decimal('pagibig_ee', 12, 2);
            $table->decimal('pagibig_er', 12, 2);
            $table->decimal('pagibig_total', 12, 2);
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
        Schema::dropIfExists('employee_premiums');
    }
}
