<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;

    public string $senderEmail;

    public string $messageBody;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $senderName,
        string $senderEmail,
        string $subject,
        string $messageBody,
    ) {
        $this->senderName = $senderName;
        $this->senderEmail = $senderEmail;
        $this->subject = $subject;
        $this->messageBody = $messageBody;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
            replyTo: [$this->senderEmail],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
            with: [
                'subject' => $this->subject,
            ],
        );
    }
}
