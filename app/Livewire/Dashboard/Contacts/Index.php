<?php

namespace App\Livewire\Dashboard\Contacts;

use App\Livewire\Concerns\InteractsWithToasts;
use App\Mail\ContactReply;
use App\Models\Contacts;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.dashboard')]
#[Title('Pesan Masuk')]
class Index extends Component
{
    use InteractsWithToasts, WithPagination;

    #[Url(as: 'q', keep: false)]
    public string $search = '';

    #[Locked]
    public ?string $viewingId = null;

    #[Locked]
    public ?string $replyingId = null;

    #[Locked]
    public ?string $deletingId = null;

    public string $replyMessage = '';

    private const REPLY_LIMIT = 10;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function view(string $id): void
    {
        $this->viewingId = Contacts::whereKey($id)->value('id');
    }

    public function closeView(): void
    {
        $this->viewingId = null;
    }

    public function startReply(string $id): void
    {
        $this->replyingId = Contacts::whereKey($id)->value('id');
        $this->replyMessage = '';
        $this->viewingId = null;
        $this->resetValidation();
    }

    public function cancelReply(): void
    {
        $this->reset(['replyingId', 'replyMessage']);
        $this->resetValidation();
    }

    public function sendReply(): void
    {
        $this->authorize('owner');

        if ($this->replyingId === null) {
            return;
        }

        $this->validate(
            ['replyMessage' => ['required', 'string', 'min:5', 'max:5000']],
            [
                'replyMessage.required' => 'Isi balasan wajib diisi.',
                'replyMessage.min' => 'Balasan minimal 5 karakter.',
                'replyMessage.max' => 'Balasan maksimal 5000 karakter.',
            ],
        );

        $key = 'contact-reply:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, self::REPLY_LIMIT)) {
            $this->toastError('Batas pengiriman balasan tercapai. Coba lagi nanti.');

            return;
        }

        $contact = Contacts::find($this->replyingId);

        if (! $contact) {
            $this->cancelReply();
            $this->toastError('Pesan sudah tidak ada.');

            return;
        }

        try {
            Mail::to($contact->email)->send(new ContactReply($this->replyMessage, $contact->subject));
            RateLimiter::hit($key, 3600);

            $this->cancelReply();
            $this->toastSuccess('Balasan terkirim ke '.$contact->email.'.');
        } catch (\Throwable $e) {
            Log::error('Contact reply failed', ['contact_id' => $contact->id, 'error' => $e->getMessage()]);

            $this->toastError('Gagal mengirim balasan. Periksa konfigurasi email.');
        }
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
    }

    public function delete(): void
    {
        $this->authorize('owner');

        if ($this->deletingId === null) {
            return;
        }

        $contact = Contacts::find($this->deletingId);
        $this->deletingId = null;

        if (! $contact) {
            return;
        }

        $name = $contact->name;
        $contact->delete();

        $this->resetPage();
        $this->toastSuccess('Pesan dari '.$name.' berhasil dihapus.');
    }

    public function render()
    {
        $contacts = Contacts::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';

                $query->where(fn ($q) => $q
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('subject', 'like', $term));
            })
            ->latest()
            ->paginate(10);

        return view('livewire.dashboard.contacts.index', [
            'contacts' => $contacts,
            'viewing' => $this->viewingId ? Contacts::find($this->viewingId) : null,
            'replying' => $this->replyingId ? Contacts::find($this->replyingId) : null,
        ]);
    }
}
