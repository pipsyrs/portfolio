<div class="space-y-6">

    <x-dash.page-header title="Spesialisasi"
                        eyebrow="Konten"
                        subtitle="{{ $items->total() }} item terdaftar.">
        <x-slot:actions>
    <div class="flex items-center gap-2">
                <div class="w-52">
                    <x-dash.search wire:model.live.debounce.400ms="search" placeholder="Cari…" />
        </x-slot:actions>
    </x-dash.page-header>

            <x-dash.button wire:click="create" icon="fa-solid fa-plus">Tambah</x-dash.button>
        </div>
    </div>

    @if ($items->isEmpty())
        <x-dash.card padding="p-0">
            <x-dash.empty-state icon="fa-solid fa-bullseye"
                                title="{{ $search ? 'Tidak ada yang cocok' : 'Belum ada data' }}"
                                description="{{ $search ? 'Coba kata kunci lain.' : 'Tambahkan item pertama agar bisa dipakai pada project.' }}">
                <x-slot:action>
                    <x-dash.button wire:click="create" icon="fa-solid fa-plus" size="sm">Tambah</x-dash.button>
                </x-slot:action>
            </x-dash.empty-state>
        </x-dash.card>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($items as $item)
                <div wire:key="item-{{ $item->id }}"
                     class="card card-hover group flex items-center gap-3 rounded-xl p-3.5">

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                          style="background-color: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary);">
                        <i class="{{ $item->icon }} text-sm"></i>
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium" style="color: var(--ink);">{{ $item->name }}</p>
                        <p class="mono text-[10px]" style="color: var(--ink-soft);">
                            {{ $item->projects_count }} project
                        </p>
                    </div>

                    <div class="flex shrink-0 gap-1 opacity-0 transition group-hover:opacity-100 focus-within:opacity-100">
                        <button type="button" wire:click="edit('{{ $item->id }}')"
                                class="dash-icon-btn"
                                title="Ubah">
                            <i class="fa-solid fa-pen text-[10px]"></i>
                        </button>

                        <button type="button" wire:click="confirmDelete('{{ $item->id }}')"
                                class="dash-icon-btn is-danger"
                                title="Hapus">
                            <i class="fa-solid fa-trash text-[10px]"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($items->hasPages())
            <div>{{ $items->links() }}</div>
        @endif
    @endif

    <x-dash.modal :show="$showForm" close-action="closeForm" max-width="max-w-xl"
                  title="{{ $editingId ? 'Ubah Data' : 'Tambah Data' }}"
                  icon="fa-solid fa-pen-to-square">

        <form wire:submit="save" class="space-y-4">
            <x-dash.input label="Nama" name="name" required wire:model="name" placeholder="Backend Development" />

            <x-dash.icon-picker name="icon" :value="$icon" wire:model.live="icon" />

            <div class="flex justify-end gap-2 pt-2">
                <x-dash.button variant="secondary" size="sm" wire:click="closeForm">Batal</x-dash.button>
                <x-dash.button type="submit" size="sm" loading-target="save" icon="fa-solid fa-check">Simpan</x-dash.button>
            </div>
        </form>
    </x-dash.modal>

    <x-dash.confirm :show="$deletingId !== null" title="Hapus item ini?"
                    confirm="delete" cancel="cancelDelete" confirm-label="Ya, hapus">
        Item dilepas dari semua project yang memakainya, lalu dihapus permanen.
    </x-dash.confirm>
</div>
