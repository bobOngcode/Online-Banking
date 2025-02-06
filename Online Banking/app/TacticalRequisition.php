<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TacticalRequisition extends Model
{
    protected $fillable = [
        'branch_id', 
        'user_id', 
        'marketing_event_id',
        'venue',
        'sponsor',
        'period_from',
        'period_to',
        'operating_from',
        'operating_to',
        'prev_period_from',
        'prev_period_to',
        'prev_venue',
        'prev_sponsor',
        'prev_quota',
        'prev_total_sales',
        'prev_sales_achievement',
        'prev_total_expense',
        'remarks',
    ];

    public function tactical_rows()
    {   
        return $this->hasMany('App\TacticalRequisitionRow', 'tactical_requisition_id', 'id');
        //(<Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function tactical_attachments()
    {   
        return $this->hasMany('App\TacticalRequisitionAttachment', 'tactical_requisition_id', 'id');
        //(<Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function approved_logs()
    {   
        return $this->hasMany('App\ApprovedLog', 'document_id', 'id');
        //(<Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function activity_logs()
    {   
        return $this->hasMany('App\TacticalActivityLog', 'document_id', 'id');
        //(<Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //(<Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
        //(<Model>, <id_of_specified_Model>, <id_of_this_model> )  
    }

    public function marketing_event()
    {
        return $this->hasOne('App\MarketingEvent', 'id', 'marketing_event_id');
        //(<Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
