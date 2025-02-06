<?php

use Illuminate\Database\Seeder;
use App\SapDatabase;

class SAPDatabaseSeeder extends Seeder
{

    public function run()
    {
        $databases = [
            'AddessaNov2024',
            'AppliantechNov2024',
            'EasyToOwnNov2024',
            'ElectroloopNov2024',
            'OutexcelNov2024',
            'PanApplianceNov2024',
            'SteadfordNov2024',
            'ThreathonsNov2024',
            'MetroIlocosNov2024',
        ];

        foreach ($databases as $key => $database) {
            SapDatabase::firstOrCreate([
                'server' => '192.168.1.25',
                'database' => $database,
                'username' => 'sapprog105',
                'password' => Crypt::encrypt('105*Prog'),
                'active' => 1,
            ]);
        }
    }
}
