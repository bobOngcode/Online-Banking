<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnlineBankingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('online_banking', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('access_module_id');
            $table->string('formtype');
            $table->timestamp('submitted_at');
            $table->string('company');
            $table->string('position');
            $table->string('name');
            $table->date('bdate')->nullable();
            $table->string('email')->nullable();
            $table->string('contact')->nullable();
            $table->string('tin')->nullable();
            $table->string('sss')->nullable();
            $table->string('philhealth')->nullable();
            $table->string('update_type')->nullable();
            $table->string('from_acct')->nullable();
            $table->string('to_acct')->nullable();
            $table->string('bank')->nullable();
            $table->string('auth_id')->nullable();
            $table->string('bank_name');
            $table->string('acct_no');
            $table->string('check_series')->nullable();
            $table->string('amount_limit')->nullable();
            $table->string('allowed_access1')->nullable();
            $table->string('allowed_access2')->nullable();
            $table->date('date_started')->nullable();
            $table->date('date_ended')->nullable();
            $table->timestamps();
            $table->string('status');
            $table->timestamp('date_approve')->nullable();
            $table->text('remarks')->nullable();
            
            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('online_banking');
    }
}
