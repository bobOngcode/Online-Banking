<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TacticalRequisitionRow extends Model
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

    public function tactical_requisition()
    {   
        return $this->belongsTo('App\TacticalRequisition', 'id','tactical_requisition_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model>  )
    }

    public function tactical_sub_rows()
    {   
        return $this->hasMany('App\TacticalRequisitionSubRow', 'tactical_requisition_row_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
