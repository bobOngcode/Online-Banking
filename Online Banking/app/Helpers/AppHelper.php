<?php
namespace App\Helpers;
use Config;

class AppHelper
{
    public function configDatabaseConnection($value)
    {
        return Config::set('database.connections.'.$value['connection'], array(
            'driver' => $value['driver'],
            'host' => $value['host'],
            'port' => $value['port'],
            'database' => $value['database'],
            'username' => $value['username'],
            'password' => $value['password'],   
        ));
    }

    public static function instance()
    {
        return new AppHelper();
    }
    
}