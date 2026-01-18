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
            background: #3b82f6;
            padding: 30px;
            text-align: center;
            color: white;
        }
        .header-icon {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .header-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .header-subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
        }
        .email-body {
            padding: 30px;
        }
        .greeting {
            font-size: 15px;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .message-text {
            color: #374151;
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        .details-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 18px;
            margin: 20px 0;
        }
        .details-title {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }
        .detail-row {
            margin: 8px 0;
            font-size: 14px;
            color: #4b5563;
        }
        .detail-label {
            font-weight: 500;
            color: #1f2937;
        }
        .detail-value {
            color: #3b82f6;
        }
        .info-box {
            background: #f0f9ff;
            border-left: 3px solid #3b82f6;
            padding: 14px 16px;
            margin: 20px 0;
            border-radius: 3px;
        }
        .info-box-title {
            font-size: 13px;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .info-box-text {
            font-size: 13px;
            color: #1e3a8a;
            line-height: 1.5;
        }
        .button-section {
            text-align: center;
            margin: 25px 0;
            padding-top: 5px;
        }
        .button-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 16px;
        }
        .btn {
            display: inline-block;
            padding: 13px 40px;
            margin: 0 6px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-accept {
            background: #10b981;
            color: #ffffff;
        }
        .btn-decline {
            background: #ec4899;
            color: #ffffff;
        }
        .signature {
            margin-top: 20px;
            font-size: 14px;
            color: #374151;
        }
        .signature-name {
            font-weight: 600;
            color: #1f2937;
        }
        .email-footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-brand {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 6px;
        }
        .footer-text {
            color: #6b7280;
            font-size: 12px;
        }
        @media only screen and (max-width: 600px) {
            .email-header { padding: 25px 20px; }
            .email-body { padding: 25px 20px; }
            .email-footer { padding: 18px 20px; }
            .btn { 
                display: block; 
                margin: 8px 0; 
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="header-icon">📊</div>
            <div class="header-title">FIDE</div>
            <div class="header-subtitle">Vendor Management System</div>
        </div>

        <div class="email-body">
            <div class="greeting">Dear {{ $vendorName ?? 'Vendor' }},</div>
            
            <div class="message-text">
                We are pleased to invite you to join our Vendor Portal.
            </div>

            <div class="details-box">
                <div class="details-title">Your Details:</div>
                <div class="detail-row">
                    <span class="detail-label">Name:</span> {{ $vendorName ?? 'Vendor' }}
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span> 
                    <span class="detail-value">{{ $vendorEmail ?? 'vendor@example.com' }}</span>
                </div>
                @if(isset($acceptUrl))
                <div class="detail-row">
                    <span class="detail-label">Portal Access:</span> 
                    <span class="detail-value">{{ $acceptUrl }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Date:</span> {{ date('d-M-Y') }}
                </div>
            </div>

            <div class="message-text">
                Please review the attached document and accept or reject the invitation using the buttons in this email.
            </div>

            <div class="message-text">
                We look forward to working with you.
            </div>

            <div class="signature">
                <p>Best Regards,</p>
                <p class="signature-name">Vendor Management Team</p>
            </div>

            <div class="info-box">
                <div class="info-box-title">📋 What happens next?</div>
                <div class="info-box-text">
                    Once you accept the invitation, you will receive your login credentials via email within 5 minutes. Please check your inbox and spam folder.
                </div>
            </div>

            @if(isset($acceptUrl) && isset($rejectUrl))
            <div class="button-section">
                <p class="button-label">Please respond to this invitation:</p>
                <a href="{{ $acceptUrl }}" class="btn btn-accept">✓ Accept Invitation</a>
                <a href="{{ $rejectUrl }}" class="btn btn-decline">✗ Decline Invitation</a>
            </div>
            @endif
        </div>

        <div class="email-footer">
            <p class="footer-brand">Vendor Management Portal</p>
            <p class="footer-text">This is an automated email from our Vendor Management System.<br>Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>