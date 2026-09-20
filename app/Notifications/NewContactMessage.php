<?php

namespace App\Notifications;

use App\Models\Contacts;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewContactMessage extends Notification
{
    use Queueable;

    public function __construct(public Contacts $contact) {}

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
            'type' => 'contact',
            'title' => 'Pesan baru dari '.$this->contact->name,
            'body' => Str::limit($this->contact->message, 120),
            'icon' => 'fa-solid fa-envelope',
            'color' => 'info',
            'url' => route('dashboard.contacts'),
            'contact_id' => $this->contact->id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
