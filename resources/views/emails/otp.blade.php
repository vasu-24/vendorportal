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
        
        /* OTP Box */
        .otp-container {
            text-align: center;
            margin: 35px 0;
            padding: 30px 0;
            border-top: 2px solid #f3f4f6;
            border-bottom: 2px solid #f3f4f6;
        }
        .otp-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        .otp-box {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px dashed #3b82f6;
            border-radius: 12px;
            padding: 30px 40px;
            display: inline-block;
            margin: 10px 0;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 700;
            color: #1e40af;
            letter-spacing: 10px;
            font-family: 'Courier New', monospace;
        }
        
        /* Warning Box */
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .warning-box p {
            margin: 0;
            font-size: 13px;
            color: #92400e;
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
            .otp-code {
                font-size: 36px;
                letter-spacing: 6px;
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
            
            <!-- OTP Display -->
            <div class="otp-container">
                <p class="otp-label">Your OTP Code</p>
                <div class="otp-box">
                    <div class="otp-code">{{ $otp }}</div>
                </div>
            </div>
            
            <!-- Warning Box -->
            <div class="warning-box">
                <p><strong>⏱️ Important:</strong> This OTP will expire in <strong>10 minutes</strong>. Do not share this code with anyone.</p>
            </div>
            
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