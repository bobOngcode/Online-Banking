<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{   

    protected $fillable = [
        'branch_id',
        'whse_code',
        'user_id',
        'brand_id',
        'model',
        'product_category_id',
        'serial',
        'quantity',
        'inventory_type',
        'inventory_group',
        'file_upload_log_id',
    ];

    public function brand()
    {
        return $this->hasOne('App\Brand', 'id', 'brand_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function product_category()
    {
        return $this->hasOne('App\ProductCategory', 'id', 'product_category_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
