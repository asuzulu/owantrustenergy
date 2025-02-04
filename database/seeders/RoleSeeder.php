<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Employee']);
        Role::create(['name' => 'Customer']);
        Role::create(['name' => 'Agent']);
    }
}
