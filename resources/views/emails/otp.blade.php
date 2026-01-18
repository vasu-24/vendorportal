<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            padding: 0;
            margin: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
        }
        .email-header {
            background: #174081;
            padding: 35px 30px;
            text-align: center;
        }
        .logo-title {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 1px;
        }
        .header-subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 5px;
        }
        .email-body {
            padding: 40px 35px;
        }
        .greeting {
            font-size: 16px;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .message-text {
            color: #4a5568;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        .otp-section {
            text-align: center;
            margin: 35px 0;
            padding: 30px 20px;
            background: #f8f9fa;
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
        }
        .otp-label {
            font-size: 13px;
            color: #718096;
            margin-bottom: 15px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 700;
            color: #174081;
            letter-spacing: 10px;
            font-family: 'Courier New', monospace;
        }
        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 18px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-text {
            font-size: 13px;
            color: #856404;
            line-height: 1.6;
        }
        .signature {
            margin-top: 30px;
            font-size: 14px;
            color: #4a5568;
        }
        .signature-name {
            font-weight: 600;
            color: #174081;
            margin-top: 4px;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer-text {
            color: #718096;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .footer-copyright {
            color: #a0aec0;
            font-size: 11px;
        }
        @media only screen and (max-width: 600px) {
            .email-body { padding: 30px 25px; }
            .otp-code { font-size: 28px; letter-spacing: 6px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo-title">FIDE</div>
            <div class="header-subtitle">Vendor Management System</div>
        </div>

        <div class="email-body">
            <div class="greeting">Dear {{ $vendorName ?? 'Vendor' }},</div>
            
            <div class="message-text">
                You have requested to reset your password. Please use the verification code below to complete the password reset process.
            </div>

            <div class="otp-section">
                <p class="otp-label">Password Reset Code</p>
                <div class="otp-code">{{ $otp ?? '000000' }}</div>
            </div>

            <div class="info-box">
                <div class="info-text">
                    <strong>⚠ Important:</strong> This code will expire in 10 minutes. If you did not request a password reset, please ignore this email or contact support immediately.
                </div>
            </div>

            <div class="signature">
                <p>Thanks & Regards,</p>
                <p class="signature-name">FIDE Team</p>
            </div>
        </div>

        <div class="email-footer">
            <p class="footer-text">This is an automated email. Please do not reply.</p>
            <p class="footer-copyright">© {{ date('Y') }} FIDE. All rights reserved.</p>
        </div>
    </div>
</body>
</html>