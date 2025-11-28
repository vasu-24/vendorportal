<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MailTemplate;

class MailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        MailTemplate::create([
            'name' => 'Vendor Invitation',
            'subject' => 'We invite you to our Vendor Portal',
            'body' => 'Dear {vendor_name},

We are pleased to invite you to join our Vendor Portal.

Your Details:
Name: {vendor_name}
Email: {vendor_email}

Portal Access: {portal_url}
Date: {current_date}

Please review the attached document and accept or reject the invitation using the buttons in the email.

We look forward to working with you.

Best Regards,
Vendor Management Team',
            'category' => 'welcome',
            'status' => 'active'
        ]);
    }
}