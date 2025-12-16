<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorSetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vendorName;
    public $vendorEmail;
    public $setPasswordUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($vendorName, $vendorEmail, $setPasswordUrl)
    {
        $this->vendorName = $vendorName;
        $this->vendorEmail = $vendorEmail;
        $this->setPasswordUrl = $setPasswordUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Your Account is Approved - Set Your Password',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-set-password',
            with: [
                'vendorName' => $this->vendorName,
                'vendorEmail' => $this->vendorEmail,
                'setPasswordUrl' => $this->setPasswordUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}