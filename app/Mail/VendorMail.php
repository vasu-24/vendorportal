<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $acceptUrl;
    public $rejectUrl;
    public $vendorName;
    public $templateContent;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body, $acceptUrl, $rejectUrl, $vendorName, $templateContent)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->acceptUrl = $acceptUrl;
        $this->rejectUrl = $rejectUrl;
        $this->vendorName = $vendorName;
        $this->templateContent = $templateContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-mail',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return []; // NO PDF ATTACHMENT!
    }
}