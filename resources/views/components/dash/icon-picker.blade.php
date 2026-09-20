@props(['name', 'value' => null, 'label' => 'Ikon'])

@php
    // Daftar kurasi agar pemilihan ikon tetap berjalan tanpa token FontAwesome
    // API. Pencarian tetap tersedia lewat input di bawah.
    $icons = [
        'fa-brands fa-laravel', 'fa-brands fa-php', 'fa-brands fa-js', 'fa-brands fa-node-js',
        'fa-brands fa-vuejs', 'fa-brands fa-react', 'fa-brands fa-angular', 'fa-brands fa-bootstrap',
        'fa-brands fa-html5', 'fa-brands fa-css3-alt', 'fa-brands fa-sass', 'fa-brands fa-python',
        'fa-brands fa-java', 'fa-brands fa-golang', 'fa-brands fa-rust', 'fa-brands fa-swift',
        'fa-brands fa-android', 'fa-brands fa-apple', 'fa-brands fa-docker', 'fa-brands fa-git-alt',
        'fa-brands fa-github', 'fa-brands fa-gitlab', 'fa-brands fa-aws', 'fa-brands fa-digital-ocean',
        'fa-brands fa-linux', 'fa-brands fa-ubuntu', 'fa-brands fa-figma', 'fa-brands fa-wordpress',
        'fa-solid fa-database', 'fa-solid fa-server', 'fa-solid fa-cloud', 'fa-solid fa-code',
        'fa-solid fa-terminal', 'fa-solid fa-bug', 'fa-solid fa-shield-halved', 'fa-solid fa-lock',
        'fa-solid fa-gauge-high', 'fa-solid fa-layer-group', 'fa-solid fa-cubes', 'fa-solid fa-diagram-project',
        'fa-solid fa-mobile-screen', 'fa-solid fa-desktop', 'fa-solid fa-palette', 'fa-solid fa-pen-ruler',
        'fa-solid fa-chart-line', 'fa-solid fa-robot', 'fa-solid fa-brain', 'fa-solid fa-network-wired',
        'fa-solid fa-plug', 'fa-solid fa-gears', 'fa-solid fa-rocket', 'fa-solid fa-wand-magic-sparkles',
    ];
@endphp

<div x-data="{ q: '' }">
    <label class="dash-label">{{ $label }} <span style="color: var(--danger);">*</span></label>

    <div class="rounded-xl border p-3" style="border-color: var(--hairline);">
        <div class="mb-3 flex items-center gap-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg"
                 style="background-color: var(--surface-alt); color: var(--primary);">
                @if ($value)
                    <i class="{{ $value }} text-sm"></i>
                @else
                    <i class="fa-regular fa-circle-question text-sm" style="color: var(--ink-soft);"></i>
                @endif
            </div>

            <input type="text" x-model="q" placeholder="Cari ikon…" class="dash-input flex-1 py-1.5 text-xs">
        </div>

        <div class="grid max-h-44 grid-cols-8 gap-1.5 overflow-y-auto sm:grid-cols-10">
            @foreach ($icons as $icon)
                <button type="button"
                        wire:click="$set('{{ $name }}', '{{ $icon }}')"
                        x-show="q === '' || '{{ $icon }}'.includes(q.toLowerCase())"
                        @class([
                            'flex h-8 w-8 items-center justify-center rounded-lg border transition hover:scale-110',
                        ])
                        style="border-color: {{ $value === $icon ? 'var(--primary)' : 'transparent' }}; background-color: {{ $value === $icon ? 'color-mix(in srgb, var(--primary) 12%, transparent)' : 'var(--surface-alt)' }}; color: {{ $value === $icon ? 'var(--primary)' : 'var(--ink-soft)' }};"
                        title="{{ $icon }}">
                    <i class="{{ $icon }} text-xs"></i>
                </button>
            @endforeach
        </div>

        <p class="dash-hint">Atau tempel kelas FontAwesome sendiri, contoh: <code class="mono">fa-solid fa-star</code></p>
        <input type="text" {{ $attributes }} placeholder="fa-solid fa-star" class="dash-input mt-1.5 py-1.5 text-xs">
    </div>

    @error($name)
        <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
    @enderror
</div>
