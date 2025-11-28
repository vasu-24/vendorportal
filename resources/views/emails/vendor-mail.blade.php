<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .email-header {
            background: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .email-body {
            padding: 30px;
            color: #333;
            line-height: 1.6;
        }
        .email-body p {
            margin: 10px 0;
        }
        .message-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #0d6efd;
            margin: 20px 0;
            white-space: pre-line;
        }
        .attachment-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }
        .btn-accept {
            background: #28a745;
            color: white;
        }
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2>📧 Vendor Portal</h2>
        </div>
        
        <div class="email-body">
            <!-- Custom Email Message -->
            <div class="message-content">
                {{ $body }}
            </div>
            
            <!-- Attachment Notice -->
            <div class="attachment-notice">
                <strong>📎 Attachment Included:</strong><br>
                A document has been attached to this email. Please review it carefully.
            </div>
            
            <!-- Action Buttons -->
            <p style="text-align: center; font-weight: bold; margin-top: 30px;">
                Please respond using the buttons below:
            </p>
            
            <div class="button-container">
                <a href="{{ $acceptUrl }}" class="btn btn-accept">✓ Accept</a>
                <a href="{{ $rejectUrl }}" class="btn btn-reject">✗ Reject</a>
            </div>
            
            <p style="font-size: 12px; color: #999; margin-top: 30px; text-align: center;">
                If the buttons don't work, copy and paste these links:<br>
                <strong>Accept:</strong> {{ $acceptUrl }}<br>
                <strong>Reject:</strong> {{ $rejectUrl }}
            </p>
        </div>
        
        <div class="email-footer">
            &copy; {{ date('Y') }} Vendor Portal. All rights reserved.
        </div>
    </div>
</body>
</html>