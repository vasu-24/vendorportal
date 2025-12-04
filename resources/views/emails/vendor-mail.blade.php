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
        }
        /* Header Section */
        .email-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 40px 40px 35px 40px;
            text-align: center;
            position: relative;
        }
        .logo-section {
            margin-bottom: 15px;
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
        .greeting {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .content-section {
            color: #374151;
            font-size: 15px;
            line-height: 1.7;
        }
        .content-section p {
            margin: 0 0 15px 0;
        }
        .content-section p:last-child {
            margin-bottom: 0;
        }
        .content-section ul {
            margin: 15px 0;
            padding-left: 0;
            list-style: none;
        }
        .content-section ul li {
            padding-left: 25px;
            margin: 10px 0;
            position: relative;
        }
        .content-section ul li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
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
        /* 🔥 UPDATED - LIGHTER RED COLOR */
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
            background: #f9fafb;
            border-left: 4px solid #3b82f6;
            padding: 16px 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #4b5563;
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
        .footer-links {
            margin: 20px 0 15px 0;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer-links a {
            color: #3b82f6;
            text-decoration: none;
            margin: 0 12px;
            font-size: 13px;
            font-weight: 500;
        }
        .footer-links a:hover {
            text-decoration: underline;
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
                <h1>🏢 Vendor Portal</h1>
            </div>
            <p class="header-subtitle">Vendor Management System</p>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            
            <!-- Content -->
            <div class="content-section">
                {!! nl2br(e($body)) !!}
            </div>
            
            <!-- Info Box (Optional - shows after acceptance) -->
            <div class="info-box">
                <p><strong>📋 What happens next?</strong><br>
                Once you accept this invitation, you will receive your login credentials via email within 5 minutes. Please check your inbox and spam folder.</p>
            </div>
            
            <!-- Action Buttons -->
            @if($acceptUrl && $rejectUrl)
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
            <p class="footer-brand">Vendor Management Portal</p>
            <p class="footer-text">
                This is an automated email from our Vendor Management System.<br>
                Please do not reply directly to this email.
            </p>
            
            <div class="footer-links">
                <a href="#">Help Center</a>
                <a href="#">Contact Support</a>
                <a href="#">Terms of Service</a>
            </div>
            
            <p class="copyright">
                © {{ date('Y') }} Vendor Portal. All rights reserved.
            </p>
        </div>
        
    </div>
</body>
</html>