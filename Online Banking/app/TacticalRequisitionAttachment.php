<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TacticalRequisitionAttachment extends Model
{
    protected $fillable = [
        'tactical_requisition_id',
        'file_name',
        'file_path',
        'file_type',
    ];

    public function tactical_requisition()
    {   
        return $this->belongsTo('App\TacticalRequisition', 'id','tactical_requisition_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model>  )
    }
}
