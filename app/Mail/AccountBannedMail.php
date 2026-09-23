<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountBannedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $reason,
        public ?string $duration = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->user->email],
            subject: 'Twoje konto zostało zablokowane',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-banned',
            with: [
                'user' => $this->user,
                'reason' => $this->reason,
                'duration' => $this->duration,
            ],
        );
    }
}
