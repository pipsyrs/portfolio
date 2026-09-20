<?php

namespace App\Livewire\Dashboard\Profile;

use App\Actions\Profile\UpdateProfile;
use App\Livewire\Concerns\InteractsWithToasts;
use App\Models\User;
use App\Rules\SafeDocumentUpload;
use App\Rules\SafeImageUpload;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.dashboard')]
#[Title('Profil')]
class Edit extends Component
{
    use InteractsWithToasts, WithFileUploads;

    public string $tab = 'personal';

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $headline = '';

    public string $keywords = '';

    public string $specialis = '';

    public string $about_title = '';

    public string $about_description = '';

    public ?int $experience = null;

    public string $address = '';

    /** @var array<int,array{information:string}> */
    public array $about_extra_information = [];

    /** @var array<int,array<string,mixed>> */
    public array $careers = [];

    /** @var array<int,array<string,mixed>> */
    public array $certifications = [];

    public ?TemporaryUploadedFile $foto = null;

    public ?TemporaryUploadedFile $about_image = null;

    public ?TemporaryUploadedFile $cv_file = null;

    /** @var array<int,TemporaryUploadedFile|null> */
    public array $careerLogos = [];

    /** @var array<int,TemporaryUploadedFile|null> */
    public array $certificationFiles = [];

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public const TABS = [
        'personal' => 'Personal',
        'hero' => 'Hero & SEO',
        'about' => 'About',
        'careers' => 'Karier',
        'certifications' => 'Sertifikasi',
        'files' => 'Berkas',
        'security' => 'Keamanan',
        'sessions' => 'Sesi Aktif',
    ];

    public function mount(): void
    {
        $user = $this->user();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->phone = (string) $user->phone;
        $this->headline = (string) $user->headline;
        $this->keywords = (string) $user->keywords;
        $this->specialis = (string) $user->specialis;
        $this->about_title = (string) $user->about_title;
        $this->about_description = (string) $user->about_description;
        $this->experience = $user->experience !== null ? (int) $user->experience : null;
        $this->address = (string) $user->address;

        $this->about_extra_information = collect(is_array($user->about_extra_information) ? $user->about_extra_information : [])
            ->map(fn ($row) => ['information' => (string) ($row['information'] ?? '')])
            ->values()
            ->all();

        $this->careers = collect(is_array($user->careers) ? $user->careers : [])
            ->map(fn ($row) => [
                'logo' => $row['logo'] ?? null,
                'company' => (string) ($row['company'] ?? ''),
                'position' => (string) ($row['position'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
                'start_date' => (string) ($row['start_date'] ?? ''),
                'end_date' => (string) ($row['end_date'] ?? ''),
                'on_going' => (bool) ($row['on_going'] ?? false),
            ])
            ->values()
            ->all();

        $this->certifications = collect(is_array($user->certifications) ? $user->certifications : [])
            ->map(fn ($row) => [
                'title' => (string) ($row['title'] ?? ''),
                'issuer' => (string) ($row['issuer'] ?? ''),
                'file' => $row['file'] ?? null,
                'credential_id' => (string) ($row['credential_id'] ?? ''),
                'credential_url' => (string) ($row['credential_url'] ?? ''),
                'issued_at' => (string) ($row['issued_at'] ?? ''),
                'expired_at' => (string) ($row['expired_at'] ?? ''),
                'no_expiry' => (bool) ($row['no_expiry'] ?? false),
            ])
            ->values()
            ->all();
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, self::TABS)) {
            $this->tab = $tab;
        }
    }

    public function addExtraInformation(): void
    {
        $this->about_extra_information[] = ['information' => ''];
    }

    public function removeExtraInformation(int $index): void
    {
        unset($this->about_extra_information[$index]);
        $this->about_extra_information = array_values($this->about_extra_information);
    }

    public function addCareer(): void
    {
        $this->careers[] = [
            'logo' => null,
            'company' => '',
            'position' => '',
            'description' => '',
            'start_date' => '',
            'end_date' => '',
            'on_going' => false,
        ];
    }

    public function removeCareer(int $index): void
    {
        unset($this->careers[$index], $this->careerLogos[$index]);
        $this->careers = array_values($this->careers);
        $this->careerLogos = array_values($this->careerLogos);
    }

    public function moveCareer(int $index, int $direction): void
    {
        $target = $index + $direction;

        if (! isset($this->careers[$index], $this->careers[$target])) {
            return;
        }

        [$this->careers[$index], $this->careers[$target]] = [$this->careers[$target], $this->careers[$index]];
    }

