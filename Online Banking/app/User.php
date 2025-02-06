<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'branch_id', 'position_id', 'active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function e_signature()
    {
        return $this->hasOne('App\UserSignature', 'user_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }


    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }


    public function position()
    {
        return $this->hasOne('App\Position', 'id', 'position_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }


    public function access_chart_user_maps () {
        return $this->belongsToMany('App\AccessChart', 'access_chart_user_maps', 'user_id', 'access_chart_id');
    }


    public function online_banking()
    {   
        return $this->hasMany('App\OnlineBank', 'user_id','id');
        //                 ( <Model>, <id_of_specified_Model>, id_of_this_model>  )
    }
 
}
