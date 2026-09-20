@props([
    'show' => false,
    'title' => null,
    'icon' => null,
    'tone' => 'default',
    'maxWidth' => 'max-w-lg',
    'closeAction' => null,
    'onClose' => null,
])

@php
    $accent = match ($tone) {
        'danger' => 'var(--danger)',
        'success' => 'var(--success)',
        'warning' => 'var(--warning)',
        'info' => 'var(--info)',
        default => 'var(--primary)',
    };

    // Shell ini presentasional: penutupan bisa didorong lewat method Livewire
    // (alur hapus) atau ekspresi Alpine (dialog murni klien seperti logout).
    $dismiss = $closeAction
        ? 'wire:click="' . $closeAction . '"'
        : ($onClose ? 'x-on:click="' . $onClose . '"' : '');

    $escape = $closeAction
        ? '$wire.call(\'' . $closeAction . '\')'
        : ($onClose ?: '');
@endphp

@if ($show)
    <div class="dash-dialog fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
         x-data
         @if ($escape) x-on:keydown.escape.window="{{ $escape }}" @endif
         role="dialog" aria-modal="true"
         @if ($title) aria-label="{{ $title }}" @endif>

        <div class="dash-dialog-scrim absolute inset-0" {!! $dismiss !!}></div>

        <div class="dash-dialog-panel card relative w-full {{ $maxWidth }} overflow-hidden rounded-2xl">

            {{-- Garis aksen tipis: satu-satunya penanda warna, supaya nada
                 dialog terbaca tanpa blok ikon besar. --}}
            <div class="h-[3px] w-full" style="background-color: {{ $accent }};"></div>

            <div class="p-5">
                @if ($title)
                    <div class="flex items-center gap-2.5">
                        @if ($icon)
                            <i class="{{ $icon }} text-sm" style="color: {{ $accent }};"></i>
                        @endif

                        <h3 class="text-sm font-semibold" style="color: var(--ink);">{{ $title }}</h3>
                    </div>
                @endif

                <div @class(['text-xs leading-relaxed', 'mt-2.5' => $title]) style="color: var(--ink-soft);">
                    {{ $slot }}
                </div>

                @isset($footer)
                    {{-- Di ponsel tombol utama berada di atas dan selebar layar;
                         di desktop kembali sebaris di kanan. --}}
                    <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </div>
    </div>
@endif
