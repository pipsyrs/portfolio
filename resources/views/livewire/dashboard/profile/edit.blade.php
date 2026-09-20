<div class="mx-auto max-w-4xl space-y-6">

    <x-dash.page-header title="Profil"
                        eyebrow="Sistem"
                        subtitle="Data di sini yang tampil sebagai isi landing page." />

    <x-dash.tabs :tabs="$tabs" :active="$tab" />

    <form wire:submit="save" class="space-y-6">

        @if ($tab === 'personal')
            <x-dash.card title="Foto Profil" icon="fa-solid fa-user">
                <div class="flex flex-wrap items-center gap-5">
                    <img src="{{ $foto ? $foto->temporaryUrl() : $user->avatarUrl() }}" alt=""
                         class="h-20 w-20 rounded-2xl object-cover"
                         style="border: 1px solid var(--hairline);">

                    <div class="min-w-0 flex-1">
                        <x-dash.file-drop name="foto" wire:model="foto" :preview="false"
                                          hint="JPG, PNG, atau WebP. Maksimal 2 MB. Metadata EXIF dihapus otomatis."
                                          accept="image/jpeg,image/png,image/webp" />
                    </div>
                </div>
            </x-dash.card>

            <x-dash.card title="Informasi Pribadi" icon="fa-solid fa-id-card">
                <div class="space-y-4">
                    <x-dash.input label="Nama Lengkap" name="name" required wire:model="name" />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-dash.input label="Email" name="email" type="email" required wire:model="email" />
                        <x-dash.input label="Nomor Telepon" name="phone" type="tel" required
                                      wire:model="phone" placeholder="+62 812 3456 7890" />
                    </div>

                    <x-dash.textarea label="Alamat" name="address" rows="2" wire:model="address" />
                </div>
            </x-dash.card>
        @endif

        @if ($tab === 'hero')
            <x-dash.card title="Hero & SEO" icon="fa-solid fa-star"
                         subtitle="Teks yang pertama kali dibaca pengunjung dan mesin pencari.">
                <div class="space-y-4">
                    <x-dash.input label="Headline" name="headline" wire:model="headline"
                                  placeholder="Membangun aplikasi web yang cepat dan aman" />

                    <x-dash.input label="Spesialisasi" name="specialis" wire:model="specialis"
                                  hint="Tampil di bawah nama, juga dipakai sebagai judul halaman."
                                  placeholder="Fullstack Web Developer" />

                    <x-dash.textarea label="Kata Kunci" name="keywords" rows="2" wire:model="keywords"
                                     hint="Pisahkan dengan koma."
                                     placeholder="laravel developer, backend, vue, tailwind" />
                </div>
            </x-dash.card>
        @endif

        @if ($tab === 'about')
            <x-dash.card title="Bagian About" icon="fa-solid fa-circle-info">
                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-dash.input label="Judul" name="about_title" wire:model="about_title"
                                      placeholder="Tentang Saya" />

                        <x-dash.input label="Pengalaman (tahun)" name="experience" type="number"
                                      min="0" max="80" wire:model="experience" />
                    </div>

                    <x-dash.file-drop label="Gambar About" name="about_image" wire:model="about_image"
                                      :current="$about_image ? $about_image->temporaryUrl() : ($user->about_image ? safe_image_url($user->about_image, 'about-images') : null)"
                                      hint="Maksimal 2 MB."
                                      accept="image/jpeg,image/png,image/webp" />

                    <x-dash.rich-editor label="Deskripsi" name="about_description" :value="$about_description" />
                </div>
            </x-dash.card>

            <x-dash.card title="Informasi Tambahan" icon="fa-solid fa-list-check"
                         subtitle="Poin-poin singkat yang tampil di samping deskripsi.">
                <x-slot:actions>
                    <x-dash.button variant="secondary" size="sm" wire:click="addExtraInformation" icon="fa-solid fa-plus">
                        Tambah
                    </x-dash.button>
                </x-slot:actions>

                @if (empty($about_extra_information))
                    <p class="py-6 text-center text-xs" style="color: var(--ink-soft);">Belum ada poin tambahan.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($about_extra_information as $index => $row)
                            <div wire:key="extra-{{ $index }}" class="flex items-center gap-2">
                                <span class="mono w-6 shrink-0 text-center text-[11px]" style="color: var(--ink-soft);">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>

                                <input type="text" wire:model="about_extra_information.{{ $index }}.information"
                                       class="dash-input flex-1" placeholder="Contoh: 5+ tahun pengalaman Laravel">

                                <button type="button" wire:click="removeExtraInformation({{ $index }})"
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition hover:bg-[color-mix(in_srgb,var(--danger)_10%,transparent)]" title="Hapus">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-dash.card>
        @endif

        @if ($tab === 'careers')
            <x-dash.card title="Riwayat Karier" icon="fa-solid fa-briefcase"
                         subtitle="Urutan di sini menentukan urutan tampil di landing page.">
                <x-slot:actions>
                    <x-dash.button variant="secondary" size="sm" wire:click="addCareer" icon="fa-solid fa-plus">
                        Tambah
                    </x-dash.button>
                </x-slot:actions>

                @if (empty($careers))
                    <p class="py-6 text-center text-xs" style="color: var(--ink-soft);">Belum ada riwayat karier.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($careers as $index => $career)
                            <div wire:key="career-{{ $index }}" class="rounded-xl border p-4" style="border-color: var(--hairline);">

                                <div class="mb-4 flex items-center justify-between">
                                    <span class="mono text-[11px] font-medium" style="color: var(--primary);">
                                        POSISI {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                    <div class="flex gap-1">
                                        <button type="button" wire:click="moveCareer({{ $index }}, -1)"
                                                @disabled($index === 0)
                                                class="dash-icon-btn" title="Naik">
                                            <i class="fa-solid fa-arrow-up text-[10px]"></i>
                                        </button>

                                        <button type="button" wire:click="moveCareer({{ $index }}, 1)"
                                                @disabled($index === count($careers) - 1)
                                                class="dash-icon-btn" title="Turun">
                                            <i class="fa-solid fa-arrow-down text-[10px]"></i>
                                        </button>

                                        <button type="button" wire:click="removeCareer({{ $index }})"
                                                class="dash-icon-btn is-danger" title="Hapus">
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <x-dash.file-drop label="Logo Perusahaan" name="careerLogos.{{ $index }}"
                                                      wire:model="careerLogos.{{ $index }}"
                                                      :current="$career['logo'] ? safe_image_url($career['logo'], 'careers-logo') : null"
                                                      hint="Maksimal 2 MB."
                                                      accept="image/jpeg,image/png,image/webp" />

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <x-dash.input label="Perusahaan" name="careers.{{ $index }}.company" required
                                                      wire:model="careers.{{ $index }}.company" />

                                        <x-dash.input label="Posisi" name="careers.{{ $index }}.position" required
                                                      wire:model="careers.{{ $index }}.position" />
                                    </div>

                                    <x-dash.rich-editor label="Deskripsi" name="careers.{{ $index }}.description"
                                                        :value="$career['description']" />

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <x-dash.input label="Tanggal Mulai" name="careers.{{ $index }}.start_date"
                                                      type="date" required wire:model="careers.{{ $index }}.start_date" />

                                        <div>
                                            <x-dash.input label="Tanggal Selesai" name="careers.{{ $index }}.end_date"
                                                          type="date" wire:model="careers.{{ $index }}.end_date"
                                                          :disabled="$career['on_going']" />

                                            <label class="mt-2 flex cursor-pointer items-center gap-2">
                                                <input type="checkbox" wire:model.live="careers.{{ $index }}.on_going"
                                                       class="h-4 w-4 rounded" style="accent-color: var(--primary);">
                                                <span class="text-xs" style="color: var(--ink-soft);">Masih berjalan</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-dash.card>
        @endif

        @if ($tab === 'certifications')
            <x-dash.card title="Sertifikasi" icon="fa-solid fa-certificate">
                <x-slot:actions>
                    <x-dash.button variant="secondary" size="sm" wire:click="addCertification" icon="fa-solid fa-plus">
                        Tambah
                    </x-dash.button>
                </x-slot:actions>

                @if (empty($certifications))
                    <p class="py-6 text-center text-xs" style="color: var(--ink-soft);">Belum ada sertifikasi.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($certifications as $index => $certification)
                            <div wire:key="cert-{{ $index }}" class="rounded-xl border p-4" style="border-color: var(--hairline);">

                                <div class="mb-4 flex items-center justify-between">
                                    <span class="mono text-[11px] font-medium" style="color: var(--primary);">
                                        SERTIFIKAT {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                    <button type="button" wire:click="removeCertification({{ $index }})"
                                            class="dash-icon-btn is-danger" title="Hapus">
                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <x-dash.input label="Judul" name="certifications.{{ $index }}.title" required
                                                      wire:model="certifications.{{ $index }}.title" />

                                        <x-dash.input label="Penerbit" name="certifications.{{ $index }}.issuer" required
                                                      wire:model="certifications.{{ $index }}.issuer" />
                                    </div>

                                    <x-dash.file-drop label="Berkas Sertifikat" name="certificationFiles.{{ $index }}"
                                                      wire:model="certificationFiles.{{ $index }}" :preview="false"
                                                      hint="{{ $certification['file'] ? 'Tersimpan: ' . basename($certification['file']) . '. ' : '' }}PDF atau gambar. Maksimal 5 MB."
                                                      accept="application/pdf,image/jpeg,image/png,image/webp" />

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <x-dash.input label="ID Kredensial" name="certifications.{{ $index }}.credential_id"
                                                      wire:model="certifications.{{ $index }}.credential_id" />

                                        <x-dash.input label="URL Kredensial" name="certifications.{{ $index }}.credential_url"
                                                      type="url" wire:model="certifications.{{ $index }}.credential_url" />
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <x-dash.input label="Tanggal Terbit" name="certifications.{{ $index }}.issued_at"
                                                      type="date" required wire:model="certifications.{{ $index }}.issued_at" />

                                        <div>
                                            <x-dash.input label="Tanggal Kedaluwarsa" name="certifications.{{ $index }}.expired_at"
                                                          type="date" wire:model="certifications.{{ $index }}.expired_at"
                                                          :disabled="$certification['no_expiry']" />

                                            <label class="mt-2 flex cursor-pointer items-center gap-2">
                                                <input type="checkbox" wire:model.live="certifications.{{ $index }}.no_expiry"
                                                       class="h-4 w-4 rounded" style="accent-color: var(--primary);">
                                                <span class="text-xs" style="color: var(--ink-soft);">Tidak kedaluwarsa</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-dash.card>
        @endif

        @if ($tab === 'files')
            <x-dash.card title="Curriculum Vitae" icon="fa-solid fa-file-pdf"
                         subtitle="Berkas ini yang dibuka tombol 'View CV' di landing page.">
                <div class="space-y-4">
                    @if ($user->cv_file)
                        <div class="flex items-center gap-3 rounded-xl p-3" style="background-color: var(--surface-alt);">
                            <i class="fa-solid fa-file-pdf text-sm" style="color: var(--danger);"></i>
                            <span class="mono flex-1 truncate text-xs" style="color: var(--ink-soft);">
                                {{ basename($user->cv_file) }}
                            </span>
                            <a href="{{ route('view.cv') }}" target="_blank"
                               class="mono text-[11px] hover:underline" style="color: var(--primary);">BUKA</a>
                        </div>
                    @endif

                    <x-dash.file-drop name="cv_file" wire:model="cv_file" :preview="false"
                                      hint="Hanya PDF. Maksimal 10 MB. Isi berkas diverifikasi di server."
                                      accept="application/pdf" />
                </div>
            </x-dash.card>
        @endif

        @if ($tab === 'security')
            <livewire:dashboard.profile.two-factor />
        @endif

        @if ($tab === 'sessions')
            <livewire:dashboard.profile.sessions />
        @endif
        @if ($tab === 'security')
            <x-dash.card title="Ganti Kata Sandi" icon="fa-solid fa-lock"
                         subtitle="Kosongkan bila tidak ingin mengganti kata sandi.">
                <div class="max-w-md space-y-4">
                    <x-dash.input label="Kata Sandi Saat Ini" name="current_password" type="password"
                                  wire:model="current_password" autocomplete="current-password" />

                    <x-dash.input label="Kata Sandi Baru" name="password" type="password"
                                  wire:model="password" autocomplete="new-password"
                                  hint="Minimal 8 karakter." />

                    <x-dash.input label="Konfirmasi Kata Sandi Baru" name="password_confirmation" type="password"
                                  wire:model="password_confirmation" autocomplete="new-password" />

                    <div class="flex items-start gap-3 rounded-xl p-3" style="background-color: var(--surface-alt);">
                        <i class="fa-solid fa-circle-info mt-0.5 text-xs" style="color: var(--info);"></i>
                        <p class="text-xs leading-relaxed" style="color: var(--ink-soft);">
                            Setelah kata sandi diganti, semua sesi di perangkat lain otomatis dikeluarkan.
                            Sesi di perangkat ini tetap aktif.
                        </p>
                    </div>
                </div>
            </x-dash.card>
        @endif

        <div class="sticky bottom-4 flex items-center justify-end">
            <div class="card flex items-center gap-3 rounded-xl px-4 py-3">
                <span class="text-xs" style="color: var(--ink-soft);">Perubahan belum tersimpan otomatis.</span>

                <x-dash.button type="submit" loading-target="save" icon="fa-solid fa-floppy-disk" data-dash-blocking>
                    Simpan Profil
                </x-dash.button>
            </div>
        </div>
    </form>
</div>
