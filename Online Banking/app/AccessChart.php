<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessChart extends Model
{

    public function users_map () {
        return $this->belongsToMany('App\User', 'access_chart_user_maps', 'access_chart_id', 'user_id');
    }

    public function access_chart_user_maps()
    {
        return $this->hasMany('App\AccessChartUserMap', 'access_chart_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    
    public function access_module()
    {
        return $this->hasOne('App\AccessModule', 'id', 'access_for');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }


    public function approver_per_level()
    {
        return $this->hasMany('App\ApproverPerLevel', 'module_id', 'access_for');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
