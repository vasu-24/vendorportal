<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        /* Header Section */
        .email-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 40px 40px 35px 40px;
            text-align: center;
        }
        .logo-section h1 {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .header-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 15px;
            margin: 8px 0 0 0;
            font-weight: 400;
        }
        
        /* Body Section */
        .email-body {
            padding: 40px 40px 35px 40px;
            background: #ffffff;
        }
        .content-section {
            color: #374151;
            font-size: 15px;
            line-height: 1.7;
            white-space: pre-line;
        }
        
        /* Button Section */
        .button-section {
            margin: 35px 0 30px 0;
            padding: 30px 0;
            text-align: center;
            border-top: 2px solid #f3f4f6;
            border-bottom: 2px solid #f3f4f6;
        }
        .button-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .button-group {
            display: inline-block;
        }
        .btn {
            display: inline-block;
            padding: 15px 45px;
            margin: 0 6px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-accept {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
        }
        .btn-accept:hover {
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
            transform: translateY(-2px);
        }
        .btn-reject {
            background: linear-gradient(135deg, #fb7185 0%, #f43f5e 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(251, 113, 133, 0.35);
        }
        .btn-reject:hover {
            box-shadow: 0 6px 20px rgba(251, 113, 133, 0.45);
            transform: translateY(-2px);
        }
        
        /* Info Box */
        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 16px 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #166534;
            line-height: 1.6;
        }
        
        /* Footer Section */
        .email-footer {
            background: #f9fafb;
            padding: 35px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer-brand {
            font-weight: 600;
            color: #1f2937;
            font-size: 16px;
            margin-bottom: 12px;
        }
        .footer-text {
            color: #6b7280;
            font-size: 13px;
            margin: 8px 0;
            line-height: 1.6;
        }
        .copyright {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 15px;
        }
        
        /* Responsive Design */
        @media only screen and (max-width: 600px) {
            .email-header,
            .email-body,
            .email-footer {
                padding-left: 25px;
                padding-right: 25px;
            }
            .btn {
                display: block;
                margin: 8px 0;
                width: 100%;
            }
            .button-group {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        
        <!-- Header -->
        <div class="email-header">
            <div class="logo-section">
                <h1>🏢 FIDE</h1>
            </div>
            <p class="header-subtitle">Vendor Management System</p>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            
            <!-- Dynamic Content -->
            <div class="content-section">
                {!! nl2br(e($body)) !!}
            </div>
            
            <!-- Info Box -->
            <div class="info-box">
                <p><strong>📋 What happens next?</strong><br>
                Once you accept this invitation, you will receive your login credentials via email within 5 minutes. Please check your inbox and spam folder.</p>
            </div>
            
            <!-- Action Buttons -->
            @if(isset($acceptUrl) && isset($rejectUrl))
            <div class="button-section">
                <p class="button-label">Please respond to this invitation:</p>
                <div class="button-group">
                    <a href="{{ $acceptUrl }}" class="btn btn-accept">✅ Accept Invitation</a>
                    <a href="{{ $rejectUrl }}" class="btn btn-reject">❌ Decline Invitation</a>
                </div>
            </div>
            @endif
            
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p class="footer-brand">FIDE Vendor Management Portal</p>
            <p class="footer-text">
                This is an automated email from our Vendor Management System.<br>
                Please do not reply directly to this email.
            </p>
            <p class="copyright">
                © {{ date('Y') }} FIDE. All rights reserved.
            </p>
        </div>
        
    </div>
</body>
</html>