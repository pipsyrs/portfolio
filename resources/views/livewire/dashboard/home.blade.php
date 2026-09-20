<div class="space-y-6">

    <x-dash.page-header title="Halo, {{ Str::before($owner?->name ?? 'Admin', ' ') }}"
                        eyebrow="Ringkasan"
                        subtitle="{{ now()->translatedFormat('l, d F Y') }}">
        <x-slot:actions>
    <x-dash.button href="{{ route('dashboard.projects.create') }}" wire:navigate
                           icon="fa-solid fa-plus" size="sm">
                Project Baru
            </x-dash.button>
        </x-slot:actions>
    </x-dash.page-header>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-dash.stat label="Pengunjung Hari Ini" :value="$this->stats['visitors_today']"
                     icon="fa-solid fa-user-group" tone="primary"
                     hint="{{ $this->stats['visitors_week'] }} minggu ini" />

        <x-dash.stat label="Total Pengunjung" :value="$this->stats['visitors_total']"
                     icon="fa-solid fa-chart-simple" tone="info" />

        <x-dash.stat label="Projects" :value="$this->stats['projects']"
                     icon="fa-solid fa-folder-open" tone="success"
                     hint="{{ $this->stats['tech_stacks'] }} tech · {{ $this->stats['specializations'] }} spesialisasi" />

        <x-dash.stat label="Pesan Masuk" :value="$this->stats['contacts']"
                     icon="fa-solid fa-envelope" tone="warning"
                     hint="{{ $this->stats['contacts_week'] }} minggu ini" />
    </div>

    <livewire:dashboard.visitors.chart />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <livewire:dashboard.visitors.table />
        </div>

        <x-dash.card title="Pesan Terbaru" icon="fa-solid fa-inbox" padding="p-0">
            <x-slot:actions>
                <a href="{{ route('dashboard.contacts') }}" wire:navigate
                   class="mono text-[11px] transition hover:underline" style="color: var(--primary);">
                    LIHAT SEMUA
                </a>
            </x-slot:actions>

            @forelse ($this->recentContacts as $contact)
                <a href="{{ route('dashboard.contacts') }}" wire:navigate
                   class="flex items-start gap-3 border-b px-5 py-3.5 transition last:border-b-0 hover:bg-[color-mix(in_srgb,var(--primary)_4%,transparent)]"
                   style="border-color: color-mix(in srgb, var(--hairline) 60%, transparent);">

                    <span class="mono flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[11px] font-medium"
                          style="background-color: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary);">
                        {{ strtoupper(substr($contact->name, 0, 2)) }}
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-xs font-medium" style="color: var(--ink);">{{ $contact->name }}</span>
                        <span class="block truncate text-xs" style="color: var(--ink-soft);">{{ $contact->subject }}</span>
                        <span class="mono mt-1 block text-[10px]" style="color: var(--ink-soft);">
                            {{ $contact->created_at?->diffForHumans() }}
                        </span>
                    </span>
                </a>
            @empty
                <x-dash.empty-state icon="fa-solid fa-inbox" title="Belum ada pesan"
                                    description="Pesan dari formulir kontak landing page akan muncul di sini." />
            @endforelse
        </x-dash.card>
    </div>

    @if ($this->topCountries->isNotEmpty())
        <x-dash.card title="Asal Pengunjung" icon="fa-solid fa-globe">
            @php $max = $this->topCountries->max('total') ?: 1; @endphp

            <div class="space-y-3">
                @foreach ($this->topCountries as $row)
                    <div class="flex items-center gap-3">
                        <span class="w-32 shrink-0 truncate text-xs" style="color: var(--ink);">{{ $row->country }}</span>

                        <span class="h-1.5 flex-1 overflow-hidden rounded-full" style="background-color: var(--surface-alt);">
                            <span class="block h-full rounded-full"
                                  style="width: {{ round($row->total / $max * 100) }}%; background-color: var(--primary);"></span>
                        </span>

                        <span class="mono w-10 shrink-0 text-right text-[11px]" style="color: var(--ink-soft);">{{ $row->total }}</span>
                    </div>
                @endforeach
            </div>
        </x-dash.card>
    @endif
</div>
