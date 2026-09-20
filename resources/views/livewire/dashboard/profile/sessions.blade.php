<div>
    <x-dash.card title="Sesi Aktif" icon="fa-solid fa-laptop"
                 subtitle="Perangkat yang sedang memegang sesi ke akun ini." padding="p-0">

        @if ($this->sessions && count($this->sessions) > 1)
            <x-slot:actions>
                <x-dash.button variant="secondary" size="sm" wire:click="askPassword"
                               icon="fa-solid fa-right-from-bracket">
                    Akhiri sesi lain
                </x-dash.button>
            </x-slot:actions>
        @endif

        @if (! $this->supported)
            <div class="p-5">
                <div class="flex items-start gap-2.5 rounded-xl p-3"
                     style="background-color: color-mix(in srgb, var(--warning) 9%, transparent);">
                    <i class="fa-solid fa-triangle-exclamation mt-0.5 text-xs" style="color: var(--warning);"></i>
                    <p class="text-xs leading-relaxed" style="color: var(--ink-soft);">
                        Daftar sesi membutuhkan <span class="mono">SESSION_DRIVER=database</span> di berkas
                        <span class="mono">.env</span>. Saat ini memakai
                        <span class="mono">{{ config('session.driver') }}</span>.
                    </p>
                </div>
            </div>
        @elseif (empty($this->sessions))
            <x-dash.empty-state icon="fa-solid fa-laptop" title="Belum ada sesi tercatat"
                                description="Sesi akan muncul di sini setelah login berikutnya." />
        @else
            @foreach ($this->sessions as $item)
                <div wire:key="sess-{{ $item['id'] }}"
                     class="flex items-start gap-4 border-b px-5 py-4 last:border-b-0"
                     style="border-color: color-mix(in srgb, var(--hairline) 60%, transparent);">

                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                          style="background-color: color-mix(in srgb, {{ $item['is_current'] ? 'var(--success)' : 'var(--ink)' }} 10%, transparent);
                                 color: {{ $item['is_current'] ? 'var(--success)' : 'var(--ink-soft)' }};">
                        <i class="{{ $item['device']['icon'] }} text-xs"></i>
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm" style="color: var(--ink);">
                                {{ $item['device']['browser'] }} · {{ $item['device']['platform'] }}
                            </p>

                            @if ($item['is_current'])
                                <x-dash.badge tone="success">PERANGKAT INI</x-dash.badge>
                            @endif
                        </div>

                        <p class="mono mt-1 text-[11px]" style="color: var(--ink-soft);">
                            {{ $item['ip'] }} · aktif {{ $item['last_active']->diffForHumans() }}
                        </p>

                        <p class="mono mt-1 break-all text-[10px]" style="color: var(--ink-soft); opacity: 0.7;">
                            {{ $item['agent'] }}
                        </p>
                    </div>

                    @unless ($item['is_current'])
                        <button type="button" wire:click="confirmRevoke('{{ $item['id'] }}')"
                                class="dash-icon-btn is-danger" title="Akhiri sesi ini">
                            <i class="fa-solid fa-xmark text-[11px]"></i>
                        </button>
                    @endunless
                </div>
            @endforeach
        @endif
    </x-dash.card>

    <x-dash.confirm :show="$revokingId !== null" title="Akhiri sesi perangkat ini?"
                    confirm="revoke" cancel="cancelRevoke" confirm-label="Ya, akhiri">
        Perangkat itu akan langsung diminta masuk kembali. Perangkat lain tidak terpengaruh.
    </x-dash.confirm>

    <x-dash.modal :show="$askingPassword" close-action="cancelPassword" max-width="max-w-md"
                  title="Akhiri semua sesi lain" icon="fa-solid fa-shield-halved" tone="warning">

        <form wire:submit="revokeOthers" class="space-y-4">
            <p>
                Semua perangkat selain yang ini akan dikeluarkan. Masukkan kata sandi
                untuk memastikan permintaan ini benar dari Anda.
            </p>

            <x-dash.input label="Kata Sandi" name="password" type="password" required
                          wire:model="password" autocomplete="current-password" />

            <div class="flex justify-end gap-2 pt-1">
                <x-dash.button variant="secondary" size="sm" wire:click="cancelPassword">Batal</x-dash.button>
                <x-dash.button type="submit" variant="danger" size="sm" loading-target="revokeOthers">
                    Akhiri sesi lain
                </x-dash.button>
            </div>
        </form>
    </x-dash.modal>
</div>
