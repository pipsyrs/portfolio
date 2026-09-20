<x-dash.card title="Kunjungan Terakhir" icon="fa-solid fa-clock-rotate-left" padding="p-0">

    <x-slot:actions>
        <div class="w-44">
            <x-dash.search wire:model.live.debounce.400ms="search" placeholder="Cari IP / lokasi…" />
        </div>
    </x-slot:actions>

    @if ($visitors->isEmpty())
        <x-dash.empty-state icon="fa-solid fa-user-slash" title="Belum ada kunjungan"
                            description="Data kunjungan tercatat saat ada yang membuka landing page." />
    @else
        <div class="overflow-x-auto">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Lokasi</th>
                        <th class="hidden sm:table-cell">Perangkat</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($visitors as $visitor)
                        <tr wire:key="visitor-{{ $visitor->id }}">
                            <td>
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-location-dot text-[10px]" style="color: var(--primary);"></i>
                                    <span class="truncate">
                                        {{ $visitor->city ? $visitor->city . ', ' : '' }}{{ $visitor->country ?: 'Tidak diketahui' }}
                                    </span>
                                </div>
                                <span class="mono text-[10px]" style="color: var(--ink-soft);">{{ $visitor->ip_address }}</span>
                            </td>
                            <td class="hidden max-w-[240px] sm:table-cell">
                                <span class="block truncate text-xs" style="color: var(--ink-soft);"
                                      title="{{ $visitor->user_agent }}">
                                    {{ Str::limit($visitor->user_agent, 48) }}
                                </span>
                            </td>
                            <td>
                                <span class="mono text-[11px]" style="color: var(--ink-soft);">
                                    {{ $visitor->created_at?->diffForHumans() }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($visitors->hasPages())
            <div class="border-t px-5 py-3" style="border-color: var(--hairline);">
                {{ $visitors->links() }}
            </div>
        @endif
    @endif
</x-dash.card>
