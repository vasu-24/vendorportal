<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .email-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .email-header p {
            margin: 8px 0 0 0;
            opacity: 0.95;
            font-size: 14px;
        }
        .email-body {
            padding: 40px 35px;
        }
        .greeting {
            font-size: 18px;
            color: #1e3a5f;
            margin-bottom: 20px;
        }
        .content-section {
            line-height: 1.8;
            color: #374151;
            margin-bottom: 30px;
        }
        .highlight-box {
            background: #f0f9ff;
            border-left: 4px solid #1e3a5f;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        .btn {
            display: inline-block;
            padding: 16px 50px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8a 100%);
            color: white !important;
            box-shadow: 0 4px 15px rgba(30, 58, 95, 0.3);
        }
        .btn:hover {
            background: linear-gradient(135deg, #2d5a8a 0%, #1e3a5f 100%);
        }
        .note {
            background: #fefce8;
            border: 1px solid #fef08a;
            padding: 15px;
            border-radius: 8px;
            font-size: 13px;
            color: #854d0e;
            margin-top: 25px;
        }
        .email-footer {
            background: #f9fafb;
            text-align: center;
            padding: 30px;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        
        <!-- Header -->
        <div class="email-header">
            <h1>🎉 Account Approved!</h1>
            <p>Your vendor registration has been approved</p>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            
            <p class="greeting">Dear <strong>{{ $vendorName }}</strong>,</p>
            
            <div class="content-section">
                <p>Great news! Your vendor registration has been <strong>approved</strong>. You're just one step away from accessing your Vendor Portal account.</p>
                
                <p>Please click the button below to set up your password and complete your account setup.</p>
            </div>

            <div class="highlight-box">
                <strong>📧 Your Login Email:</strong><br>
                {{ $vendorEmail }}
            </div>
            
            <!-- Action Button -->
            <div class="button-container">
                <a href="{{ $setPasswordUrl }}" class="btn">Set Your Password</a>
            </div>

            <div class="note">
                <strong>⚠️ Security Note:</strong><br>
                • This link is valid for <strong>48 hours</strong><br>
                • Do not share this link with anyone<br>
                • If you did not request this, please contact support
            </div>
            
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Vendor Portal</strong></p>
            <p>This is an automated email. Please do not reply.</p>
            <p>&copy; {{ date('Y') }} Vendor Portal. All rights reserved.</p>
        </div>
        
    </div>
</body>
</html>