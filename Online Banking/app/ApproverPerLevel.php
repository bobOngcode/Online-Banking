<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApproverPerLevel extends Model
{   

    protected $fillable = ['module_id', 'level', 'num_of_approvers'];

    public function access_chart_user_maps()
    {
        return $this->hasMany('App\AccessChartUserMap', 'access_chart_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
