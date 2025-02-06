<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryReconciliation extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'date_reconciled',
        'status',
        'inventory_group',
        'inventory_type',
        'docdate',
        'whse_code',
    ];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function inventory_recon_maps()
    {
        return $this->hasMany('App\InventoryReconciliationMap', 'inventory_recon_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function inventory_recon_breakdown()
    {
        return $this->hasMany('App\InventoryReconciliationBreakdown', 'inventory_recon_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
