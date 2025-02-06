<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryReconciliationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->integer('branch_id');
            $table->integer('user_id');
            $table->date('date_reconciled')->nullable();
            $table->string('status');
            $table->string('inventory_group');
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
        Schema::dropIfExists('inventory_reconciliations');
    }
}
