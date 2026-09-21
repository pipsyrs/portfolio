<?php

namespace App\Actions\Profile;

use App\Actions\Media\SanitizeRichText;
use App\Actions\Media\StoreSecureUpload;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class UpdateProfile
{
    /**
     * Kolom yang boleh ditulis dari form profil. Daftar ini sengaja eksplisit
     * supaya field lain (mis. email_verified_at atau remember_token) tidak
     * bisa ikut terisi lewat payload yang dimanipulasi.
     */
    private const WRITABLE = [
        'name',
        'email',
        'phone',
        'headline',
        'keywords',
        'specialis',
        'about_title',
        'experience',
        'address',
    ];

    public function __construct(
        private readonly StoreSecureUpload $uploads,
        private readonly SanitizeRichText $sanitize,
    ) {}

    /**
     * @param  array<string,mixed>  $data
     * @param  array<string,mixed>  $files
     */
    public function __invoke(User $user, array $data, array $files = []): User
    {
        $payload = Arr::only($data, self::WRITABLE);

        $payload['experience'] = blank($payload['experience'] ?? null) ? null : (int) $payload['experience'];

        // Konten rich text dibersihkan sekali di sini, saat disimpan.
        $payload['about_description'] = ($this->sanitize)($data['about_description'] ?? null);

        $payload['about_extra_information'] = $this->extraInformation($data['about_extra_information'] ?? []);

        foreach (['foto' => 'profile-photos', 'about_image' => 'about-images'] as $field => $directory) {
            $file = $files[$field] ?? null;

            if (! $file instanceof TemporaryUploadedFile) {
                continue;
            }

            $old = $user->{$field};
            $payload[$field] = $this->uploads->image($file, $directory);

            if ($old) {
                $this->uploads->delete($old);
            }
        }

        if (($files['cv_file'] ?? null) instanceof TemporaryUploadedFile) {
            $old = $user->cv_file;
            $payload['cv_file'] = $this->uploads->document($files['cv_file'], 'cv');

            if ($old) {
                $this->uploads->delete($old);
            }
        }

        $payload['careers'] = $this->careers($data['careers'] ?? [], $files['careerLogos'] ?? [], $user);
        $payload['certifications'] = $this->certifications($data['certifications'] ?? [], $files['certificationFiles'] ?? [], $user);

        if (filled($data['password'] ?? null)) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->fill($payload)->save();

        User::forgetOwnerCache();

        return $user;
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @return array<int,array<string,string>>
     */
    private function extraInformation(array $rows): array
    {
        return collect($rows)
            ->map(fn ($row) => trim((string) ($row['information'] ?? '')))
            ->filter(fn (string $value) => $value !== '')
            ->map(fn (string $value) => ['information' => $value])
            ->values()
            ->all();
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @param  array<int,mixed>  $logos
     * @return array<int,array<string,mixed>>
     */
    private function careers(array $rows, array $logos, User $user): array
    {
        $existing = collect(is_array($user->careers) ? $user->careers : [])->pluck('logo')->filter()->all();
        $kept = [];

        $result = collect($rows)->values()->map(function (array $row, int $index) use ($logos, $existing, &$kept) {
            $onGoing = (bool) ($row['on_going'] ?? false);

            // Properti Livewire bisa diubah dari sisi klien, jadi path lama
            // hanya diterima bila memang sudah tercatat milik pengguna ini.
            $logo = in_array($row['logo'] ?? null, $existing, true) ? $row['logo'] : null;

            if (($logos[$index] ?? null) instanceof TemporaryUploadedFile) {
                $logo = $this->uploads->image($logos[$index], 'careers-logo');
            }

            if ($logo) {
                $kept[] = $logo;
            }

            return [
                'logo' => $logo,
                'company' => trim((string) ($row['company'] ?? '')),
                'position' => trim((string) ($row['position'] ?? '')),
                'description' => ($this->sanitize)($row['description'] ?? null),
                'start_date' => $row['start_date'] ?: null,
                'end_date' => $onGoing ? null : ($row['end_date'] ?: null),
                'on_going' => $onGoing,
            ];
        })
            ->filter(fn (array $row) => $row['company'] !== '' && $row['position'] !== '')
            ->values()
            ->all();

        // Logo yang tidak lagi dirujuk baris mana pun akan menggantung di disk.
        foreach (array_diff($existing, $kept) as $orphan) {
            $this->uploads->delete($orphan);
        }

        return $result;
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @param  array<int,mixed>  $uploads
     * @return array<int,array<string,mixed>>
     */
    private function certifications(array $rows, array $uploads, User $user): array
    {
        $existing = collect(is_array($user->certifications) ? $user->certifications : [])->pluck('file')->filter()->all();
        $kept = [];

        $result = collect($rows)->values()->map(function (array $row, int $index) use ($uploads, $existing, &$kept) {
            $noExpiry = (bool) ($row['no_expiry'] ?? false);
            $file = in_array($row['file'] ?? null, $existing, true) ? $row['file'] : null;

            if (($uploads[$index] ?? null) instanceof TemporaryUploadedFile) {
                $file = $this->uploads->document($uploads[$index], 'certifications');
            }

            if ($file) {
                $kept[] = $file;
            }

            $url = trim((string) ($row['credential_url'] ?? ''));

            return [
                'title' => trim((string) ($row['title'] ?? '')),
                'issuer' => trim((string) ($row['issuer'] ?? '')),
                'file' => $file,
                'credential_id' => trim((string) ($row['credential_id'] ?? '')) ?: null,
                'credential_url' => filter_var($url, FILTER_VALIDATE_URL) ? $url : null,
                'issued_at' => $row['issued_at'] ?: null,
                'expired_at' => $noExpiry ? null : ($row['expired_at'] ?: null),
                'no_expiry' => $noExpiry,
            ];
        })
            ->filter(fn (array $row) => $row['title'] !== '' && $row['issuer'] !== '')
            ->values()
            ->all();

        foreach (array_diff($existing, $kept) as $orphan) {
            $this->uploads->delete($orphan);
        }

        return $result;
    }
}
