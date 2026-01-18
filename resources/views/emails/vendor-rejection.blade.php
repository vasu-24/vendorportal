<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f3f4f6;
            padding: 20px 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .email-header {
            background: #174081;
            padding: 25px 30px;
            color: white;
        }
        .header-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .header-subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
        }
        .email-body {
            padding: 30px 30px 25px 30px;
        }
        .greeting {
            font-size: 15px;
            color: #1f2937;
            font-weight: 500;
            margin-bottom: 18px;
        }
        .message-text {
            color: #374151;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 25px;
            white-space: pre-line;
        }
        .info-box {
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 14px 16px;
            margin: 20px 0 25px 0;
            border-radius: 3px;
        }
        .info-box-title {
            font-size: 13px;
            color: #92400e;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .info-box-text {
            font-size: 13px;
            color: #78350f;
            line-height: 1.5;
        }
        .button-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 16px 50px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
            background: #f59e0b;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .signature {
            margin-top: 25px;
            color: #374151;
            font-size: 14px;
        }
        .signature-name {
            font-weight: 600;
            color: #1f2937;
        }
        .email-footer {
            background: #f9fafb;
            padding: 15px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-text {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .footer-copyright {
            color: #9ca3af;
            font-size: 11px;
        }
        @media only screen and (max-width: 600px) {
            .email-header { padding: 20px; }
            .email-body { padding: 25px 20px; }
            .email-footer { padding: 12px 20px; }
            .btn { display: block; width: 100%; padding: 13px 30px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="header-title">FIDE</div>
            <div class="header-subtitle">Vendor Management System</div>
        </div>

        <div class="email-body">
            <div class="greeting">Dear {{ $vendorName ?? 'Vendor' }},</div>
            
            <div class="message-text">{!! nl2br(e($body)) !!}</div>

            <div class="info-box">
                <div class="info-box-title">What's Next?</div>
                <div class="info-box-text">
                    Your information is saved. Click below to update your registration with the required corrections.
                </div>
            </div>

            @if(isset($correctionUrl))
            <div class="button-wrapper">
                <a href="{{ $correctionUrl }}" class="btn">Update Registration</a>
            </div>
            @endif

            <div class="signature">
                <p>Thanks,</p>
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