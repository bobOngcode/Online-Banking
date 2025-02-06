<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryReconciliationBreakdownsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_reconciliation_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->integer('inventory_recon_id');
            $table->string('brand');
            $table->string('model');
            $table->string('product_category');
            $table->string('sap_serial');
            $table->string('physical_serial')->nullable();
            $table->string('status');
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
        Schema::dropIfExists('inventory_reconciliation_breakdowns');
    }
}
