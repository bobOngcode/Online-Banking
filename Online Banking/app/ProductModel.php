<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $fillable = ['name', 'active'];

    public function product()
    {   
        return $this->belongsTo('App\Product', 'model_id','id');
        //                 ( <Model>, <id_of_specified_Model>, id_of_this_model>  )
    }
}
