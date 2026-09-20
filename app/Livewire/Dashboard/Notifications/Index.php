<?php

namespace App\Livewire\Dashboard\Notifications;

use App\Livewire\Concerns\InteractsWithToasts;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
#[Title('Notifikasi')]
class Index extends Component
{
    use InteractsWithToasts, WithPagination;

    #[Url(as: 'filter', keep: false)]
    public string $filter = 'all';

    #[Locked]
    public ?string $deletingId = null;

    public bool $clearingAll = false;

    public function updatedFilter(): void
    {
        if (! in_array($this->filter, ['all', 'unread'], true)) {
            $this->filter = 'all';
        }

        $this->resetPage();
    }

    public function markAsRead(string $id): void
    {
        auth()->user()?->unreadNotifications()->whereKey($id)->first()?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();

        $this->toastSuccess('Semua notifikasi ditandai sudah dibaca.');
    }

    public function delete(string $id): void
    {
        $this->authorize('owner');

        // Dibatasi ke notifikasi milik user yang sedang masuk.
        auth()->user()?->notifications()->whereKey($id)->delete();

        $this->toastSuccess('Notifikasi dihapus.');
    }

    public function confirmClearAll(): void
    {
        $this->clearingAll = true;
    }

    public function cancelClearAll(): void
    {
        $this->clearingAll = false;
    }

    public function clearAll(): void
    {
        $this->authorize('owner');

        $this->clearingAll = false;

        auth()->user()?->notifications()->delete();

        $this->resetPage();
        $this->toastSuccess('Semua notifikasi dihapus.');
    }

    public function render()
    {
        $user = auth()->user();

        $query = $this->filter === 'unread'
            ? $user->unreadNotifications()
            : $user->notifications();

        return view('livewire.dashboard.notifications.index', [
            'notifications' => $query->latest()->paginate(15),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
