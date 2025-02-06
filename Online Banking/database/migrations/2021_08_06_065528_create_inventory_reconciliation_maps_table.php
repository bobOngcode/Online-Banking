<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryReconciliationMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_reconciliation_maps', function (Blueprint $table) {
            $table->id();
            $table->integer('inventory_recon_id');
            $table->integer('user_id');
            $table->string('brand');
            $table->string('model');
            $table->string('product_category')->nullable();
            $table->string('serial')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('inventory_type');
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
        Schema::dropIfExists('inventory_reconciliation_maps');
    }
}
