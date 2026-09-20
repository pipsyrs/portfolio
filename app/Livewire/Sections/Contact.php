<?php

namespace App\Livewire\Sections;

use App\Mail\ContactMail;
use App\Models\Contacts;
use App\Models\User;
use App\Notifications\NewContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Contact extends Component
{
    public $email;

    public $address;

    public $phone;

    // Form fields
    public $name = '';

    public $senderEmail = '';

    public $subject = '';

    public $message = '';

    public function mount()
    {
        $data = User::owner();
        $this->email = $data?->email;
        $this->address = $data?->address;
        $this->phone = $data?->phone;
    }

    public function sendMessage()
    {
        $key = 'contact-form:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            session()->flash('contact-error', __('Too many messages sent. Please try again later.'));

            return;
        }

        $this->validate([
            'name' => 'required|string|min:2|max:100',
            'senderEmail' => 'required|email|max:150',
            'subject' => 'required|string|min:3|max:200',
            'message' => 'required|string|min:10|max:5000',
        ], [
            'name.required' => __('Please enter your name.'),
            'name.min' => __('Name must be at least 2 characters.'),
            'senderEmail.required' => __('Please enter your email address.'),
            'senderEmail.email' => __('Please enter a valid email address.'),
            'subject.required' => __('Please enter a subject.'),
            'subject.min' => __('Subject must be at least 3 characters.'),
            'message.required' => __('Please enter your message.'),
            'message.min' => __('Message must be at least 10 characters.'),
        ]);

        RateLimiter::hit($key, 600);

        try {
            // Alamat khusus dari Pengaturan menang; email profil jadi cadangan.
            $recipientEmail = settings('contact_notification_email') ?: User::owner()?->email;

            if (blank($recipientEmail)) {
                throw new \RuntimeException('Alamat tujuan pesan kontak belum diatur.');
            }

            Mail::to($recipientEmail)->send(new ContactMail(
                senderName: $this->name,
                senderEmail: $this->senderEmail,
                subject: $this->subject,
                messageBody: $this->message,
            ));

            $contact = Contacts::create([
                'name' => $this->name,
                'email' => $this->senderEmail,
                'subject' => $this->subject,
                'message' => $this->message,
            ]);

            User::owner()?->notify(new NewContactMessage($contact));

            $this->reset(['name', 'senderEmail', 'subject', 'message']);

            session()->flash('contact-success', __('Your message has been sent successfully! Thank you for reaching out.'));
        } catch (\Exception $e) {
            Log::error('Contact form failed', ['error' => $e->getMessage()]);

            session()->flash('contact-error', __('Sorry, there was an error sending your message. Please try again later.'));
        }
    }

    public function render()
    {
        return view('livewire.sections.contact', [
            'email' => $this->email,
            'address' => $this->address,
            'phone' => $this->phone,
        ]);
    }
}
