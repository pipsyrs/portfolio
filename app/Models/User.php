<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable {
        Notifiable::notify as private dispatchNotification;
    }

    /**
     * Kegagalan siaran tidak boleh menggagalkan aksi yang memicunya.
     *
     * Kanal `database` dijalankan lebih dulu, jadi saat Reverb sedang mati
     * notifikasinya tetap tersimpan dan lonceng dashboard menyusul lewat
     * polling. Hanya kegagalan broadcast yang ditelan — galat basis data
     * tetap naik ke pemanggil.
     */
    public function notify($instance): void
    {
        try {
            $this->dispatchNotification($instance);
        } catch (BroadcastException $e) {
            Log::warning('Siaran notifikasi gagal, dashboard kembali ke polling', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'foto',
        'name',
        'email',
        'phone',
        'headline',
        'keywords',
        'about_image',
        'about_title',
        'about_description',
        'about_extra_information',
        'experience',
        'careers',
        'certifications',
        'address',
        'specialis',
        'cv_file',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'about_extra_information' => 'array',
            'careers' => 'array',
            'certifications' => 'array',
            // Terenkripsi di basis data: dump saja tidak cukup untuk melewati 2FA.
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Aplikasi ini single-account: pemilik adalah user dengan id terkecil.
     * Di-cache supaya tidak memukul database pada tiap request landing.
     */
    public static function owner(): ?self
    {
        return cache()->remember('portfolio.owner', now()->addHour(), fn () => static::query()->oldest('id')->first());
    }

    public static function forgetOwnerCache(): void
    {
        cache()->forget('portfolio.owner');
    }

    public function isOwner(): bool
    {
        return $this->getKey() === static::owner()?->getKey();
    }

    public function avatarUrl(): string
    {
        return safe_image_url($this->foto, 'profile-photos');
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_confirmed_at !== null && filled($this->two_factor_secret);
    }

    /**
     * @return array<int,string>
     */
    public function recoveryCodes(): array
    {
        return is_array($this->two_factor_recovery_codes) ? $this->two_factor_recovery_codes : [];
    }
}
