<?php

namespace App\Actions\Settings;

use App\Actions\Media\StoreSecureUpload;
use Illuminate\Support\Arr;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UpdateSettings
{
    /**
     * Hanya key di daftar ini yang boleh ditulis dari form Pengaturan.
     * Rahasia integrasi punya alurnya sendiri dan sengaja tidak ada di sini.
     */
    private const WRITABLE = [
        'app_name',
        'app_name_short',
        'app_description',
        'app_color',
        'seo_keywords',
        'youtube_link',
        'instagram_link',
        'tiktok_link',
        'facebook_link',
        'x_twitter_link',
        'github_link',
        'linkedin_link',
        'maintenance_mode',
        'visitor_tracking_enabled',
        'backup_retention',
        'backup_schedule',
        'notification_polling',
        'contact_notification_email',
        'landing_sections',
    ];

    public function __construct(private readonly StoreSecureUpload $uploads) {}

    /**
     * @param  array<string,mixed>  $data
     * @param  array<string,TemporaryUploadedFile|null>  $files
     */
    public function __invoke(array $data, array $files = []): void
    {
        // Whitelist eksplisit, bukan seluruh state komponen.
        $payload = Arr::only($data, self::WRITABLE);

        foreach (['app_favicon', 'app_background_login_image', 'seo_og_image'] as $key) {
            $file = $files[$key] ?? null;

            if (! $file instanceof TemporaryUploadedFile) {
                continue;
            }

            $old = settings($key);
            $payload[$key] = $this->uploads->image($file, 'settings');

            if ($old) {
                $this->uploads->delete($old);
            }
        }

        if (($files['app_logo'] ?? null) instanceof TemporaryUploadedFile) {
            $old = settings('app_logo');
            $payload['app_logo'] = [$this->uploads->image($files['app_logo'], 'settings')];

            foreach ((array) $old as $path) {
                $this->uploads->delete($path);
            }
        }

        settings()->set($payload);
    }
}
