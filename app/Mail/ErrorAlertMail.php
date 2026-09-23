<?php

namespace App\Mail;

use App\Models\ErrorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ErrorAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $adminEmail,
        public ErrorLog $errorLog,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->adminEmail],
            subject: '[Gołębiowy Lot] Nowy błąd: '.class_basename($this->errorLog->exception_class),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.error-alert',
            with: ['errorLog' => $this->errorLog],
        );
    }
}
