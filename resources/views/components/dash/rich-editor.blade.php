@props(['label' => null, 'name', 'value' => '', 'hint' => null, 'required' => false])

@php
    $buttons = [
        ['cmd' => 'bold', 'icon' => 'fa-solid fa-bold', 'title' => 'Tebal'],
        ['cmd' => 'italic', 'icon' => 'fa-solid fa-italic', 'title' => 'Miring'],
        ['cmd' => 'underline', 'icon' => 'fa-solid fa-underline', 'title' => 'Garis bawah'],
        ['cmd' => 'strikeThrough', 'icon' => 'fa-solid fa-strikethrough', 'title' => 'Coret'],
        ['cmd' => 'formatBlock:h2', 'icon' => 'fa-solid fa-heading', 'title' => 'Judul'],
        ['cmd' => 'formatBlock:blockquote', 'icon' => 'fa-solid fa-quote-left', 'title' => 'Kutipan'],
        ['cmd' => 'insertUnorderedList', 'icon' => 'fa-solid fa-list-ul', 'title' => 'Daftar'],
        ['cmd' => 'insertOrderedList', 'icon' => 'fa-solid fa-list-ol', 'title' => 'Daftar bernomor'],
        ['cmd' => 'createLink', 'icon' => 'fa-solid fa-link', 'title' => 'Tautan'],
        ['cmd' => 'removeFormat', 'icon' => 'fa-solid fa-eraser', 'title' => 'Hapus format'],
    ];
@endphp

{{--
    Editor ini hanya menghasilkan subset HTML yang sama dengan yang sudah
    didukung kelas .prose-content di landing. Apa pun yang lolos dari sini tetap
    disaring ulang oleh SanitizeRichText saat disimpan.
--}}
<div x-data="dashRichEditor(@js($value))" x-modelable="content" wire:model="{{ $name }}">
    @if ($label)
        <label class="dash-label">
            {{ $label }}
            @if ($required)<span style="color: var(--danger);">*</span>@endif
        </label>
    @endif

    <div class="overflow-hidden rounded-xl border" style="border-color: var(--hairline);">
        <div class="flex flex-wrap items-center gap-0.5 border-b p-1.5"
             style="border-color: var(--hairline); background-color: var(--surface-alt);">
            @foreach ($buttons as $button)
                <button type="button" title="{{ $button['title'] }}"
                        @click="run('{{ $button['cmd'] }}')"
                        class="flex h-7 w-7 items-center justify-center rounded-md transition hover:bg-[color-mix(in_srgb,var(--ink)_8%,transparent)]"
                        style="color: var(--ink-soft);">
                    <i class="{{ $button['icon'] }} text-[11px]"></i>
                </button>
            @endforeach
        </div>

        <div x-ref="editor" contenteditable="true"
             @input="sync()" @blur="sync()"
             class="prose-content min-h-[160px] px-4 py-3 text-sm focus:outline-none"
             style="background-color: var(--surface);"></div>
    </div>

    @if ($hint)<p class="dash-hint">{{ $hint }}</p>@endif

    @error($name)
        <p class="dash-error"><i class="fa-solid fa-circle-exclamation text-[10px]"></i>{{ $message }}</p>
    @enderror
</div>
