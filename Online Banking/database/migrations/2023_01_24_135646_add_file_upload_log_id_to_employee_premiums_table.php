<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileUploadLogIdToEmployeePremiumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_premiums', function (Blueprint $table) {
            $table->integer('file_upload_log_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_premiums', function (Blueprint $table) {
            $table->dropColumn('file_upload_log_id');
        });
    }
}
