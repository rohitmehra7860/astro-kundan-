<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Queue\ShouldQueue;

class OtpVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly string $userName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Email Verification OTP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-verification',
        );
    }
}
