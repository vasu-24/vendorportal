<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 40px 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;">
                                🔐 Password Reset OTP
                            </h1>
                            <p style="margin: 10px 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">
                                Vendor Portal
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 20px; color: #374151; font-size: 16px; line-height: 1.6;">
                                Hello <strong>{{ $vendor->vendor_name ?? 'Vendor' }}</strong>,
                            </p>
                            
                            <p style="margin: 0 0 20px; color: #6b7280; font-size: 15px; line-height: 1.6;">
                                We received a request to reset your password for your Vendor Portal account. Use the OTP code below to verify your identity:
                            </p>

                            <!-- OTP Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <div style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 2px dashed #0ea5e9; border-radius: 12px; padding: 24px 40px; display: inline-block;">
                                            <p style="margin: 0 0 8px; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                                                Your OTP Code
                                            </p>
                                            <p style="margin: 0; font-size: 40px; font-weight: 700; color: #0369a1; letter-spacing: 8px; font-family: 'Courier New', monospace;">
                                                {{ $otp }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiry Notice -->
                            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 0 8px 8px 0; margin: 20px 0;">
                                <p style="margin: 0; color: #92400e; font-size: 13px;">
                                    <strong>⏱️ Important:</strong> This OTP will expire in <strong>10 minutes</strong>. If you didn't request this, please ignore this email.
                                </p>
                            </div>

                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">

                            <!-- Security Tips -->
                            <div style="background: #f8fafc; border-radius: 8px; padding: 16px;">
                                <p style="margin: 0 0 10px; color: #374151; font-size: 13px; font-weight: 600;">
                                    🛡️ Security Tips:
                                </p>
                                <ul style="margin: 0; padding-left: 20px; color: #6b7280; font-size: 12px; line-height: 1.8;">
                                    <li>Never share this OTP with anyone</li>
                                    <li>Our team will never ask for your OTP</li>
                                    <li>This OTP is valid for one-time use only</li>
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 24px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 8px; color: #6b7280; font-size: 13px;">
                                &copy; {{ date('Y') }} Vendor Portal. All rights reserved.
                            </p>
                            <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                This is an automated email. Please do not reply.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>