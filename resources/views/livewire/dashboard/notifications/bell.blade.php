{{-- Polling tetap dipasang sebagai cadangan: bila Reverb mati, lonceng masih
     menyusul dengan sendirinya. --}}
<div x-data="dashNotificationBell('{{ $userId }}')" class="relative" wire:poll.{{ $pollInterval }}>

    <button type="button" @click="open = !open" class="theme-toggle-btn relative h-9 w-9" aria-label="Notifikasi">
        <i class="fa-solid fa-bell text-sm"></i>

        @if ($this->unreadCount > 0)
            <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full px-1 text-[9px] font-medium text-white"
                  style="background-color: var(--danger);">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    <div x-cloak x-show="open" @click.outside="open = false" x-transition.origin.top.right
         class="card absolute right-0 mt-2 w-80 rounded-xl shadow-lg">

        <div class="flex items-center justify-between border-b px-4 py-3" style="border-color: var(--hairline);">
            <span class="text-xs font-medium" style="color: var(--ink);">Notifikasi</span>

            @if ($this->unreadCount > 0)
                <button type="button" wire:click="markAllAsRead"
                        class="mono text-[10px] transition hover:underline" style="color: var(--primary);">
                    TANDAI DIBACA
                </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse ($this->recent as $notification)
                @php $data = $notification->data; @endphp

                <a href="{{ $data['url'] ?? '#' }}" wire:navigate
                   wire:click="markAsRead('{{ $notification->id }}')"
                   wire:key="notif-{{ $notification->id }}"
                   class="flex items-start gap-3 border-b px-4 py-3 transition last:border-b-0 hover:bg-[color-mix(in_srgb,var(--primary)_5%,transparent)]"
                   style="border-color: color-mix(in srgb, var(--hairline) 60%, transparent);">

                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
                          style="background-color: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary);">
                        <i class="{{ $data['icon'] ?? 'fa-solid fa-bell' }} text-[11px]"></i>
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="block text-xs font-medium" style="color: var(--ink);">{{ $data['title'] ?? 'Notifikasi' }}</span>
                        <span class="mt-0.5 block text-[11px] leading-relaxed" style="color: var(--ink-soft);">
                            {{ Str::limit($data['body'] ?? '', 70) }}
                        </span>
                        <span class="mono mt-1 block text-[10px]" style="color: var(--ink-soft);">
                            {{ $notification->created_at?->diffForHumans() }}
                        </span>
                    </span>

                    @if (! $notification->read_at)
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full" style="background-color: var(--primary);"></span>
                    @endif
                </a>
            @empty
                <p class="px-4 py-8 text-center text-xs" style="color: var(--ink-soft);">Belum ada notifikasi.</p>
            @endforelse
        </div>

        <a href="{{ route('dashboard.notifications') }}" wire:navigate
           class="block border-t px-4 py-2.5 text-center text-xs transition hover:underline"
           style="border-color: var(--hairline); color: var(--primary);">
            Lihat semua
        </a>
    </div>
</div>
