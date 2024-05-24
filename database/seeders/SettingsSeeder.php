<?php

namespace Database\Seeders;

use App\Models\Setting;
use Kdabrow\SeederOnce\SeederOnce;

class SettingsSeeder extends SeederOnce
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            "withdraw" => '{"fee": 100, "withdraw_limit": 5000000, "daily_limit": 5000000}',
        ];

        foreach ($settings as $key => $setting) {
            Setting::create([
                'name' => $key,
                'value' => $setting,
            ]);
        }
    }
}
