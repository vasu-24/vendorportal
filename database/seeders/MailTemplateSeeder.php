<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            // 1. Password Reset OTP Email
            [
                'name' => 'Password Reset OTP',
                'subject' => 'Your Password Reset OTP - FIDE Vendor Portal',
                'body' => 'Dear {{vendor_name}},

Your OTP for password reset is: {{otp}}

This OTP is valid for 10 minutes only.

If you did not request this, please ignore this email.

Thanks,
FIDE Team',
                'category' => 'otp',
                'status' => 1,
            ],

            // 2. Vendor Registration Approval Email
            [
                'name' => 'Vendor Approval',
                'subject' => 'Registration Approved - FIDE Vendor Portal',
                'body' => 'Dear {{vendor_name}},

Congratulations! Your vendor registration has been approved.

Company: {{company_name}}

You can now login to the portal and start using our services.

Login here: {{login_url}}

Welcome to FIDE!

Thanks,
FIDE Team',
                'category' => 'approval',
                'status' => 1,
            ],

            // 3. Vendor Registration Rejection Email
            [
                'name' => 'Vendor Rejection',
                'subject' => 'Registration Status - FIDE Vendor Portal',
                'body' => 'Dear {{vendor_name}},

Thank you for your interest in FIDE Vendor Portal.

Unfortunately, we are unable to approve your registration at this time.

Reason: {{rejection_reason}}

If you have any questions, please contact our support team.

Thanks,
FIDE Team',
                'category' => 'rejection',
                'status' => 1,
            ],

            // 4. Invitation Email
            [
                'name' => 'Vendor Invitation',
                'subject' => 'You are invited - FIDE Vendor Portal',
                'body' => 'Dear {{vendor_name}},

You are invited to register as a vendor with FIDE!

Click here to register: {{registration_url}}

Thanks,
FIDE Team',
                'category' => 'invitation',
                'status' => 1,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('mail_templates')->insert([
                'name' => $template['name'],
                'subject' => $template['subject'],
                'body' => $template['body'],
                'category' => $template['category'],
                'status' => $template['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}