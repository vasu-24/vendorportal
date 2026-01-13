<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $setPasswordUrl;
    public $vendor;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body, $setPasswordUrl, $vendor)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->setPasswordUrl = $setPasswordUrl;
        $this->vendor = $vendor;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.approved')
                    ->with([
                        'body' => $this->body,
                        'setPasswordUrl' => $this->setPasswordUrl,
                        'vendor' => $this->vendor,
                    ]);
    }
}