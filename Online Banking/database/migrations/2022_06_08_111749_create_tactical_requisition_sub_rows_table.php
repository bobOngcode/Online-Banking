<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTacticalRequisitionSubRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tactical_requisition_sub_rows', function (Blueprint $table) {
            $table->id();
            $table->integer('tactical_requisition_row_id');
            $table->integer('line_num');
            $table->string('description')->nullable();
            $table->string('resource_person')->nullable();
            $table->string('contact')->nullable();
            $table->integer('qty')->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
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
        Schema::dropIfExists('tactical_requisition_sub_rows');
    }
}
