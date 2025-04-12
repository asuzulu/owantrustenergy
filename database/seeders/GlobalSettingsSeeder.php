<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GlobalSetting;

class GlobalSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key'   => 'allow_customers',
                'label' => 'Allow Customers',
                'value' => false,
            ],
            [
                'key'   => 'allow_employees',
                'label' => 'Allow Employees',
                'value' => false,
            ],
            [
                'key'   => 'allow_agents',
                'label' => 'Allow Agents',
                'value' => false,
            ],
            [
                'key'   => 'allow_drivers',
                'label' => 'Allow Drivers',
                'value' => false,
            ],
        ];

        foreach ($settings as $setting) {
            GlobalSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
