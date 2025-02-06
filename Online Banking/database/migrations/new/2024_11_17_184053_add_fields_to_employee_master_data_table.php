<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToEmployeeMasterDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_master_data', function (Blueprint $table) {
            $table->string('school_year')->nullable();
            $table->string('employment_type')->nullable();
            $table->date('regularization_date')->nullable();
            $table->date('last_day_of_work')->nullable();
            $table->string('reason_of_resignation')->nullable();
            $table->boolean('coe_is_issued')->nullable();
            $table->boolean('last_pay_is_issued')->nullable();
            $table->string('compliance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_master_data', function (Blueprint $table) {
            $table->dropColumn('school_year');
            $table->dropColumn('employment_type');
            $table->dropColumn('regularization_date');
            $table->dropColumn('last_day_of_work');
            $table->dropColumn('reason_of_resignation');
            $table->dropColumn('coe_is_issued');
            $table->dropColumn('last_pay_is_issued');
            $table->dropColumn('compliance');
        });
    }
}
