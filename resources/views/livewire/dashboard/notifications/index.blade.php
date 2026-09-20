<div class="space-y-6">

    <x-dash.page-header title="Notifikasi"
                        eyebrow="Sistem"
                        subtitle="{{ $unreadCount }} belum dibaca dari {{ $notifications->total() }} total.">
        <x-slot:actions>
    <div class="flex items-center gap-2">
                <div class="flex items-center gap-1 rounded-lg p-0.5" style="background-color: var(--surface-alt);">
                    @foreach (['all' => 'Semua', 'unread' => 'Belum dibaca'] as $key => $label)
                        <button type="button" wire:click="$set('filter', '{{ $key }}')"
                                class="rounded-md px-3 py-1.5 text-[11px] font-medium transition"
                                style="{{ $filter === $key ? 'background-color: var(--primary); color: #fff;' : 'color: var(--ink-soft);' }}">
                            {{ $label }}
                        </button>
                    @endforeach
        </x-slot:actions>
    </x-dash.page-header>

            @if ($unreadCount > 0)
                <x-dash.button variant="secondary" size="sm" wire:click="markAllAsRead" icon="fa-solid fa-check-double">
                    Tandai dibaca
                </x-dash.button>
            @endif

            @if ($notifications->total() > 0)
                <x-dash.button variant="ghost" size="sm" wire:click="confirmClearAll" icon="fa-solid fa-trash">
                    Bersihkan
                </x-dash.button>
            @endif
        </div>
    </div>

    <x-dash.card padding="p-0">
        @if ($notifications->isEmpty())
            <x-dash.empty-state icon="fa-solid fa-bell-slash"
                                title="{{ $filter === 'unread' ? 'Semua sudah dibaca' : 'Belum ada notifikasi' }}"
                                description="Notifikasi pesan masuk dan hasil backup database akan muncul di sini." />
        @else
            @foreach ($notifications as $notification)
                @php $data = $notification->data; @endphp

                <div wire:key="n-{{ $notification->id }}"
                     class="flex items-start gap-4 border-b px-5 py-4 transition last:border-b-0"
                     style="border-color: color-mix(in srgb, var(--hairline) 60%, transparent); {{ $notification->read_at ? '' : 'background-color: color-mix(in srgb, var(--primary) 4%, transparent);' }}">

                    <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                          style="background-color: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary);">
                        <i class="{{ $data['icon'] ?? 'fa-solid fa-bell' }} text-xs"></i>
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="truncate text-sm font-medium" style="color: var(--ink);">{{ $data['title'] ?? 'Notifikasi' }}</p>
                            @if (! $notification->read_at)
                                <x-dash.badge tone="primary">BARU</x-dash.badge>
                            @endif
                        </div>

                        <p class="mt-1 text-xs leading-relaxed" style="color: var(--ink-soft);">{{ $data['body'] ?? '' }}</p>

                        <p class="mono mt-2 text-[10px]" style="color: var(--ink-soft);">
                            {{ $notification->created_at?->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>

                    <div class="flex shrink-0 gap-1">
                        @if (isset($data['url']))
                            <a href="{{ $data['url'] }}" wire:navigate wire:click="markAsRead('{{ $notification->id }}')"
                               class="dash-icon-btn" title="Buka">
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        @endif

                        <button type="button" wire:click="delete('{{ $notification->id }}')"
                                class="dash-icon-btn is-danger"
                                title="Hapus">
                            <i class="fa-solid fa-xmark text-[11px]"></i>
                        </button>
                    </div>
                </div>
            @endforeach

            @if ($notifications->hasPages())
                <div class="border-t px-5 py-3" style="border-color: var(--hairline);">{{ $notifications->links() }}</div>
            @endif
        @endif
    </x-dash.card>

    <x-dash.confirm :show="$clearingAll" title="Bersihkan semua notifikasi?"
                    confirm="clearAll" cancel="cancelClearAll" confirm-label="Ya, hapus semua">
        Seluruh notifikasi dihapus permanen dan tidak bisa dikembalikan.
    </x-dash.confirm>
</div>
