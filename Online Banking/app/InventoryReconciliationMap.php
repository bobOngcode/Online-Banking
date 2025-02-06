<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryReconciliationMap extends Model
{
    protected $fillable = [
        'inventory_recon_id',
        'user_id',
        'brand',
        'model',
        'product_category',
        'serial',
        'quantity',
        'inventory_type',
    ];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function inventory_recon()
    {
        return $this->hasOne('App\InventoryReconciliation', 'id', 'inventory_recon_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

}
