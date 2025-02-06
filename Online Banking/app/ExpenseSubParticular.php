<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpenseSubParticular extends Model
{
    protected $fillable = ['expense_particular_id', 'description', 'active'];

    public function expense_particular()
    {   
        return $this->belongsTo('App\ExpenseParticular', 'id','expense_particular_id');
        //                 ( <Model>, <id_of_specified_Model>, id_of_this_model>  )
    }
}
