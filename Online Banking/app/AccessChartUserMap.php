<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessChartUserMap extends Model
{
    public function user () {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
      
    public function access_chart () {
        return $this->belongsTo('App\AccessChart', 'access_chart_id', 'id');
    }
}
