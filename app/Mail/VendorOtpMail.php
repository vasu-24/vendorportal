<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $otp;
    public $vendor;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $body, $otp, $vendor)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->otp = $otp;
        $this->vendor = $vendor;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.otp')
                    ->with([
                        'body' => $this->body,
                        'otp' => $this->otp,
                        'vendor' => $this->vendor,
                    ]);
    }
}