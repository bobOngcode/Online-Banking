<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Signature extends Model
{
    //
    protected $table = 'user_signature';
    protected $fillable = [
        'user_id',
        'e_signature',
        'file_name',
        'file_path',
        'file_type',
    ];


    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
        //                     (<Model>, <id_of_specified_Model>, id_of_this_model>)
    }

}
