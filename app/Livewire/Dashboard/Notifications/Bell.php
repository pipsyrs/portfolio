<?php

namespace App\Livewire\Dashboard\Notifications;

use Livewire\Attributes\Computed;
use Livewire\Component;

class Bell extends Component
{
    /**
     * Ditegakkan dari browser saat soket Reverb benar-benar tersambung.
     * Selama false, lonceng bergantung sepenuhnya pada polling.
     */
    public bool $realtime = false;

    public function markAsRead(string $id): void
    {
        $notification = auth()->user()?->unreadNotifications()->whereKey($id)->first();

        $notification?->markAsRead();

        unset($this->recent, $this->unreadCount);
    }

    public function markAllAsRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();

        unset($this->recent, $this->unreadCount);
    }

    #[Computed]
    public function unreadCount(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    #[Computed]
    public function recent()
    {
        return auth()->user()?->notifications()->latest()->limit(6)->get() ?? collect();
    }

    public function render()
    {
        $interval = max(10, (int) settings('notification_polling'));

        return view('livewire.dashboard.notifications.bell', [
            // Saat Reverb tersambung, polling hanya jadi jaring pengaman yang
            // jarang. Saat tidak, interval dari Pengaturan yang dipakai supaya
            // bisa dinaikkan di server dengan sumber daya terbatas.
            'pollInterval' => ($this->realtime ? max($interval, 300) : $interval).'s',
            'userId' => (string) (auth()->id() ?? ''),
        ]);
    }
}
