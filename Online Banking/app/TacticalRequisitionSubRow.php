<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TacticalRequisitionSubRow extends Model
{
    protected $fillable = [
        'tactical_requisition_id',
        'line_num',
        'description', 
        'resource_person',
        'contact',
        'qty',
        'unit_cost',
        'amount',
    ];

    public function tactical_row()
    {   
        return $this->belongsTo('App\TacticalRequisitionRow', 'id','tactical_requisition_row _id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model>  )
    }
}
