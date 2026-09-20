<?php

namespace App\Livewire\Dashboard\Settings;

use App\Actions\Settings\UpdateSettings;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Rules\SafeImageUpload;
use Illuminate\Support\Arr;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.dashboard')]
#[Title('Pengaturan')]
class Edit extends Component
{
    use InteractsWithToasts, WithFileUploads;

    public string $tab = 'aplikasi';

    public string $app_name = '';

    public string $app_name_short = '';

    public string $app_description = '';

    public string $app_color = '#38bdf8';

    public string $seo_keywords = '';

    public string $youtube_link = '';

    public string $instagram_link = '';

    public string $tiktok_link = '';

    public string $facebook_link = '';

    public string $x_twitter_link = '';

    public string $github_link = '';

    public string $linkedin_link = '';

    public bool $maintenance_mode = false;

    public bool $visitor_tracking_enabled = true;

    public int $backup_retention = 10;

    public string $backup_schedule = 'daily';

    public int $notification_polling = 30;

    public string $contact_notification_email = '';

    /** @var array<string,bool> */
    public array $landing_sections = [];

    public ?TemporaryUploadedFile $app_logo = null;

    public ?TemporaryUploadedFile $app_favicon = null;

    public ?TemporaryUploadedFile $app_background_login_image = null;

    public ?TemporaryUploadedFile $seo_og_image = null;

    public const SECTIONS = [
        'hero' => 'Hero',
        'about' => 'About',
        'tech' => 'Tech Stack',
        'specialis' => 'Spesialisasi',
        'careers' => 'Karier',
        'certifications' => 'Sertifikasi',
        'projects' => 'Projects',
        'contact' => 'Kontak',
    ];

    public const SCHEDULES = [
        'hourly' => 'Setiap jam',
        'twice_daily' => 'Dua kali sehari',
        'daily' => 'Harian (01:00)',
        'weekly' => 'Mingguan (Senin 01:00)',
        'monthly' => 'Bulanan (tanggal 1)',
    ];

    /**
     * Tab dikelompokkan supaya navigasinya terbaca sebagai beberapa bagian
     * pendek. Tab di grup Integrasi isinya dirender komponen Integrations.
     */
    public const TAB_GROUPS = [
        'Situs' => [
            'aplikasi' => ['label' => 'Identitas', 'icon' => 'fa-solid fa-circle-info'],
            'tampilan' => ['label' => 'Tampilan', 'icon' => 'fa-solid fa-palette'],
            'media' => ['label' => 'Logo & Media', 'icon' => 'fa-solid fa-images'],
            'seo' => ['label' => 'SEO', 'icon' => 'fa-solid fa-magnifying-glass-chart'],
            'sosial' => ['label' => 'Media Sosial', 'icon' => 'fa-solid fa-share-nodes'],
        ],
        'Integrasi' => [
            'email' => ['label' => 'Email', 'icon' => 'fa-solid fa-envelope'],
            'integrasi' => ['label' => 'API & Keamanan', 'icon' => 'fa-solid fa-key'],
            'database' => ['label' => 'Database', 'icon' => 'fa-solid fa-database'],
        ],
        'Sistem' => [
            'sistem' => ['label' => 'Perilaku Situs', 'icon' => 'fa-solid fa-toggle-on'],
            'backup' => ['label' => 'Backup & Notifikasi', 'icon' => 'fa-solid fa-clock-rotate-left'],
        ],
    ];

    /** Tab yang isinya dirender komponen Integrations. */
    public const INTEGRATION_TABS = ['email', 'integrasi', 'database'];

    private const SOCIALS = ['youtube', 'instagram', 'tiktok', 'facebook', 'x_twitter', 'github', 'linkedin'];

