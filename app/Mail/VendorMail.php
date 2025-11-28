<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class VendorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $acceptUrl;
    public $rejectUrl;
    public $vendorName;
    public $templateContent;
    public $templateName;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body, $acceptUrl, $rejectUrl, $vendorName, $templateContent, $templateName)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->acceptUrl = $acceptUrl;
        $this->rejectUrl = $rejectUrl;
        $this->vendorName = $vendorName;
        $this->templateContent = $templateContent;
        $this->templateName = $templateName;
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
        // Generate PDF from template
        $pdf = Pdf::loadView('emails.template-pdf', [
            'content' => $this->templateContent,
            'vendorName' => $this->vendorName
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), $this->templateName . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}