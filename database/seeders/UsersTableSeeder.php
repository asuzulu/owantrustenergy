<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'first_name' => 'Asuzu',
            'last_name' => 'Zulu',
            'phone_number' => '2405056225',
            'gender' => 'male',
            'street' => '23 cold street',
            'city' => 'Ajah',
            'state_id' => 1, // Adjust this if you have state ids defined
            'bvn' => '12345678901',
            'nin' => '01234567890',
            'email' => 'asuzulu3@gmail.com',
            'password' => bcrypt('Password@1'), // Hashing the password
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
