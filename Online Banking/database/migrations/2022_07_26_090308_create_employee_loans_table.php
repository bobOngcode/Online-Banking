<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('position');
            $table->string('loan_type');
            $table->string('description');
            $table->string('ref_no');
            $table->date('date_granted');
            $table->decimal('principal_loan', 12, 2);
            $table->decimal('interest', 12, 2);
            $table->decimal('total_loan', 12, 2);
            $table->integer('terms');
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('monthly_amortization', 12, 2);
            $table->integer('total_months_paid');
            $table->decimal('total_paid', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->integer('remaining_term');
            $table->string('or_number');
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
        Schema::dropIfExists('employee_loans');
    }
}
