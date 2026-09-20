<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class BackupCompleted extends Notification
{
    use Queueable;

    public function __construct(
        public string $filename,
        public string $size,
        public bool $automatic = false,
    ) {}

    /**
     * Selain disimpan, notifikasi disiarkan lewat Reverb supaya lonceng
     * dashboard ikut berubah tanpa menunggu polling berikutnya. Bila
     * Reverb mati, baris database tetap ada dan polling yang menyusul.
     */
    public function via(object $notifiable): array
    {
        // Siarkan hanya bila broadcaster benar-benar terkonfigurasi.
        // Tanpa ini, Reverb yang belum diisi kuncinya membuat pengiriman
        // notifikasi gagal total — padahal seharusnya cukup turun ke polling.
        $connection = config('broadcasting.default');
        $broadcastable = filled(config('broadcasting.connections.'.$connection.'.key'));

        return $broadcastable ? ['database', 'broadcast'] : ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'backup',
            'title' => $this->automatic ? 'Backup otomatis selesai' : 'Backup database selesai',
            'body' => $this->filename.' ('.$this->size.')',
            'icon' => 'fa-solid fa-database',
            'color' => 'success',
            'url' => route('dashboard.backup'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
