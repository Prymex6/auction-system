<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class NewsletterSubscribedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subscriberEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->subscriberEmail],
            subject: 'Potwierdzenie zapisu do newslettera - Gołębiowy Lot',
        );
    }

    public function content(): Content
    {
        $url = URL::signedRoute(
            'newsletter.unsubscribe',
            ['email' => $this->subscriberEmail]
        );

        return new Content(
            view: 'emails.newsletter-subscribed',
            with: ['email' => $this->subscriberEmail, 'url' => $url],
        );
    }
}
