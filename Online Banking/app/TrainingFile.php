<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainingFile extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'title',
        'description',
    ];

    public function file_has_permissions()
    {
        return $this->hasMany('App\FileHasPermission', 'training_file_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
