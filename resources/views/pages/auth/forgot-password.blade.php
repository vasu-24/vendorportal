<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Vendor Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
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
                <p class="tagline">Reset your password to regain access to your account</p>
                
                <div class="features">
                    <div class="feature-item">
                        <i class="bi bi-1-circle-fill"></i>
                        <span>Enter Email</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-2-circle"></i>
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

        <!-- Right Panel - Forgot Password Form -->
        <div class="right-panel">
            <div class="login-form-container">
                
                <!-- Header -->
                <div class="login-header">
                    <div class="badge-admin">
                        <i class="bi bi-key"></i> Password Recovery
                    </div>
                    <h2>Forgot Password?</h2>
                    <p>No worries! Enter your email address and we'll send you an OTP to reset your password.</p>
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

                <!-- Forgot Password Form -->
                <form method="POST" action="{{ route('vendor.password.send.otp') }}">
                    @csrf

                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" 
                                   name="email" 
                                   placeholder="Enter your registered email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        Send OTP <i class="bi bi-send"></i>
                    </button>
                </form>

                <!-- Back to Login -->
                <div style="text-align: center; margin-top: 24px;">
                    <a href="{{ route('vendor.login') }}" style="color: #6366f1; text-decoration: none; font-size: 14px;">
                        <i class="bi bi-arrow-left"></i> Back to Login
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
</body>
</html>