<div>
    <x-dash.card title="Autentikasi Dua Langkah" icon="fa-solid fa-shield-halved"
                 subtitle="Kode sekali pakai dari aplikasi autentikator, selain kata sandi.">

        <x-slot:actions>
            @if ($enabled)
                <x-dash.badge tone="success" icon="fa-solid fa-circle-check">AKTIF</x-dash.badge>
            @else
                <x-dash.badge tone="warning">BELUM AKTIF</x-dash.badge>
            @endif
        </x-slot:actions>

        {{-- Tahap 1: belum aktif, belum memulai --}}
        @if (! $enabled && ! $pendingSecret)
            <p class="text-sm leading-relaxed" style="color: var(--ink-soft);">
                Dengan 2FA aktif, kata sandi yang bocor saja tidak cukup untuk masuk.
                Anda butuh aplikasi seperti Google Authenticator, Authy, atau 1Password.
            </p>

            <div class="mt-5">
                <x-dash.button wire:click="startSetup" loading-target="startSetup" icon="fa-solid fa-qrcode">
                    Aktifkan 2FA
                </x-dash.button>
            </div>
        @endif

        {{-- Tahap 2: memindai QR dan membuktikan kode --}}
        @if ($pendingSecret)
            <div class="grid gap-6 sm:grid-cols-[auto_1fr]">
                <div class="mx-auto rounded-xl bg-white p-3 sm:mx-0" style="width: 200px;">
                    {!! $qr !!}
                </div>

                <div class="min-w-0 space-y-4">
                    <div>
                        <p class="text-sm" style="color: var(--ink);">1. Pindai kode QR ini</p>
                        <p class="mt-1 text-xs leading-relaxed" style="color: var(--ink-soft);">
                            Atau masukkan kunci berikut secara manual:
                        </p>
                        <p class="mono mt-2 break-all rounded-lg px-3 py-2 text-xs"
                           style="background-color: var(--surface-alt); color: var(--ink);">
                            {{ $pendingSecret }}
                        </p>
                    </div>

                    <form wire:submit="confirm" class="space-y-3">
                        <x-dash.input label="2. Masukkan kode 6 digit" name="code" required
                                      wire:model="code" inputmode="numeric" autocomplete="one-time-code"
                                      maxlength="6" placeholder="000000" class="dash-input mono tracking-[0.3em]" />

                        <div class="flex gap-2">
                            <x-dash.button type="submit" size="sm" loading-target="confirm" icon="fa-solid fa-check">
                                Verifikasi &amp; Aktifkan
                            </x-dash.button>
                            <x-dash.button variant="secondary" size="sm" wire:click="cancelSetup">Batal</x-dash.button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Tahap 3: sudah aktif --}}
        @if ($enabled && ! $pendingSecret)
            <div class="space-y-4">
                <div class="flex items-start gap-2.5 rounded-xl p-3"
                     style="background-color: color-mix(in srgb, var(--success) 8%, transparent);">
                    <i class="fa-solid fa-circle-check mt-0.5 text-xs" style="color: var(--success);"></i>
                    <p class="text-xs leading-relaxed" style="color: var(--ink-soft);">
                        Aktif sejak {{ auth()->user()->two_factor_confirmed_at?->translatedFormat('d M Y, H:i') }}.
                        Tersisa <span class="mono">{{ $remainingCodes }}</span> kode pemulihan.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-dash.button variant="secondary" size="sm" wire:click="regenerateRecoveryCodes"
                                   loading-target="regenerateRecoveryCodes" icon="fa-solid fa-rotate">
                        Buat ulang kode pemulihan
                    </x-dash.button>

                    <x-dash.button variant="ghost" size="sm" wire:click="askDisable" icon="fa-solid fa-shield-xmark">
                        Matikan 2FA
                    </x-dash.button>
                </div>
            </div>
        @endif
    </x-dash.card>

    {{-- Kode pemulihan: satu-satunya jalan masuk bila perangkat hilang --}}
    <x-dash.modal :show="$showRecoveryCodes" close-action="hideRecoveryCodes" max-width="max-w-lg"
                  title="Kode Pemulihan" icon="fa-solid fa-key" tone="warning">

        <p>
            Simpan kode ini di tempat aman dan di luar perangkat autentikator Anda.
            Setiap kode hanya bisa dipakai sekali, dan inilah satu-satunya cara masuk
            bila perangkat autentikator hilang.
        </p>

        <div class="mono mt-4 grid grid-cols-2 gap-2 rounded-xl p-3 text-xs"
             style="background-color: var(--surface-alt); color: var(--ink);">
            @foreach ($pendingRecoveryCodes as $recoveryCode)
                <span class="select-all">{{ $recoveryCode }}</span>
            @endforeach
        </div>

        <x-slot:footer>
            <x-dash.button variant="secondary" size="sm"
                           x-on:click="navigator.clipboard.writeText(@js(implode(PHP_EOL, $pendingRecoveryCodes))); window.dashToast({ type: 'success', message: 'Kode pemulihan disalin.' })"
                           icon="fa-solid fa-copy">
                Salin
            </x-dash.button>
            <x-dash.button size="sm" wire:click="hideRecoveryCodes">Sudah saya simpan</x-dash.button>
        </x-slot:footer>
    </x-dash.modal>

    <x-dash.modal :show="$disabling" close-action="cancelDisable" max-width="max-w-md"
                  title="Matikan autentikasi dua langkah?" icon="fa-solid fa-shield-xmark" tone="danger">

        <form wire:submit="disable" class="space-y-4">
            <p>Akun kembali hanya dilindungi kata sandi. Masukkan kata sandi untuk melanjutkan.</p>

            <x-dash.input label="Kata Sandi" name="password" type="password" required
                          wire:model="password" autocomplete="current-password" />

            <div class="flex justify-end gap-2 pt-1">
                <x-dash.button variant="secondary" size="sm" wire:click="cancelDisable">Batal</x-dash.button>
                <x-dash.button type="submit" variant="danger" size="sm" loading-target="disable">
                    Ya, matikan
                </x-dash.button>
            </div>
        </form>
    </x-dash.modal>
</div>
