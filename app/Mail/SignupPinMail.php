<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SignupPinMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $pin)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Код подтверждения регистрации',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.signup-pin',
        );
    }
}
