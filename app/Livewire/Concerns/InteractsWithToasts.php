<?php

namespace App\Livewire\Concerns;

/**
 * Satu jalur untuk semua umpan balik toast. Komponen memanggil method ini,
 * dashboard.js menerjemahkannya menjadi toast SweetAlert2.
 */
trait InteractsWithToasts
{
    public function toastSuccess(string $message, ?string $title = null): void
    {
        $this->toast('success', $message, $title);
    }

    public function toastError(string $message, ?string $title = null): void
    {
        $this->toast('error', $message, $title);
    }

    public function toastInfo(string $message, ?string $title = null): void
    {
        $this->toast('info', $message, $title);
    }

    public function toastWarning(string $message, ?string $title = null): void
    {
        $this->toast('warning', $message, $title);
    }

    protected function toast(string $type, string $message, ?string $title = null): void
    {
        $this->dispatch('toast', type: $type, message: $message, title: $title);
    }
}
