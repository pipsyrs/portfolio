<x-dash.card title="Grafik Pengunjung" icon="fa-solid fa-chart-line"
             subtitle="{{ $chart['total'] }} kunjungan · puncak {{ $chart['peak'] }} · rata-rata {{ $chart['average'] }}/hari">

    <x-slot:actions>
        <div class="flex items-center gap-1 rounded-lg p-0.5" style="background-color: var(--surface-alt);">
            @foreach ($ranges as $range)
                <button type="button" wire:click="setRange({{ $range }})"
                        class="mono rounded-md px-2.5 py-1 text-[11px] font-medium transition"
                        style="{{ $days === $range ? 'background-color: var(--primary); color: #fff;' : 'color: var(--ink-soft);' }}">
                    {{ $range }}H
                </button>
            @endforeach
        </div>
    </x-slot:actions>

    <div class="relative h-[240px]" wire:loading.class="opacity-40" wire:target="setRange">
        {{-- wire:key memaksa canvas dibuat ulang saat rentang berubah, supaya
             Chart.js tidak menempel pada instance canvas yang sudah dibuang. --}}
        <canvas id="visitor-chart-{{ $days }}"
                wire:key="chart-{{ $days }}"
                wire:ignore
                data-chart="{{ json_encode(['labels' => $chart['labels'], 'data' => $chart['data']]) }}"></canvas>
    </div>
</x-dash.card>
