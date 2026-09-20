<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Menulis nilai awal untuk key yang belum pernah diisi. Key yang sudah ada
     * tidak ditimpa, sehingga seeder aman dijalankan ulang.
     */
    public function run(): void
    {
        $defaults = [
            'app_name' => 'Portfolio',
            'app_name_short' => 'PORTFOLIO',
            'app_color' => '#38bdf8',
            'maintenance_mode' => false,
            'visitor_tracking_enabled' => true,
            'backup_retention' => 10,
            'backup_schedule' => 'daily',
            'notification_polling' => 30,
            'mail_encryption' => 'tls',
            'recaptcha_enabled' => false,
            'recaptcha_threshold' => 0.5,
        ];

        $existing = Setting::query()->pluck('key')->all();

        settings()->set(array_diff_key($defaults, array_flip($existing)));
    }
}
