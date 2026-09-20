<?php

/*
|--------------------------------------------------------------------------
| Skema pengaturan aplikasi
|--------------------------------------------------------------------------
|
| Tabel `settings` berbentuk key/value, jadi menambah opsi baru cukup
| mendaftarkannya di sini — tidak perlu migrasi.
|
| cast   : bentuk nilai saat dibaca (string, bool, int, float, array)
| default: dipakai bila key belum pernah disimpan
| secret : nilai ditulis terenkripsi dan tidak pernah dikirim utuh ke browser
|
| Kredensial DATABASE sengaja tidak ada di sini: koneksi harus sudah terbuka
| untuk bisa membaca tabel ini.
|
*/

return [

    'cache_key' => 'portfolio.settings.kv',

    'cache_ttl' => 3600,

    'schema' => [

        // Identitas
        'app_name' => ['cast' => 'string', 'default' => 'Portfolio'],
        'app_name_short' => ['cast' => 'string', 'default' => 'PORTFOLIO'],
        'app_description' => ['cast' => 'string', 'default' => null],
        'app_color' => ['cast' => 'string', 'default' => '#38bdf8'],

        // Media
        'app_logo' => ['cast' => 'array', 'default' => []],
        'app_favicon' => ['cast' => 'string', 'default' => null],
        'app_background_login_image' => ['cast' => 'string', 'default' => null],

        // SEO
        'seo_keywords' => ['cast' => 'string', 'default' => null],
        'seo_og_image' => ['cast' => 'string', 'default' => null],

        // Media sosial
        'github_link' => ['cast' => 'string', 'default' => null],
        'linkedin_link' => ['cast' => 'string', 'default' => null],
        'instagram_link' => ['cast' => 'string', 'default' => null],
        'x_twitter_link' => ['cast' => 'string', 'default' => null],
        'youtube_link' => ['cast' => 'string', 'default' => null],
        'tiktok_link' => ['cast' => 'string', 'default' => null],
        'facebook_link' => ['cast' => 'string', 'default' => null],

        // Perilaku situs
        'maintenance_mode' => ['cast' => 'bool', 'default' => false],
        'visitor_tracking_enabled' => ['cast' => 'bool', 'default' => true],
        'landing_sections' => ['cast' => 'array', 'default' => []],

        // Backup & notifikasi
        'backup_retention' => ['cast' => 'int', 'default' => 10],
        'backup_schedule' => ['cast' => 'string', 'default' => 'daily'],
        'notification_polling' => ['cast' => 'int', 'default' => 30],
        'contact_notification_email' => ['cast' => 'string', 'default' => null],

        // Email
        'mail_mailer' => ['cast' => 'string', 'default' => null],
        'mail_host' => ['cast' => 'string', 'default' => null],
        'mail_port' => ['cast' => 'int', 'default' => null],
        'mail_username' => ['cast' => 'string', 'default' => null, 'secret' => true],
        'mail_password' => ['cast' => 'string', 'default' => null, 'secret' => true],
        'mail_encryption' => ['cast' => 'string', 'default' => 'tls'],
        'mail_from_address' => ['cast' => 'string', 'default' => null],
        'mail_from_name' => ['cast' => 'string', 'default' => null],

        // reCAPTCHA v3
        'recaptcha_enabled' => ['cast' => 'bool', 'default' => false],
        'recaptcha_site_key' => ['cast' => 'string', 'default' => null, 'secret' => true],
        'recaptcha_secret' => ['cast' => 'string', 'default' => null, 'secret' => true],
        'recaptcha_threshold' => ['cast' => 'float', 'default' => 0.5],

        // API pihak ketiga
        'deepl_api_key' => ['cast' => 'string', 'default' => null, 'secret' => true],
        'fontawesome_token' => ['cast' => 'string', 'default' => null, 'secret' => true],
    ],
];
