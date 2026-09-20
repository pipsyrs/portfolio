<?php

namespace App\Livewire\Dashboard\Settings;

use App\Livewire\Concerns\InteractsWithToasts;
use App\Services\RecaptchaService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Integrations extends Component
{
    use InteractsWithToasts;

    #[Locked]
    public string $section = 'email';

    public string $mail_mailer = 'smtp';

    public string $mail_host = '';

    public ?int $mail_port = null;

    public string $mail_encryption = 'tls';

    public string $mail_from_address = '';

    public string $mail_from_name = '';

    public bool $recaptcha_enabled = false;

    public float $recaptcha_threshold = 0.5;

    /**
     * Rahasia tidak pernah dikirim utuh ke browser. Field ini mulai kosong:
     * kosong berarti "biarkan nilai lama", terisi berarti "ganti".
     *
     * @var array<string,string>
     */
    public array $secrets = [
        'mail_username' => '',
        'mail_password' => '',
        'recaptcha_site_key' => '',
        'recaptcha_secret' => '',
        'deepl_api_key' => '',
        'fontawesome_token' => '',
    ];

    public string $testEmailTo = '';

    #[Locked]
    public ?string $clearingSecret = null;

    public const MAILERS = ['smtp' => 'SMTP', 'log' => 'Log (uji coba)', 'array' => 'Array (nonaktif)'];

    public const ENCRYPTIONS = ['tls' => 'TLS', 'ssl' => 'SSL'];

    public function mount(string $section = 'email'): void
    {
        $this->section = $section;

        $this->mail_mailer = (string) (settings('mail_mailer') ?: config('mail.default', 'smtp'));
        $this->mail_host = (string) settings('mail_host');
        $this->mail_port = settings('mail_port') ?: null;
        $this->mail_encryption = (string) settings('mail_encryption');
        $this->mail_from_address = (string) settings('mail_from_address');
        $this->mail_from_name = (string) settings('mail_from_name');

        $this->recaptcha_enabled = (bool) settings('recaptcha_enabled');
        $this->recaptcha_threshold = (float) (settings('recaptcha_threshold') ?: 0.5);

        $this->testEmailTo = (string) (auth()->user()?->email ?? '');
    }

    protected function rules(): array
    {
        return [
            'mail_mailer' => ['required', 'string', 'in:'.implode(',', array_keys(self::MAILERS))],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'mail_from_address' => ['nullable', 'email:filter', 'max:150'],
            'mail_from_name' => ['nullable', 'string', 'max:100'],
            'recaptcha_enabled' => ['boolean'],
            'recaptcha_threshold' => ['required', 'numeric', 'min:0', 'max:1'],
            'secrets.*' => ['nullable', 'string', 'max:500'],
            'testEmailTo' => ['nullable', 'email:filter', 'max:150'],
        ];
    }

    protected function messages(): array
    {
        return [
            'mail_port.integer' => 'Port harus berupa angka.',
            'mail_from_address.email' => 'Alamat pengirim harus email yang valid.',
            'recaptcha_threshold.min' => 'Ambang batas harus antara 0 dan 1.',
            'recaptcha_threshold.max' => 'Ambang batas harus antara 0 dan 1.',
            'testEmailTo.email' => 'Alamat tujuan uji harus email yang valid.',
        ];
    }

    public function saveMail(): void
    {
        $this->persist('saveMail', [
            'mail_mailer', 'mail_host', 'mail_port', 'mail_encryption',
            'mail_from_address', 'mail_from_name',
        ], ['mail_username', 'mail_password']);
    }

    public function saveRecaptcha(): void
    {
        $this->persist('saveRecaptcha', ['recaptcha_enabled', 'recaptcha_threshold'], [
            'recaptcha_site_key', 'recaptcha_secret',
        ]);
    }

    public function saveApiKeys(): void
    {
        $this->persist('saveApiKeys', [], ['deepl_api_key', 'fontawesome_token']);
    }

    /**
     * @param  list<string>  $keys  key biasa yang ditulis
     * @param  list<string>  $secretKeys  rahasia milik form ini
     */
    private function persist(string $form, array $keys, array $secretKeys = []): void
    {
        $this->authorize('owner');

        $rules = Arr::only($this->rules(), $keys);

        if ($secretKeys !== []) {
            $rules['secrets.*'] = $this->rules()['secrets.*'];
        }

        $validated = $this->validate($rules);
        $payload = Arr::only($validated, $keys);

        // Rahasia hanya ditulis bila field-nya benar-benar diisi ulang; kosong
        // berarti "pertahankan nilai lama", bukan "hapus".
        foreach ($secretKeys as $field) {
            if (filled($this->secrets[$field] ?? null)) {
                $payload[$field] = trim($this->secrets[$field]);
            }
        }

        settings()->set($payload);

        $this->reset('secrets');
        $this->toastSuccess('Konfigurasi integrasi tersimpan.');
        $this->dispatch('form-saved', form: $form);
    }

    public function confirmClearSecret(string $field): void
    {
        $this->clearingSecret = settings()->isSecret($field) ? $field : null;
    }

    public function cancelClearSecret(): void
    {
        $this->clearingSecret = null;
    }

    public function clearSecret(): void
    {
        $this->authorize('owner');

        $field = $this->clearingSecret;
        $this->clearingSecret = null;

        if ($field === null || ! settings()->isSecret($field)) {
            return;
        }

        settings()->forget($field);

        $this->toastInfo('Nilai dikosongkan. Sekarang memakai nilai dari .env bila ada.');
    }

    /**
     * Mengirim email uji memakai konfigurasi yang TERSIMPAN, bukan yang sedang
     * diketik, supaya hasilnya mencerminkan keadaan aplikasi sebenarnya.
     */
    public function testMail(): void
    {
        $this->authorize('owner');

        $this->validateOnly('testEmailTo');

        $to = $this->testEmailTo ?: auth()->user()?->email;

        if (blank($to)) {
            $this->toastError('Isi alamat tujuan lebih dulu.');

            return;
        }

        try {
            Mail::raw(
                "Ini email uji dari dashboard portfolio.\n\nJika Anda menerimanya, konfigurasi SMTP sudah benar.",
                fn ($message) => $message->to($to)->subject('Uji konfigurasi email'),
            );

            $this->toastSuccess('Email uji terkirim ke '.$to.'.');
        } catch (\Throwable $e) {
            Log::warning('SMTP test failed', ['error' => $e->getMessage()]);
            $this->toastError(Str::limit($e->getMessage(), 180), 'Gagal mengirim');
        }
    }

    public function testRecaptcha(RecaptchaService $recaptcha): void
    {
        $this->authorize('owner');

        if (! $recaptcha->isEnabled()) {
            $this->toastWarning('reCAPTCHA belum aktif atau kuncinya belum lengkap.');

            return;
        }

        // Token sengaja tidak sah: yang diuji adalah apakah secret diterima
        // Google, bukan apakah pengguna ini manusia.
        $result = $recaptcha->check('token-uji-tidak-sah', 'login');

        str_contains($result['message'], 'invalid-input-secret')
            ? $this->toastError('Secret reCAPTCHA ditolak Google. Periksa kembali kuncinya.')
            : $this->toastSuccess('Secret diterima Google. Konfigurasi reCAPTCHA terbaca.');
    }

    public function testDeepl(): void
    {
        $this->authorize('owner');

        $key = config('services.deepl.key');

        if (blank($key)) {
            $this->toastWarning('DeepL API key belum diisi.');

            return;
        }

        try {
            $host = str_ends_with((string) $key, ':fx') ? 'api-free.deepl.com' : 'api.deepl.com';

            $response = Http::timeout(8)
                ->withHeaders(['Authorization' => 'DeepL-Auth-Key '.$key])
                ->get('https://'.$host.'/v2/usage');

            if (! $response->successful()) {
                $this->toastError('DeepL menolak kunci ini (HTTP '.$response->status().').');

                return;
            }

            $usage = $response->json();
            $used = number_format((int) ($usage['character_count'] ?? 0));
            $limit = number_format((int) ($usage['character_limit'] ?? 0));

            $this->toastSuccess('Terhubung. Pemakaian: '.$used.' dari '.$limit.' karakter.');
        } catch (\Throwable $e) {
            $this->toastError(Str::limit($e->getMessage(), 180), 'DeepL tidak terjangkau');
        }
    }

    public function testDatabase(): void
    {
        $this->authorize('owner');

        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $ms = round((microtime(true) - $start) * 1000, 1);

            $this->toastSuccess('Koneksi database sehat ('.$ms.' ms).');
        } catch (\Throwable $e) {
            $this->toastError(Str::limit($e->getMessage(), 180), 'Koneksi gagal');
        }
    }

    /**
     * Informasi koneksi bersifat baca-saja: kredensial database mustahil
     * disimpan di dalam database yang kredensialnya sedang dibaca.
     *
     * @return array<string,string>
     */
    #[Computed]
    public function databaseInfo(): array
    {
        $name = config('database.default');
        $config = config('database.connections.'.$name);

        $info = [
            'Koneksi' => $name,
            'Driver' => $config['driver'] ?? '-',
            'Host' => ($config['host'] ?? '-').':'.($config['port'] ?? '-'),
            'Database' => $config['database'] ?? '-',
            'Pengguna' => $config['username'] ?? '-',
        ];

        try {
            $info['Versi server'] = (string) DB::selectOne('select version() as v')->v;

            $size = DB::selectOne(
                'select round(sum(data_length + index_length) / 1024 / 1024, 2) as mb
                 from information_schema.tables where table_schema = ?',
                [$config['database']],
            );

            $info['Ukuran'] = ($size->mb ?? 0).' MB';
            $info['Status'] = 'Terhubung';
        } catch (\Throwable $e) {
            $info['Status'] = 'Gagal: '.Str::limit($e->getMessage(), 80);
        }

        return $info;
    }

    public function render()
    {
        return view('livewire.dashboard.settings.integrations', [
            'mailers' => self::MAILERS,
            'encryptions' => self::ENCRYPTIONS,
        ]);
    }
}
