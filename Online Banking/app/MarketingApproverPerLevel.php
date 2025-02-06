<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketingApproverPerLevel extends Model
{
    protected $fillable = ['marketing_event_id', 'level', 'num_of_approvers'];

}
