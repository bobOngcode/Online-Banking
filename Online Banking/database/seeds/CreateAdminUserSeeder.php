<?php

use Illuminate\Database\Seeder;
use App\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
        	'name' => 'Administrator', 
            'email' => 'admin@webportal.ac',
        	'password' => bcrypt('M15@2dwin0n7y'),
            'active' => 'Y',
            'branch_id' => 1,
        ]);

        $role = Role::firstOrCreate(['name' => 'Administrator']);

        $permissions = Permission::pluck('id','id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
    }
}
