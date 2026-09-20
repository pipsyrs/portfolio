<div class="space-y-6">

    <x-dash.page-header title="Backup Database"
                        eyebrow="Sistem"
                        subtitle="{{ count($backups) }} berkas · total {{ $totalSize }}">
        <x-slot:actions>
            <x-dash.button wire:click="confirmBackup" icon="fa-solid fa-cloud-arrow-down">
                Backup Sekarang
            </x-dash.button>
        </x-slot:actions>
    </x-dash.page-header>

    <x-dash.card title="Kesiapan Sistem" icon="fa-solid fa-stethoscope"
                 subtitle="Diperiksa langsung dari server, bukan asumsi.">
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($this->diagnostics as $check)
                <div class="flex items-start gap-2.5 rounded-xl p-3"
                     style="background-color: color-mix(in srgb, {{ $check['ok'] ? 'var(--success)' : 'var(--danger)' }} 7%, transparent);">
                    <i class="fa-solid {{ $check['ok'] ? 'fa-circle-check' : 'fa-circle-xmark' }} mt-0.5 text-xs"
                       style="color: {{ $check['ok'] ? 'var(--success)' : 'var(--danger)' }};"></i>
                    <div class="min-w-0">
                        <p class="text-xs" style="color: var(--ink);">{{ $check['label'] }}</p>
                        <p class="mono mt-0.5 break-words text-[10px] leading-relaxed" style="color: var(--ink-soft);">
                            {{ $check['detail'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-xs leading-relaxed" style="color: var(--ink-soft);">
            Berkas baru dienkripsi AES-256 dengan kunci terpisah dari <span class="mono">APP_KEY</span>,
            disimpan di luar direktori publik, dan hanya bisa diunduh lewat tautan bertanda tangan
            berumur 5 menit. Jadwal otomatis diatur di
            <a href="{{ route('dashboard.settings') }}" wire:navigate class="underline" style="color: var(--primary);">Pengaturan</a>.
        </p>

        <div class="mt-3 flex items-start gap-2.5 rounded-xl p-3"
             style="background-color: color-mix(in srgb, var(--warning) 8%, transparent);">
            <i class="fa-solid fa-key mt-0.5 text-xs" style="color: var(--warning);"></i>
            <p class="text-xs leading-relaxed" style="color: var(--ink-soft);">
                Simpan salinan <span class="mono">BACKUP_ENCRYPTION_KEY</span> di luar server.
                Tanpa kunci itu tidak ada berkas backup yang bisa dipulihkan.
            </p>
        </div>
    </x-dash.card>

    <x-dash.card padding="p-0">
        @if (empty($backups))
            <x-dash.empty-state icon="fa-solid fa-database" title="Belum ada backup"
                                description="Jalankan backup pertama atau tunggu jadwal otomatis berikutnya.">
                <x-slot:action>
                    <x-dash.button wire:click="confirmBackup" size="sm" icon="fa-solid fa-cloud-arrow-down">
                        Backup Sekarang
                    </x-dash.button>
                </x-slot:action>
            </x-dash.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Nama Berkas</th>
                            <th>Ukuran</th>
                            <th class="hidden sm:table-cell">Dibuat</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($backups as $backup)
                            <tr wire:key="backup-{{ $backup['name'] }}">
                                <td>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <i class="fa-solid {{ $backup['encrypted'] ? 'fa-lock' : 'fa-file-zipper' }} text-xs"
                                           style="color: {{ $backup['encrypted'] ? 'var(--success)' : 'var(--warning)' }};"></i>
                                        <span class="mono text-xs">{{ $backup['name'] }}</span>

                                        @unless ($backup['encrypted'])
                                            <x-dash.badge tone="warning">TANPA ENKRIPSI</x-dash.badge>
                                        @endunless
                                    </div>
                                </td>
                                <td><span class="mono text-xs" style="color: var(--ink-soft);">{{ $backup['size'] }}</span></td>
                                <td class="hidden sm:table-cell">
                                    <span class="mono text-[11px]" style="color: var(--ink-soft);">{{ $backup['date'] }}</span>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-1">
                                        <button type="button" wire:click="verifyFile('{{ $backup['name'] }}')"
                                                class="dash-icon-btn" title="Periksa keutuhan berkas">
                                            <i class="fa-solid fa-shield-halved text-[10px]"></i>
                                        </button>

                                        <a href="{{ $this->downloadUrl($backup['name']) }}"
                                           class="dash-icon-btn is-primary" title="Unduh">
                                            <i class="fa-solid fa-download text-[10px]"></i>
                                        </a>

                                        <button type="button" wire:click="confirmDelete('{{ $backup['name'] }}')"
                                                class="dash-icon-btn is-danger" title="Hapus">
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-dash.card>

    <x-dash.confirm :show="$confirmingBackup" title="Jalankan backup sekarang?"
                    tone="warning" icon="fa-solid fa-database"
                    confirm="backup" cancel="cancelBackup" confirm-label="Ya, jalankan" blocking>
        mysqldump akan berjalan di server ini. Pada VPS kecil prosesnya bisa
        memakan CPU dan memori selama beberapa saat.
    </x-dash.confirm>

    <x-dash.confirm :show="$deletingFile !== null" title="Hapus berkas backup?"
                    confirm="delete" cancel="cancelDelete" confirm-label="Ya, hapus">
        Berkas <span class="mono">{{ $deletingFile }}</span> dihapus permanen dari server.
    </x-dash.confirm>
</div>
