<div class="space-y-5">

    @if ($section === 'email')
        <x-dash.form-card action="saveMail" title="Server Email (SMTP)" icon="fa-solid fa-envelope"
                          subtitle="Dipakai untuk membalas pesan kontak dan notifikasi sistem.">
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="dash-label">Pengirim</label>
                        <select wire:model="mail_mailer" class="dash-input">
                            @foreach ($mailers as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="dash-hint">Pilih Log untuk menguji tanpa benar-benar mengirim.</p>
                    </div>

                    <div>
                        <label class="dash-label">Enkripsi</label>
                        <select wire:model="mail_encryption" class="dash-input">
                            @foreach ($encryptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-[2fr_1fr]">
                    <x-dash.input label="Host" name="mail_host" wire:model="mail_host"
                                  placeholder="smtp.gmail.com" />
                    <x-dash.input label="Port" name="mail_port" type="number"
                                  wire:model="mail_port" placeholder="587" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-dash.secret-input label="Username" field="mail_username"
                                         :stored="settings()->hasSecret('mail_username')" />
                    <x-dash.secret-input label="Password" field="mail_password"
                                         :stored="settings()->hasSecret('mail_password')"
                                         hint="Untuk Gmail gunakan App Password, bukan kata sandi akun." />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-dash.input label="Email Pengirim" name="mail_from_address" type="email"
                                  wire:model="mail_from_address" placeholder="noreply@domain.com" />
                    <x-dash.input label="Nama Pengirim" name="mail_from_name"
                                  wire:model="mail_from_name" placeholder="Portfolio" />
                </div>
            </div>
        </x-dash.form-card>

        {{-- Bukan form pengaturan: tidak menyimpan apa pun, jadi tidak memakai
             form-card yang punya tombol simpan. --}}
        <x-dash.card title="Uji Pengiriman" icon="fa-solid fa-paper-plane"
                     subtitle="Memakai konfigurasi yang sudah tersimpan, bukan yang sedang diketik.">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[240px] flex-1">
                    <x-dash.input label="Kirim ke" name="testEmailTo" type="email" wire:model="testEmailTo" />
                </div>

                <x-dash.button variant="secondary" wire:click="testMail" loading-target="testMail"
                               icon="fa-solid fa-paper-plane" data-dash-blocking>
                    Kirim email uji
                </x-dash.button>
            </div>
        </x-dash.card>
    @endif

    @if ($section === 'integrasi')
        <x-dash.form-card action="saveRecaptcha" title="reCAPTCHA v3" icon="fa-solid fa-shield-halved"
                          subtitle="Menyaring bot pada halaman login dan formulir kontak.">
            <div class="space-y-4">
                <x-dash.toggle label="Aktifkan reCAPTCHA" tone="success"
                               description="Hanya berlaku bila kedua kunci di bawah sudah terisi."
                               wire:model.live="recaptcha_enabled" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-dash.secret-input label="Site Key" field="recaptcha_site_key"
                                         :stored="settings()->hasSecret('recaptcha_site_key')" />
                    <x-dash.secret-input label="Secret Key" field="recaptcha_secret"
                                         :stored="settings()->hasSecret('recaptcha_secret')" />
                </div>

                <div class="max-w-xs">
                    <label class="dash-label">Ambang Batas Skor</label>
                    <div class="flex items-center gap-3">
                        <input type="range" min="0" max="1" step="0.1" wire:model.live="recaptcha_threshold"
                               class="flex-1" style="accent-color: var(--primary);">
                        <span class="mono w-10 text-right text-xs" style="color: var(--ink);">
                            {{ number_format($recaptcha_threshold, 1) }}
                        </span>
                    </div>
                    <p class="dash-hint">Makin tinggi makin ketat. 0.5 adalah anjuran Google.</p>

                    @error('recaptcha_threshold')
                        <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
                    @enderror
                </div>

                <x-dash.button variant="secondary" size="sm" type="button" wire:click="testRecaptcha"
                               loading-target="testRecaptcha" icon="fa-solid fa-vial">
                    Uji kunci
                </x-dash.button>
            </div>
        </x-dash.form-card>

        <x-dash.form-card action="saveApiKeys" title="Kunci API Lain" icon="fa-solid fa-key"
                          subtitle="Kosongkan field untuk mempertahankan nilai yang sudah tersimpan.">
            <div class="space-y-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[240px] flex-1">
                        <x-dash.secret-input label="DeepL API Key" field="deepl_api_key"
                                             :stored="settings()->hasSecret('deepl_api_key')"
                                             hint="Menerjemahkan konten landing page. Kunci paket gratis berakhiran :fx" />
                    </div>

                    <x-dash.button variant="secondary" type="button" wire:click="testDeepl" loading-target="testDeepl"
                                   icon="fa-solid fa-vial">
                        Uji
                    </x-dash.button>
                </div>

                <x-dash.secret-input label="FontAwesome API Token" field="fontawesome_token"
                                     :stored="settings()->hasSecret('fontawesome_token')"
                                     hint="Opsional. Membuka pencarian ikon penuh di Tech Stack dan Spesialisasi." />
            </div>
        </x-dash.form-card>
    @endif

    @if ($section === 'database')
        <x-dash.card title="Koneksi Database" icon="fa-solid fa-database"
                     subtitle="Baca-saja. Perubahan hanya lewat berkas .env.">

            <x-slot:actions>
                <x-dash.button variant="secondary" size="sm" wire:click="testDatabase"
                               loading-target="testDatabase" icon="fa-solid fa-plug">
                    Tes koneksi
                </x-dash.button>
            </x-slot:actions>

            <div class="space-y-2">
                @foreach ($this->databaseInfo as $label => $value)
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b pb-2 last:border-b-0 last:pb-0"
                         style="border-color: color-mix(in srgb, var(--hairline) 60%, transparent);">
                        <span class="text-xs" style="color: var(--ink-soft);">{{ $label }}</span>
                        <span class="mono text-xs" style="color: var(--ink);">{{ $value }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex items-start gap-2.5 rounded-xl p-3"
                 style="background-color: color-mix(in srgb, var(--info) 8%, transparent);">
                <i class="fa-solid fa-circle-info mt-0.5 text-xs" style="color: var(--info);"></i>
                <p class="text-xs leading-relaxed" style="color: var(--ink-soft);">
                    Kredensial database tidak bisa disimpan di sini. Laravel harus membuka koneksi
                    untuk membaca tabel pengaturan, jadi kredensial koneksi itu sendiri wajib tetap
                    berada di <span class="mono">.env</span>.
                </p>
            </div>
        </x-dash.card>
    @endif

    <x-dash.confirm :show="$clearingSecret !== null" title="Kosongkan nilai ini?"
                    confirm="clearSecret" cancel="cancelClearSecret" confirm-label="Ya, kosongkan">
        Nilai <span class="mono">{{ $clearingSecret }}</span> dihapus dari basis data.
        Aplikasi kembali memakai nilai dari berkas <span class="mono">.env</span> bila ada.
    </x-dash.confirm>
</div>
