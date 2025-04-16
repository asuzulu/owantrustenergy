<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('warehouses')->insert([
            'id' => 1,
            'name' => 'Unassigned',
            'address' => 'Unassigned',
            'phone_number' => 'Unassigned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
