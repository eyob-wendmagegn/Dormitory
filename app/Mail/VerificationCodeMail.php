<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $code,
        public string $type = 'activation', // 'activation' or 'reset'
        public ?string $name = null
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->type === 'reset'
            ? "Your Woldia University Password Reset Code: {$this->code}"
            : "Your Woldia University Verification Code: {$this->code}";

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subject,
        );
    }

    /**
     * High priority headers to ensure instant delivery and prevent greylisting delays.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Priority' => '1',
                'Priority' => 'Urgent',
                'Importance' => 'high',
                'Auto-Submitted' => 'auto-generated',
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-code',
            text: 'emails.verification-code-text',
        );
    }
}
