<div class="space-y-6">

    <x-dash.page-header title="Pesan Masuk"
                        eyebrow="Komunikasi"
                        subtitle="{{ $contacts->total() }} pesan dari formulir kontak.">
        <x-slot:actions>
    <div class="w-full sm:w-64">
                <x-dash.search wire:model.live.debounce.400ms="search" placeholder="Cari nama, email, subjek…" />
        </x-slot:actions>
    </x-dash.page-header>
    </div>

    <x-dash.card padding="p-0">
        @if ($contacts->isEmpty())
            <x-dash.empty-state icon="fa-solid fa-inbox"
                                title="{{ $search ? 'Tidak ada pesan yang cocok' : 'Belum ada pesan' }}"
                                description="Pesan dari formulir kontak landing page akan muncul di sini." />
        @else
            <div class="overflow-x-auto">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Pengirim</th>
                            <th class="hidden md:table-cell">Subjek</th>
                            <th class="hidden sm:table-cell">Waktu</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contacts as $contact)
                            <tr wire:key="contact-{{ $contact->id }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="mono flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[11px] font-medium"
                                              style="background-color: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary);">
                                            {{ strtoupper(substr($contact->name, 0, 2)) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-medium" style="color: var(--ink);">{{ $contact->name }}</p>
                                            <p class="truncate text-[11px]" style="color: var(--ink-soft);">{{ $contact->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="hidden max-w-[260px] md:table-cell">
                                    <span class="block truncate text-xs">{{ $contact->subject }}</span>
                                </td>

                                <td class="hidden sm:table-cell">
                                    <span class="mono text-[11px]" style="color: var(--ink-soft);">
                                        {{ $contact->created_at?->diffForHumans() }}
                                    </span>
                                </td>

                                <td>
                                    <div class="flex justify-end gap-1">
                                        <button type="button" wire:click="view('{{ $contact->id }}')"
                                                class="dash-icon-btn" title="Lihat">
                                            <i class="fa-solid fa-eye text-[10px]"></i>
                                        </button>

                                        <button type="button" wire:click="startReply('{{ $contact->id }}')"
                                                class="dash-icon-btn"
                                                style="color: var(--info);" title="Balas">
                                            <i class="fa-solid fa-reply text-[10px]"></i>
                                        </button>

                                        <button type="button" wire:click="confirmDelete('{{ $contact->id }}')"
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

            @if ($contacts->hasPages())
                <div class="border-t px-5 py-3" style="border-color: var(--hairline);">{{ $contacts->links() }}</div>
            @endif
        @endif
    </x-dash.card>

    <x-dash.modal :show="$viewing !== null" close-action="closeView" max-width="max-w-2xl"
                  title="Detail Pesan" icon="fa-solid fa-envelope-open-text">
        @if ($viewing)
            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <p class="mono text-[10px] uppercase tracking-wider" style="color: var(--ink-soft);">Nama</p>
                        <p class="mt-1 text-sm" style="color: var(--ink);">{{ $viewing->name }}</p>
                    </div>
                    <div>
                        <p class="mono text-[10px] uppercase tracking-wider" style="color: var(--ink-soft);">Email</p>
                        <p class="mt-1 truncate text-sm" style="color: var(--ink);">{{ $viewing->email }}</p>
                    </div>
                    <div>
                        <p class="mono text-[10px] uppercase tracking-wider" style="color: var(--ink-soft);">Waktu</p>
                        <p class="mt-1 text-sm" style="color: var(--ink);">{{ $viewing->created_at?->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>

                <div>
                    <p class="mono text-[10px] uppercase tracking-wider" style="color: var(--ink-soft);">Subjek</p>
                    <p class="mt-1 text-sm font-medium" style="color: var(--ink);">{{ $viewing->subject }}</p>
                </div>

                <div>
                    <p class="mono text-[10px] uppercase tracking-wider" style="color: var(--ink-soft);">Pesan</p>
                    <p class="mt-1 whitespace-pre-line text-sm leading-relaxed" style="color: var(--ink-soft);">{{ $viewing->message }}</p>
                </div>
            </div>

            <x-slot:footer>
                <x-dash.button variant="secondary" size="sm" wire:click="closeView">Tutup</x-dash.button>
                <x-dash.button size="sm" icon="fa-solid fa-reply" wire:click="startReply('{{ $viewing->id }}')">Balas</x-dash.button>
            </x-slot:footer>
        @endif
    </x-dash.modal>

    <x-dash.modal :show="$replying !== null" close-action="cancelReply" max-width="max-w-xl"
                  title="Balas Pesan" icon="fa-solid fa-paper-plane" tone="info">
        @if ($replying)
            <form wire:submit="sendReply" class="space-y-4">
                <div class="rounded-xl p-3" style="background-color: var(--surface-alt);">
                    <p class="text-xs" style="color: var(--ink-soft);">
                        Kepada <span class="font-medium" style="color: var(--ink);">{{ $replying->name }}</span>
                        ({{ $replying->email }})
                    </p>
                    <p class="mt-1 text-xs" style="color: var(--ink-soft);">Perihal: {{ $replying->subject }}</p>
                </div>

                <x-dash.textarea label="Isi Balasan" name="replyMessage" rows="6" required
                                 wire:model="replyMessage" placeholder="Tulis balasan di sini…" />

                <div class="flex justify-end gap-2">
                    <x-dash.button variant="secondary" size="sm" wire:click="cancelReply">Batal</x-dash.button>
                    <x-dash.button type="submit" size="sm" loading-target="sendReply"
                                   icon="fa-solid fa-paper-plane" data-dash-blocking>Kirim</x-dash.button>
                </div>
            </form>
        @endif
    </x-dash.modal>

    <x-dash.confirm :show="$deletingId !== null" title="Hapus pesan ini?"
                    confirm="delete" cancel="cancelDelete" confirm-label="Ya, hapus">
        Pesan dihapus permanen dan tidak bisa dipulihkan.
    </x-dash.confirm>
</div>
