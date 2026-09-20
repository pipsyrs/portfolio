<div class="w-full max-w-sm">

    <div class="mb-8">
        <div class="mb-6 flex items-center gap-2.5 lg:hidden">
            @if (settings('app_logo') && is_array(settings('app_logo')) && count(settings('app_logo')))
                <img src="{{ safe_image_url(settings('app_logo')[0]) }}" alt="" class="h-8 w-8 rounded-lg object-cover">
            @endif
            <span class="text-sm font-medium" style="color: var(--ink);">{{ settings('app_name_short') ?: 'PORTFOLIO' }}</span>
        </div>

        <p class="eyebrow mb-3"><span class="section-num">01</span> Akses Terbatas</p>

        <h1 class="text-2xl font-medium tracking-tight" style="color: var(--ink);">
            {{ $awaitingTwoFactor ? 'Verifikasi dua langkah' : 'Masuk ke dashboard' }}
        </h1>
        <p class="mt-2 text-sm" style="color: var(--ink-soft);">
            Panel ini hanya untuk pemilik{{ $owner ? ', ' . Str::before($owner->name, ' ') : '' }}.
        </p>
    </div>

    @if (session('session-expired'))
        <div class="mb-5 flex items-start gap-2.5 rounded-xl p-3"
             style="background-color: color-mix(in srgb, var(--warning) 10%, transparent);">
            <i class="fa-solid fa-clock mt-0.5 text-xs" style="color: var(--warning);"></i>
            <p class="text-xs leading-relaxed" style="color: var(--ink-soft);">
                {{ session('session-expired') }}
            </p>
        </div>
    @endif
    @if (! $awaitingTwoFactor)
    @php $siteKey = app(\App\Services\RecaptchaService::class)->siteKey(); @endphp

    <form wire:submit="login" class="space-y-4"
          @if ($siteKey) x-data="dashRecaptcha(@js($siteKey))" x-init="load()" @endif>

        {{-- Umpan bot: disembunyikan dari pengguna sungguhan, diperiksa di server. --}}
        <div aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:0;overflow:hidden;">
            <label>Website</label>
            <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
        </div>

        <x-dash.input
            label="Email" name="email" type="email" required
            wire:model="email"
            autocomplete="username"
            autofocus
            placeholder="nama@domain.com" />

        <div x-data="{ show: false }">
            <label for="password" class="dash-label">Kata Sandi <span style="color: var(--danger);">*</span></label>

            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'"
                       wire:model="password"
                       autocomplete="current-password"
                       placeholder="••••••••"
                       @class(['dash-input pr-10', 'is-invalid' => $errors->has('password')])>

                <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2"
                        style="color: var(--ink-soft);"
                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <i class="fa-solid text-xs" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>

            @error('password')
                <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
            @enderror
        </div>

        <label class="flex cursor-pointer items-center gap-2 pt-1">
            <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded" style="accent-color: var(--primary);">
            <span class="text-xs" style="color: var(--ink-soft);">Ingat perangkat ini</span>
        </label>

        <x-dash.button type="submit" class="w-full" loading-target="login" icon="fa-solid fa-arrow-right-to-bracket">
            Masuk
        </x-dash.button>
    </form>

    @endif

    @if ($awaitingTwoFactor)
        <form wire:submit="verifyTwoFactor" class="space-y-4">
            <div class="flex items-start gap-2.5 rounded-xl p-3"
                 style="background-color: color-mix(in srgb, var(--primary) 8%, transparent);">
                <i class="fa-solid fa-shield-halved mt-0.5 text-xs" style="color: var(--primary);"></i>
                <p class="text-xs leading-relaxed" style="color: var(--ink-soft);">
                    @if ($useRecoveryCode)
                        Masukkan salah satu kode pemulihan yang Anda simpan saat mengaktifkan 2FA.
                        Kode itu akan hangus setelah dipakai.
                    @else
                        Buka aplikasi autentikator Anda dan masukkan kode 6 digit yang sedang tampil.
                    @endif
                </p>
            </div>

            <div>
                <label for="twoFactorCode" class="dash-label">
                    {{ $useRecoveryCode ? 'Kode Pemulihan' : 'Kode Autentikator' }}
                    <span style="color: var(--danger);">*</span>
                </label>

                <input id="twoFactorCode" type="text" wire:model="twoFactorCode"
                       autocomplete="one-time-code" autofocus
                       inputmode="{{ $useRecoveryCode ? 'text' : 'numeric' }}"
                       maxlength="{{ $useRecoveryCode ? 20 : 6 }}"
                       placeholder="{{ $useRecoveryCode ? 'XXXXXXXX-XXXXXXXX' : '000000' }}"
                       @class(['dash-input mono', 'tracking-[0.3em]' => ! $useRecoveryCode, 'is-invalid' => $errors->has('twoFactorCode')])>

                @error('twoFactorCode')
                    <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
                @enderror
            </div>

            <x-dash.button type="submit" class="w-full" loading-target="verifyTwoFactor"
                           icon="fa-solid fa-arrow-right-to-bracket">
                Verifikasi
            </x-dash.button>

            <div class="flex items-center justify-between pt-1">
                <button type="button" wire:click="toggleRecoveryCode"
                        class="text-xs transition hover:underline" style="color: var(--primary);">
                    {{ $useRecoveryCode ? 'Pakai aplikasi autentikator' : 'Kehilangan perangkat?' }}
                </button>

                <button type="button" wire:click="backToPassword"
                        class="text-xs transition hover:underline" style="color: var(--ink-soft);">
                    Kembali
                </button>
            </div>
        </form>
    @endif
    @if (app(\App\Services\RecaptchaService::class)->isEnabled() && ! $awaitingTwoFactor)
        <p class="mt-5 text-[10px] leading-relaxed" style="color: var(--ink-soft);">
            Dilindungi reCAPTCHA. Berlaku
            <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer"
               class="underline">Kebijakan Privasi</a> dan
            <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer"
               class="underline">Persyaratan Layanan</a> Google.
        </p>
    @endif
    <div class="mt-8 flex items-center justify-between border-t pt-5" style="border-color: var(--hairline);">
        <a href="{{ route('index') }}" class="text-xs transition hover:underline" style="color: var(--ink-soft);">
            <i class="fa-solid fa-arrow-left mr-1 text-[10px]"></i> Kembali ke situs
        </a>

        <button type="button" onclick="window.dashToggleTheme()" class="theme-toggle-btn h-8 w-8" aria-label="Ganti tema">
            <i class="fa-solid fa-moon text-xs" data-theme-icon></i>
        </button>
    </div>
</div>
