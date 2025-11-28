<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0d6efd;
            margin: 0;
        }
        .content {
            white-space: pre-line;
            margin: 30px 0;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Vendor Portal</h1>
        <p>Official Document</p>
    </div>
    
    <div class="content">
        <p><strong>Dear {{ $vendorName }},</strong></p>
        <div>{{ $content }}</div>
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} Vendor Portal. All rights reserved.</p>
        <p>Generated on: {{ date('d-M-Y H:i:s') }}</p>
    </div>
</body>
</html>