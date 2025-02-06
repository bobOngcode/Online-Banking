<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'Inventory Branch',
            'HR Admin',
            'MSS',
            'Audit Admin',
            'Inventory Admin',
         ];
 
         foreach ($roles as $role) {
              Role::create(['name' => $role]);
         }
    }
}
