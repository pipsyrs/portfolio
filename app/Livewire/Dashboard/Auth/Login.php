<?php

namespace App\Livewire\Dashboard\Auth;

use App\Http\Middleware\EnforceAbsoluteSessionLifetime;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\User;
use App\Services\RecaptchaService;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Masuk')]
class Login extends Component
{
    use InteractsWithToasts;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    /**
     * Umpan bot. Field ini disembunyikan dari pengguna sungguhan lewat CSS,
     * jadi isi apa pun di dalamnya berarti pengisian otomatis.
     */
    public string $website = '';

    /** Token reCAPTCHA v3, diisi klien sesaat sebelum submit. */
    public string $recaptchaToken = '';

    /** Tahap dua. ID pengguna disimpan di sesi server, bukan di properti
     *  komponen, supaya tidak bisa diubah dari sisi klien. */
    public bool $awaitingTwoFactor = false;

    public string $twoFactorCode = '';

    public bool $useRecoveryCode = false;

    private const PENDING_KEY = 'auth.2fa_pending';

    private const MAX_ATTEMPTS = 5;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:filter', 'max:150'],
            'password' => ['required', 'string', 'min:8', 'max:150'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ];
    }

    public function login(): void
    {
        if (filled($this->website)) {
            // Diamkan bot: laporkan kegagalan generik tanpa menyentuh database.
            $this->fail('Email atau kata sandi salah.');
        }

        $this->validate();

        $recaptcha = app(RecaptchaService::class);

        // Diperiksa sebelum menyentuh database: bot tidak perlu dihitung
        // sebagai percobaan login sungguhan.
        if ($recaptcha->isEnabled()) {
            $result = $recaptcha->check($this->recaptchaToken, 'login');

            if (! $result['ok']) {
                $this->logAttempt('recaptcha rejected');

                $this->fail($result['message']);
            }
        }

        $key = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            $this->logAttempt('locked out');

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Backoff progresif: makin sering gagal, makin lama jendela kuncinya.
            RateLimiter::hit($key, 60 * (RateLimiter::attempts($key) + 1));

            $this->logAttempt('failed');
            $this->fail('Email atau kata sandi salah.');
        }

        if (! Auth::user()?->isOwner()) {
            Auth::logout();
            $this->logAttempt('not owner');
            $this->fail('Akun ini tidak memiliki akses ke dashboard.');
        }

        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            // Sesi belum boleh terbentuk sebelum faktor kedua terbukti.
            Auth::logout();

            session()->put(self::PENDING_KEY, [
                'id' => $user->getKey(),
                'at' => now()->getTimestamp(),
            ]);

            RateLimiter::clear($key);

            $this->reset('password');
            $this->awaitingTwoFactor = true;

            return;
        }

        RateLimiter::clear($key);

        // Cegah session fixation: ID sesi lama tidak boleh dipakai ulang
        // setelah hak akses meningkat.
        session()->regenerate();

        // Penanda waktu login menjadi dasar batas sesi mutlak.
        session()->put(EnforceAbsoluteSessionLifetime::KEY, now()->getTimestamp());

        $this->redirectRoute('dashboard.home', navigate: false);
    }

    /**
     * Tahap dua: kode TOTP atau satu kode pemulihan.
     */
    public function verifyTwoFactor(TwoFactorService $service): void
    {
        $pending = session(self::PENDING_KEY);

        // Jendela sempit: sesi tahap dua tidak boleh menggantung berjam-jam.
        if (! is_array($pending) || now()->getTimestamp() - ($pending['at'] ?? 0) > 300) {
            session()->forget(self::PENDING_KEY);
            $this->awaitingTwoFactor = false;

            $this->fail('Waktu verifikasi habis. Silakan masuk kembali.');
        }

        $key = '2fa:'.$pending['id'].'|'.get_real_ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $this->logAttempt('two-factor locked out');

            throw ValidationException::withMessages([
                'twoFactorCode' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($key).' detik.',
            ]);
        }

        $user = User::find($pending['id']);

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            session()->forget(self::PENDING_KEY);
            $this->awaitingTwoFactor = false;

            $this->fail('Sesi tidak valid. Silakan masuk kembali.');
        }

        $input = trim($this->twoFactorCode);

        if ($input === '') {
            throw ValidationException::withMessages([
                'twoFactorCode' => $this->useRecoveryCode ? 'Masukkan kode pemulihan.' : 'Masukkan kode 6 digit.',
            ]);
        }

        $passed = $this->useRecoveryCode
            ? $this->consumeRecoveryCode($service, $user, $input)
            : $service->verify((string) $user->two_factor_secret, $input);

        if (! $passed) {
            RateLimiter::hit($key, 60 * (RateLimiter::attempts($key) + 1));
            $this->logAttempt('two-factor failed');
            $this->reset('twoFactorCode');

            throw ValidationException::withMessages([
                'twoFactorCode' => $this->useRecoveryCode
                    ? 'Kode pemulihan tidak dikenali.'
                    : 'Kode tidak cocok. Pastikan jam perangkat Anda tepat.',
            ]);
        }

        RateLimiter::clear($key);
        session()->forget(self::PENDING_KEY);

        Auth::login($user, $this->remember);

        session()->regenerate();
        session()->put(EnforceAbsoluteSessionLifetime::KEY, now()->getTimestamp());

        Log::channel('security')->info('Two-factor login succeeded', [
            'user' => $user->getKey(),
            'ip' => get_real_ip(),
            'method' => $this->useRecoveryCode ? 'recovery-code' : 'totp',
        ]);

        $this->redirectRoute('dashboard.home', navigate: false);
    }

    private function consumeRecoveryCode(TwoFactorService $service, User $user, string $input): bool
    {
        $remaining = $service->consumeRecoveryCode($user->recoveryCodes(), $input);

        if ($remaining === null) {
            return false;
        }

        // Kode pemulihan sekali pakai: langsung dicoret begitu dipakai.
        $user->forceFill(['two_factor_recovery_codes' => $remaining])->save();

        return true;
    }

    public function toggleRecoveryCode(): void
    {
        $this->useRecoveryCode = ! $this->useRecoveryCode;
        $this->reset('twoFactorCode');
        $this->resetValidation();
    }

    public function backToPassword(): void
    {
        session()->forget(self::PENDING_KEY);
        $this->reset(['awaitingTwoFactor', 'twoFactorCode', 'useRecoveryCode', 'password']);
        $this->resetValidation();
    }

    private function fail(string $message): never
    {
        $this->reset(['password', 'recaptchaToken']);
        $this->dispatch('recaptcha-reset');

        throw ValidationException::withMessages(['email' => $message]);
    }

    private function logAttempt(string $outcome): void
    {
        Log::channel('security')->warning('Dashboard login '.$outcome, [
            'email' => Str::limit($this->email, 80),
            'ip' => get_real_ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 200),
        ]);
    }

    private function throttleKey(): string
    {
        return 'login:'.Str::lower($this->email).'|'.get_real_ip();
    }

    public function render()
    {
        return view('livewire.dashboard.auth.login', [
            'owner' => User::owner(),
        ]);
    }
}
