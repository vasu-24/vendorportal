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
        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 18px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box-title {
            font-size: 14px;
            color: #047857;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .info-box-text {
            font-size: 13px;
            color: #065f46;
            line-height: 1.6;
        }
        .button-wrapper {
            text-align: center;
            margin: 35px 0;
        }
        .btn {
            display: inline-block;
            padding: 16px 50px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            background: #174081;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(23, 64, 129, 0.3);
            transition: all 0.3s ease;
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
            .btn { display: block; width: 100%; padding: 14px 30px; }
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
            <div class="greeting">Dear {{ $vendorName }},</div>
            
            <div class="message-text">
                Congratulations! Your vendor registration has been approved.<br><br>
                We are pleased to inform you that you have been successfully onboarded as a vendor partner with FIDE. You can now access your vendor portal to manage all your business transactions with us.
            </div>

            <div class="info-box">
                <div class="info-box-title">✓ What's Next?</div>
                <div class="info-box-text">
                    Set your password to access your vendor portal where you can manage invoices, contracts, and documents.
                </div>
            </div>

            <div class="button-wrapper">
                <a href="{{ $setPasswordUrl }}" class="btn" style="color: #ffffff;">Set Password</a>
            </div>

            <div class="signature">
                <p>Thanks & Regards,</p>
                <p class="signature-name">FIDE Team</p>
            </div>
        </div>

        <div class="email-footer">
            <p class="footer-text">This is an automated email. Please do not reply.</p>
            <p class="footer-copyright">© 2026 FIDE. All rights reserved.</p>
        </div>
    </div>
</body>
</html>