<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VendorRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $vendor;
    public $rejectionReason;
    public $correctionUrl;

    public function __construct($vendor, $rejectionReason, $correctionUrl)
    {
        $this->vendor = $vendor;
        $this->rejectionReason = $rejectionReason;
        $this->correctionUrl = $correctionUrl;
    }

    public function build()
    {
        return $this->subject('Action Required: Please Update Your Registration - ' . $this->vendor->vendor_name)
                    ->view('emails.vendor-rejection');
    }
}