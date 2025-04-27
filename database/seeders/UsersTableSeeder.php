<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'first_name' => 'Asuzu',
                'last_name' => 'Zulu',
                'phone_number' => '2405056225',
                'gender' => 'male',
                'street' => '23 Cold Street',
                'city' => 'Ajah',
                'state' => 'Lagos',
                'bvn' => '12345789016',
                'nin' => '01234567890',
                'email' => 'asuzulu3@gmail.com',
                'dob' => '1986-04-15',
                'password' => Hash::make('password'),
                'profile_image' => null,
                'position' => 'Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Chris',
                'last_name' => 'Ojo',
                'phone_number' => '08091234567',
                'gender' => 'male',
                'street' => '45 Green Avenue',
                'city' => 'Benin City',
                'state' => 'Edo',
                'bvn' => '23456789013',
                'nin' => '98765432100',
                'email' => 'cojo2492@gmail.com',
                'dob' => '1985-01-01',
                'password' => Hash::make('password'),
                'profile_image' => null,
                'position' => 'Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Set',
                'last_name' => 'Manager',
                'phone_number' => '08033334444',
                'gender' => 'male',
                'street' => '8 Industrial Street',
                'city' => 'Abeokuta',
                'state' => 'Ogun',
                'bvn' => '22223333444',
                'nin' => '54321987654',
                'email' => 'setmanager@gmail.com',
                'dob' => '1982-05-15',
                'password' => Hash::make('password'),
                'profile_image' => null,
                'position' => 'Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
