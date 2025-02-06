<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTacticalRequisitionAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tactical_requisition_attachments', function (Blueprint $table) {
            $table->id();
            $table->integer('tactical_requisition_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->string('title');
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
        Schema::dropIfExists('tactical_requisition_attachments');
    }
}
