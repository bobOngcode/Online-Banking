<?php

use Illuminate\Database\Seeder;
use App\AccessLevel;

class AccessLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AccessLevel::create(['level' => 4]);
    }
}
