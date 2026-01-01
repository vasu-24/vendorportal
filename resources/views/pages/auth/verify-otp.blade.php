<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Vendor Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .otp-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 24px 0;
        }
        .otp-inputs input {
            width: 50px;
            height: 55px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .otp-inputs input:focus {
            border-color: #6366f1;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .otp-inputs input.filled {
            border-color: #6366f1;
            background: #fff;
        }
        .otp-inputs input.error {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .email-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f9ff;
            color: #0369a1;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            margin-bottom: 10px;
        }
        .timer {
            text-align: center;
            margin-top: 16px;
            font-size: 14px;
            color: #6b7280;
        }
        .timer span {
            color: #6366f1;
            font-weight: 600;
        }
        .resend-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
        }
        .resend-link:hover {
            text-decoration: underline;
        }
        .resend-link.disabled {
            color: #9ca3af;
            pointer-events: none;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-wrapper">
        
        <!-- Left Panel - Branding with Canvas Animation -->
        <div class="left-panel">
            <canvas id="bgCanvas"></canvas>
            <div class="brand-content">
                <div class="logo-container">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22 viewBox=%220 0 24 24%22 fill=%22white%22><path d=%22M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5%22/></svg>'">
                </div>
                <h1>Vendor Portal</h1>
                <p class="tagline">Enter the OTP sent to your email</p>
                
                <div class="features">
                    <div class="feature-item">
                        <i class="bi bi-1-circle"></i>
                        <span>Enter Email</span>
                    </div>
                    <div class="feature-item" style="opacity: 1;">
                        <i class="bi bi-2-circle-fill"></i>
                        <span>Receive OTP</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-3-circle"></i>
                        <span>Verify OTP</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-4-circle"></i>
                        <span>Set New Password</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - OTP Verification Form -->
        <div class="right-panel">
            <div class="login-form-container">
                
                <!-- Header -->
                <div class="login-header">
                    <div class="badge-admin">
                        <i class="bi bi-shield-lock"></i> Verification
                    </div>
                    <h2>Enter OTP</h2>
                    <p>We've sent a 6-digit OTP to your email address</p>
                    <div class="email-badge">
                        <i class="bi bi-envelope"></i>
                        {{ $email }}
                    </div>
                </div>

                <!-- Error Alert -->
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Success Alert -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- OTP Form -->
                <form method="POST" action="{{ route('vendor.password.verify.otp') }}" id="otpForm">
                    @csrf

                    <div class="otp-inputs">
                        <input type="text" maxlength="1" class="otp-input" data-index="0" autofocus>
                        <input type="text" maxlength="1" class="otp-input" data-index="1">
                        <input type="text" maxlength="1" class="otp-input" data-index="2">
                        <input type="text" maxlength="1" class="otp-input" data-index="3">
                        <input type="text" maxlength="1" class="otp-input" data-index="4">
                        <input type="text" maxlength="1" class="otp-input" data-index="5">
                    </div>

                    <!-- Hidden input to store full OTP -->
                    <input type="hidden" name="otp" id="otpValue">

                    <button type="submit" class="btn-login" id="verifyBtn" disabled>
                        Verify OTP <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <!-- Timer & Resend -->
                <div class="timer">
                    <span id="timerText">OTP expires in <span id="countdown">10:00</span></span>
                </div>

                <div style="text-align: center; margin-top: 16px;">
                    <span style="color: #6b7280; font-size: 14px;">Didn't receive the OTP? </span>
                    <a href="{{ route('vendor.password.resend.otp') }}" class="resend-link" id="resendLink">
                        Resend OTP
                    </a>
                </div>

                <!-- Back to Login -->
                <div style="text-align: center; margin-top: 24px;">
                    <a href="{{ route('vendor.password.request') }}" style="color: #6366f1; text-decoration: none; font-size: 14px;">
                        <i class="bi bi-arrow-left"></i> Change Email
                    </a>
                </div>

                <!-- Footer -->
                <div class="login-footer">
                    <p>&copy; {{ date('Y') }} Vendor Portal — Powered by <a href="#">Kredo</a></p>
                </div>

            </div>
        </div>

    </div>

    <!-- Canvas Animation Script -->
    <script src="{{ asset('js/login-canvas.js') }}"></script>
    
    <!-- OTP Script -->
    <script>
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpValue = document.getElementById('otpValue');
        const verifyBtn = document.getElementById('verifyBtn');
        const otpForm = document.getElementById('otpForm');

        // Handle OTP input
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                
                // Only allow numbers
                if (!/^\d*$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                if (value.length === 1) {
                    input.classList.add('filled');
                    // Move to next input
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                }

                updateOtpValue();
            });

            input.addEventListener('keydown', (e) => {
                // Handle backspace
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                    otpInputs[index - 1].classList.remove('filled');
                }
            });

            // Handle paste
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').slice(0, 6);
                
                if (/^\d+$/.test(pastedData)) {
                    pastedData.split('').forEach((char, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = char;
                            otpInputs[i].classList.add('filled');
                        }
                    });
                    updateOtpValue();
                    if (pastedData.length === 6) {
                        otpInputs[5].focus();
                    }
                }
            });
        });

        function updateOtpValue() {
            let otp = '';
            otpInputs.forEach(input => {
                otp += input.value;
            });
            otpValue.value = otp;
            verifyBtn.disabled = otp.length !== 6;
        }

        // Countdown timer (10 minutes)
        let timeLeft = 600; // 10 minutes in seconds
        const countdownEl = document.getElementById('countdown');
        const timerText = document.getElementById('timerText');

        const timer = setInterval(() => {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 0) {
                clearInterval(timer);
                timerText.innerHTML = '<span style="color: #ef4444;">OTP has expired</span>';
                verifyBtn.disabled = true;
                otpInputs.forEach(input => {
                    input.classList.add('error');
                    input.disabled = true;
                });
            }
        }, 1000);
    </script>
</body>
</html>