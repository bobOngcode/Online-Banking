<?php

use Illuminate\Database\Seeder;
use App\AccessModule;
use App\AccessChart;

class AccessModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            
            'User Management',
            'Inventory',
            'Employee',
            'Training',
            'Tactical Requisition',
            'Access Chart',
        ];

        foreach ($modules as $module) {
            AccessModule::create(
                [
                    'name' => $module,
                ]
            );
        } 
       
       $access_modules = AccessModule::all();

        foreach ($access_modules as $access_module) {
            AccessChart::create(
                [
                    'name' => $access_module->name,
                    'access_for' => $access_module->id,
                    'max_approval_level' => 4,
                ]
            );
        } 
    }
}