    public function mount(): void
    {
        $this->app_name = (string) settings('app_name');
        $this->app_name_short = (string) settings('app_name_short');
        $this->app_description = (string) settings('app_description');
        $this->app_color = settings()->color();
        $this->seo_keywords = (string) settings('seo_keywords');

        foreach (self::SOCIALS as $social) {
            $field = $social.'_link';
            $this->{$field} = (string) settings($field);
        }

        $this->maintenance_mode = (bool) settings('maintenance_mode');
        $this->visitor_tracking_enabled = (bool) settings('visitor_tracking_enabled');
        $this->backup_retention = (int) settings('backup_retention');
        $this->backup_schedule = (string) settings('backup_schedule');
        $this->notification_polling = (int) settings('notification_polling');
        $this->contact_notification_email = (string) settings('contact_notification_email');

        $stored = (array) settings('landing_sections');

        foreach (array_keys(self::SECTIONS) as $key) {
            $this->landing_sections[$key] = (bool) ($stored[$key] ?? true);
        }
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs())) {
            $this->tab = $tab;
        }
    }

    /**
     * @return array<string,array{label:string,icon:string}>
     */
    private function tabs(): array
    {
        return array_merge(...array_values(self::TAB_GROUPS));
    }

    protected function rules(): array
    {
        $rules = [
            'app_name' => ['required', 'string', 'min:2', 'max:120'],
            'app_name_short' => ['required', 'string', 'min:1', 'max:60'],
            'app_description' => ['nullable', 'string', 'max:500'],
            // Warna disuntikkan ke dalam blok <style>, jadi formatnya dikunci.
            'app_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],

            'maintenance_mode' => ['boolean'],
            'visitor_tracking_enabled' => ['boolean'],
            'backup_retention' => ['required', 'integer', 'min:1', 'max:60'],
            'backup_schedule' => ['required', 'string', 'in:'.implode(',', array_keys(self::SCHEDULES))],
            'notification_polling' => ['required', 'integer', 'min:10', 'max:600'],
            'contact_notification_email' => ['nullable', 'email:filter', 'max:150'],

            'landing_sections' => ['array'],
            'landing_sections.*' => ['boolean'],

            'app_logo' => ['nullable', new SafeImageUpload(2048)],
            'app_favicon' => ['nullable', new SafeImageUpload(1024)],
            'app_background_login_image' => ['nullable', new SafeImageUpload(4096)],
            'seo_og_image' => ['nullable', new SafeImageUpload(2048)],
        ];

        foreach (self::SOCIALS as $social) {
            $rules[$social.'_link'] = ['nullable', 'string', 'max:255', 'url:http,https'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'app_name_short.required' => 'Nama singkat wajib diisi.',
            'app_color.required' => 'Warna utama wajib diisi.',
            'app_color.regex' => 'Warna harus berupa kode heksadesimal, contoh #38bdf8.',
            'backup_retention.min' => 'Minimal menyimpan 1 berkas backup.',
            'backup_retention.max' => 'Maksimal menyimpan 60 berkas backup.',
            'notification_polling.min' => 'Interval polling minimal 10 detik.',
            'notification_polling.max' => 'Interval polling maksimal 600 detik.',
            'contact_notification_email.email' => 'Format email tidak valid.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan per grup
    |--------------------------------------------------------------------------
    |
    | Tiap kartu punya tombol simpannya sendiri, jadi menyentuh satu grup tidak
    | ikut memvalidasi grup lain — pesan error muncul tepat di form yang salah.
    |
    */

    public function saveIdentity(): void
    {
        $this->persist('saveIdentity', ['app_name', 'app_name_short', 'app_description']);
    }

    public function saveColor(): void
    {
        $this->persist('saveColor', ['app_color']);

        $this->dispatch('settings-saved', color: $this->app_color);
    }

    public function saveSections(): void
    {
        $this->persist('saveSections', ['landing_sections', 'landing_sections.*'], ['landing_sections']);
    }

    public function saveMedia(): void
    {
        $this->persist(
            'saveMedia',
            [],
            [],
            ['app_logo', 'app_favicon', 'app_background_login_image'],
        );
    }

    public function saveSeo(): void
    {
        $this->persist('saveSeo', ['seo_keywords'], ['seo_keywords'], ['seo_og_image']);
    }

    public function saveSocial(): void
    {
        $this->persist('saveSocial', array_map(fn ($social) => $social.'_link', self::SOCIALS));
    }

    public function saveBehavior(): void
    {
        $this->persist('saveBehavior', ['maintenance_mode', 'visitor_tracking_enabled']);
    }

    public function saveBackup(): void
    {
        $this->persist('saveBackup', [
            'backup_schedule',
            'backup_retention',
            'notification_polling',
            'contact_notification_email',
        ]);
    }

    /**
     * @param  list<string>  $ruleKeys  aturan yang dipakai (boleh memuat wildcard)
     * @param  list<string>|null  $valueKeys  key yang benar-benar ditulis; null = sama dengan $ruleKeys
     * @param  list<string>  $fileKeys  properti unggahan yang ikut dikirim
     */
    private function persist(string $form, array $ruleKeys, ?array $valueKeys = null, array $fileKeys = []): void
    {
        $this->authorize('owner');

        $validated = $this->validate(Arr::only($this->rules(), array_merge($ruleKeys, $fileKeys)));

        $files = [];

        foreach ($fileKeys as $key) {
            $files[$key] = $this->{$key};
        }

        app(UpdateSettings::class)(
            Arr::only($validated, $valueKeys ?? $ruleKeys),
            $files,
        );

        if ($fileKeys !== []) {
            $this->reset($fileKeys);
        }

        $this->toastSuccess('Pengaturan berhasil disimpan.');

        // Dipakai tombol simpan di form ini untuk kembali bersembunyi.
        $this->dispatch('form-saved', form: $form);
    }

    public function render()
    {
        return view('livewire.dashboard.settings.edit', [
            'sections' => self::SECTIONS,
            'schedules' => self::SCHEDULES,
            'tabGroups' => self::TAB_GROUPS,
        ]);
    }
}
