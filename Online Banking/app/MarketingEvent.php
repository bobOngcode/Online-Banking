<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketingEvent extends Model
{
    protected $fillable = ['event_name', 'active', 'attachment_required'];

    public function expense_particulars()
    {
        return $this->hasMany('App\ExpenseParticular', 'marketing_event_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function users_map () {
        return $this->belongsToMany('App\User', 'marketing_event_user_maps', 'marketing_event_id', 'user_id');
    }

    public function marketing_event_user_maps()
    {
        return $this->hasMany('App\MarketingEventUserMap', 'marketing_event_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function approver_per_level()
    {
        return $this->hasMany('App\MarketingApproverPerLevel', 'marketing_event_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
