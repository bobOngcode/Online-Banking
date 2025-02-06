<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryReconciliationBreakdown extends Model
{
    protected $fillable = [
        'inventory_recon_id',
        'brand',
        'model',
        'product_category',
        'sap_serial',
        'physical_serial',
        'status'
    ];

    public function inventory_recon()
    {
        return $this->hasOne('App\InventoryReconciliation', 'id', 'inventory_recon_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
