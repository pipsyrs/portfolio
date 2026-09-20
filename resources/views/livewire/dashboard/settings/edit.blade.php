@php
    $logo = is_array(settings('app_logo')) ? (settings('app_logo')[0] ?? null) : null;
@endphp

<div class="space-y-6">

    <x-dash.page-header title="Pengaturan Aplikasi"
                        eyebrow="Sistem"
                        subtitle="Semua konfigurasi tampilan dan perilaku situs diatur dari sini — tanpa perlu deploy ulang." />

    <div class="grid gap-6 lg:grid-cols-[210px_minmax(0,1fr)]">

        <aside class="lg:sticky lg:top-6 lg:self-start">
            <x-dash.side-tabs :groups="$tabGroups" :active="$tab" />
        </aside>

        <div class="min-w-0 space-y-5">

            @if ($tab === 'aplikasi')
                <x-dash.form-card action="saveIdentity" title="Identitas Aplikasi"
                                  icon="fa-solid fa-circle-info"
                                  subtitle="Nama yang tampil di judul tab, sidebar, dan halaman login.">
                    <div class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-dash.input label="Nama Aplikasi" name="app_name" required
                                          wire:model="app_name" placeholder="Portfolio Saya" />
                            <x-dash.input label="Nama Singkat" name="app_name_short" required
                                          wire:model="app_name_short"
                                          hint="Dipakai di sidebar dan judul tab."
                                          placeholder="PORTFOLIO" />
                        </div>

                        <x-dash.textarea label="Deskripsi Singkat" name="app_description" rows="3"
                                         wire:model="app_description"
                                         hint="Tampil di panel halaman login."
                                         placeholder="Kelola seluruh konten situs dari satu tempat." />
                    </div>
                </x-dash.form-card>
            @endif

            @if ($tab === 'tampilan')
                <x-dash.form-card action="saveColor" title="Warna Utama" icon="fa-solid fa-palette"
                                  subtitle="Warna ini dipakai landing page dan dashboard secara bersamaan.">
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <input type="color" wire:model.live="app_color"
                                   class="h-11 w-16 cursor-pointer rounded-lg border"
                                   style="border-color: var(--hairline); background: transparent;">

                            <input type="text" wire:model.live="app_color"
                                   class="dash-input mono w-32" placeholder="#38bdf8">

                            <div class="flex items-center gap-2 rounded-lg px-3 py-2" style="background-color: var(--surface-alt);">
                                <span class="h-5 w-5 rounded-md" style="background-color: {{ $app_color }};"></span>
                                <span class="mono text-[11px]" style="color: var(--ink-soft);">pratinjau langsung</span>
                            </div>
                        </div>

                        @error('app_color')
                            <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
                        @enderror

                        <div class="flex flex-wrap gap-2">
                            @foreach (['#38bdf8', '#00ff91', '#6366f1', '#f43f5e', '#f59e0b', '#10b981', '#8b5cf6', '#0ea5e9'] as $preset)
                                <button type="button" wire:click="$set('app_color', '{{ $preset }}')"
                                        class="h-8 w-8 rounded-lg border-2 transition hover:scale-110"
                                        style="background-color: {{ $preset }}; border-color: {{ $app_color === $preset ? 'var(--ink)' : 'transparent' }};"
                                        title="{{ $preset }}"></button>
                            @endforeach
                        </div>
                    </div>
                </x-dash.form-card>

                <x-dash.form-card action="saveSections" title="Bagian Landing Page" icon="fa-solid fa-eye"
                                  subtitle="Bagian yang dimatikan tidak dirender sama sekali — datanya tetap utuh.">
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        @foreach ($sections as $key => $label)
                            <x-dash.toggle :label="$label" tone="success" wire:model="landing_sections.{{ $key }}" />
                        @endforeach
                    </div>
                </x-dash.form-card>
            @endif

            @if ($tab === 'media')
                <x-dash.form-card action="saveMedia" title="Logo & Gambar" icon="fa-solid fa-images"
                                  subtitle="Gambar lama otomatis dihapus setelah penggantinya tersimpan."
                                  blocking>
                    <div class="space-y-5">
                        <x-dash.file-drop
                            label="Logo Aplikasi" name="app_logo" wire:model="app_logo"
                            :current="$app_logo ? $app_logo->temporaryUrl() : ($logo ? safe_image_url($logo) : null)"
                            hint="Rekomendasi 512×512px. Maksimal 2 MB."
                            accept="image/jpeg,image/png,image/webp" />

                        <x-dash.file-drop
                            label="Favicon" name="app_favicon" wire:model="app_favicon"
                            :current="$app_favicon ? $app_favicon->temporaryUrl() : (settings('app_favicon') ? safe_image_url(settings('app_favicon')) : null)"
                            hint="Rekomendasi 192×192px. Maksimal 1 MB."
                            accept="image/jpeg,image/png,image/webp" />

                        <x-dash.file-drop
                            label="Latar Halaman Login" name="app_background_login_image" wire:model="app_background_login_image"
                            :current="$app_background_login_image ? $app_background_login_image->temporaryUrl() : (settings('app_background_login_image') ? safe_image_url(settings('app_background_login_image')) : null)"
                            hint="Rekomendasi 1600×1200px. Maksimal 4 MB."
                            accept="image/jpeg,image/png,image/webp" />
                    </div>
                </x-dash.form-card>
            @endif

            @if ($tab === 'seo')
                <x-dash.form-card action="saveSeo" title="SEO & Berbagi" icon="fa-solid fa-magnifying-glass-chart"
                                  subtitle="Dipakai landing page. Bila dikosongkan, nilai dari profil yang dipakai."
                                  blocking>
                    <div class="space-y-4">
                        <x-dash.textarea label="Kata Kunci" name="seo_keywords" rows="3"
                                         wire:model="seo_keywords"
                                         hint="Pisahkan dengan koma."
                                         placeholder="fullstack developer, laravel, vue, tailwind" />

                        <x-dash.file-drop
                            label="Gambar Open Graph" name="seo_og_image" wire:model="seo_og_image"
                            :current="$seo_og_image ? $seo_og_image->temporaryUrl() : (settings('seo_og_image') ? safe_image_url(settings('seo_og_image')) : null)"
                            hint="Tampil saat tautan dibagikan. Rekomendasi 1200×630px."
                            accept="image/jpeg,image/png,image/webp" />
                    </div>
                </x-dash.form-card>
            @endif

            @if ($tab === 'sosial')
                <x-dash.form-card action="saveSocial" title="Tautan Media Sosial" icon="fa-solid fa-share-nodes"
                                  subtitle="Kosongkan untuk menyembunyikan ikonnya dari situs.">
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ([
                            'github_link' => ['GitHub', 'fa-brands fa-github', 'https://github.com/username'],
                            'linkedin_link' => ['LinkedIn', 'fa-brands fa-linkedin', 'https://linkedin.com/in/username'],
                            'instagram_link' => ['Instagram', 'fa-brands fa-instagram', 'https://instagram.com/username'],
                            'x_twitter_link' => ['X (Twitter)', 'fa-brands fa-x-twitter', 'https://x.com/username'],
                            'youtube_link' => ['YouTube', 'fa-brands fa-youtube', 'https://youtube.com/@channel'],
                            'tiktok_link' => ['TikTok', 'fa-brands fa-tiktok', 'https://tiktok.com/@username'],
                            'facebook_link' => ['Facebook', 'fa-brands fa-facebook', 'https://facebook.com/username'],
                        ] as $field => $meta)
                            <div>
                                <label class="dash-label">
                                    <i class="{{ $meta[1] }} mr-1.5 text-[11px]" style="color: var(--ink-soft);"></i>
                                    {{ $meta[0] }}
                                </label>

                                <input type="url" wire:model="{{ $field }}" placeholder="{{ $meta[2] }}"
                                       @class(['dash-input', 'is-invalid' => $errors->has($field)])>

                                @error($field)
                                    <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </x-dash.form-card>
            @endif

            @if (in_array($tab, \App\Livewire\Dashboard\Settings\Edit::INTEGRATION_TABS, true))
                <livewire:dashboard.settings.integrations :section="$tab" :key="'integrations-'.$tab" />
            @endif

            @if ($tab === 'sistem')
                <x-dash.form-card action="saveBehavior" title="Perilaku Situs" icon="fa-solid fa-toggle-on"
                                  subtitle="Berlaku untuk landing page. Dashboard tidak terpengaruh.">
                    <div class="space-y-2.5">
                        <x-dash.toggle label="Mode Pemeliharaan" tone="warning"
                                       description="Pengunjung melihat halaman pemeliharaan. Anda yang sedang login tetap melihat situs apa adanya."
                                       wire:model="maintenance_mode" />

                        <x-dash.toggle label="Catat Kunjungan" tone="success"
                                       description="Merekam kunjungan untuk grafik pengunjung. Bot otomatis diabaikan."
                                       wire:model="visitor_tracking_enabled" />
                    </div>
                </x-dash.form-card>
            @endif

            @if ($tab === 'backup')
                <x-dash.form-card action="saveBackup" title="Backup & Notifikasi"
                                  icon="fa-solid fa-clock-rotate-left"
                                  subtitle="Jadwal dijalankan lewat cron schedule:run.">
                    <div class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="dash-label">Jadwal Backup Otomatis</label>
                                <select wire:model="backup_schedule" class="dash-input">
                                    @foreach ($schedules as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <p class="dash-hint">Dijalankan lewat cron <span class="mono">schedule:run</span>.</p>

                                @error('backup_schedule')
                                    <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <x-dash.input label="Jumlah Backup Disimpan" name="backup_retention" type="number"
                                          min="1" max="60" required wire:model="backup_retention"
                                          hint="Berkas terlama otomatis dihapus melebihi angka ini." />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-dash.input label="Interval Polling Notifikasi" name="notification_polling" type="number"
                                          min="10" max="600" required wire:model="notification_polling"
                                          hint="Dalam detik. Naikkan bila server terbatas." />

                            <x-dash.input label="Email Pemberitahuan Kontak" name="contact_notification_email" type="email"
                                          wire:model="contact_notification_email"
                                          hint="Kosongkan untuk memakai email profil."
                                          placeholder="nama@domain.com" />
                        </div>
                    </div>
                </x-dash.form-card>

                <div class="card rounded-2xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-shield-halved mt-0.5 text-sm" style="color: var(--success);"></i>
                        <p class="text-xs leading-relaxed" style="color: var(--ink-soft);">
                            Berkas backup dienkripsi dengan <span class="mono">BACKUP_ENCRYPTION_KEY</span> yang
                            terpisah dari <span class="mono">APP_KEY</span>. Jalankan backup manual dan periksa
                            kesiapan server di halaman
                            <a href="{{ route('dashboard.backup') }}" wire:navigate class="underline" style="color: var(--primary);">Backup Database</a>.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
