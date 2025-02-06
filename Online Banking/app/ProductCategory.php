<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = ['name', 'active'];

    public function product()
    {   
        return $this->belongsTo('App\Product', 'product_category_id','id');
        //                 ( <Model>, <id_of_specified_Model>, id_of_this_model>  )
    }
}
