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
        }
        .header-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 15px;
            margin: 8px 0 0 0;
        }
        .email-body {
            padding: 40px;
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
        
        /* Rejection Reason Box */
        .rejection-box {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 20px;
            margin: 25px 0;
            border-radius: 6px;
        }
        .rejection-box-title {
            font-weight: 600;
            color: #dc2626;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .rejection-box-content {
            color: #7f1d1d;
            font-size: 14px;
            line-height: 1.6;
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
        .btn {
            display: inline-block;
            padding: 15px 45px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
        }
        
        /* Footer */
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
        }
        .copyright {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 15px;
        }
        
        @media only screen and (max-width: 600px) {
            .email-header, .email-body, .email-footer {
                padding-left: 25px;
                padding-right: 25px;
            }
            .btn {
                display: block;
                width: 100%;
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
            
            <p class="greeting">Dear {{ $vendor->vendor_name }},</p>
            
            <div class="content-section">
                <p>Thank you for submitting your vendor registration.</p>
                <p>After reviewing your submission, our team has identified some issues that need to be corrected before we can proceed with the approval.</p>
            </div>
            
            <!-- Rejection Reason Box -->
            <div class="rejection-box">
                <p class="rejection-box-title">📋 Reason for Rejection:</p>
                <p class="rejection-box-content">{{ $rejectionReason }}</p>
            </div>
            
            <!-- Info Box -->
            <div class="info-box">
                <p><strong>✅ Good News!</strong><br>
                Your previously submitted information has been saved. Simply click the button below to update your registration with the required corrections.</p>
            </div>
            
            <!-- Action Button -->
            <div class="button-section">
                <p class="button-label">Please update your registration:</p>
                <a href="{{ $correctionUrl }}" class="btn btn-primary">🔄 Update My Registration</a>
            </div>
            
            <div class="content-section">
                <p style="font-size: 13px; color: #6b7280;">This link will expire in 7 days. If you have any questions, please contact our support team.</p>
            </div>
            
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p class="footer-brand">Vendor Management Portal</p>
            <p class="footer-text">
                This is an automated email from our Vendor Management System.<br>
                Please do not reply directly to this email.
            </p>
            <p class="copyright">
                © {{ date('Y') }} Vendor Portal. All rights reserved.
            </p>
        </div>
        
    </div>
</body>
</html>