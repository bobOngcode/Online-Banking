<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpenseParticular extends Model
{
    protected $fillable = ['marketing_event_id', 'description', 'active', 'dynamic'];

    public function marketing_event()
    {   
        return $this->belongsTo('App\ExpenseParticular', 'marketing_event_id','id');
        //                 ( <Model>, <id_of_specified_Model>, id_of_this_model>  )
    }

    public function expense_sub_particulars()
    {
        return $this->hasMany('App\ExpenseSubParticular', 'expense_particular_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