    public function addCertification(): void
    {
        $this->certifications[] = [
            'title' => '',
            'issuer' => '',
            'file' => null,
            'credential_id' => '',
            'credential_url' => '',
            'issued_at' => '',
            'expired_at' => '',
            'no_expiry' => false,
        ];
    }

    public function removeCertification(int $index): void
    {
        unset($this->certifications[$index], $this->certificationFiles[$index]);
        $this->certifications = array_values($this->certifications);
        $this->certificationFiles = array_values($this->certificationFiles);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email:filter', 'max:150', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'headline' => ['nullable', 'string', 'max:255'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'specialis' => ['nullable', 'string', 'max:150'],
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_description' => ['nullable', 'string', 'max:20000'],
            'experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'address' => ['nullable', 'string', 'max:500'],

            'about_extra_information' => ['array', 'max:20'],
            'about_extra_information.*.information' => ['nullable', 'string', 'max:255'],

            'careers' => ['array', 'max:30'],
            'careers.*.company' => ['required', 'string', 'max:150'],
            'careers.*.position' => ['required', 'string', 'max:150'],
            'careers.*.description' => ['nullable', 'string', 'max:10000'],
            'careers.*.start_date' => ['required', 'date_format:Y-m-d'],
            'careers.*.end_date' => ['nullable', 'date_format:Y-m-d'],
            'careers.*.on_going' => ['boolean'],

            'certifications' => ['array', 'max:50'],
            'certifications.*.title' => ['required', 'string', 'max:200'],
            'certifications.*.issuer' => ['required', 'string', 'max:200'],
            'certifications.*.credential_id' => ['nullable', 'string', 'max:150'],
            'certifications.*.credential_url' => ['nullable', 'string', 'max:255', 'url:http,https'],
            'certifications.*.issued_at' => ['required', 'date_format:Y-m-d'],
            'certifications.*.expired_at' => ['nullable', 'date_format:Y-m-d'],
            'certifications.*.no_expiry' => ['boolean'],

            'foto' => ['nullable', new SafeImageUpload(2048)],
            'about_image' => ['nullable', new SafeImageUpload(2048)],
            'cv_file' => ['nullable', new SafeDocumentUpload(10240, pdfOnly: true)],
            'careerLogos.*' => ['nullable', new SafeImageUpload(2048)],
            'certificationFiles.*' => ['nullable', new SafeDocumentUpload(5120)],

            'password' => ['nullable', 'string', 'min:8', 'max:150', 'confirmed'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka dan tanda + - ( ).',
            'careers.*.company.required' => 'Nama perusahaan wajib diisi.',
            'careers.*.position.required' => 'Posisi wajib diisi.',
            'careers.*.start_date.required' => 'Tanggal mulai wajib diisi.',
            'certifications.*.title.required' => 'Judul sertifikasi wajib diisi.',
            'certifications.*.issuer.required' => 'Penerbit sertifikasi wajib diisi.',
            'certifications.*.issued_at.required' => 'Tanggal terbit wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }

    public function save(UpdateProfile $updateProfile): void
    {
        $this->authorize('owner');

        $validated = $this->validate();

        // Mengganti kata sandi wajib membuktikan kepemilikan sesi saat ini,
        // sehingga sesi yang dibajak tidak bisa mengunci pemilik aslinya.
        if (filled($this->password) && ! Hash::check($this->current_password, $this->user()->password)) {
            $this->addError('current_password', 'Kata sandi saat ini tidak sesuai.');

            return;
        }

        $plainPassword = $this->password;

        $updateProfile($this->user(), $validated + ['password' => $plainPassword], [
            'foto' => $this->foto,
            'about_image' => $this->about_image,
            'cv_file' => $this->cv_file,
            'careerLogos' => $this->careerLogos,
            'certificationFiles' => $this->certificationFiles,
        ]);

        $this->reset([
            'foto', 'about_image', 'cv_file', 'careerLogos', 'certificationFiles',
            'current_password', 'password', 'password_confirmation',
        ]);

        if (filled($plainPassword)) {
            // Setiap sesi lain yang masih memegang hash lama langsung dicabut.
            auth()->logoutOtherDevices($plainPassword);
        }

        $this->mount();

        $this->toastSuccess('Profil berhasil diperbarui.');
    }

    private function user(): User
    {
        return auth()->user();
    }

    public function render()
    {
        return view('livewire.dashboard.profile.edit', [
            'user' => $this->user(),
            'tabs' => self::TABS,
        ]);
    }
}
