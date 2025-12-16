<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password - Vendor Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <style>
        .password-requirements {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 8px;
            font-size: 12px;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 18px;
            color: #64748b;
        }
        .password-requirements li {
            margin-bottom: 4px;
        }
        .password-requirements li.valid {
            color: #16a34a;
        }
        .password-requirements li.valid::marker {
            content: "✓ ";
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 8px;
            background: #e2e8f0;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #16a34a; width: 100%; }
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
                <p class="tagline">Set up your account password to get started</p>
                
                <div class="features">
                    <div class="feature-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Secure Password</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-lock"></i>
                        <span>Encrypted Storage</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-person-check"></i>
                        <span>Account Verified</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-arrow-right-circle"></i>
                        <span>Ready to Login</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Set Password Form -->
        <div class="right-panel">
            <div class="login-form-container">
                
                <!-- Header -->
                <div class="login-header">
                    <div class="badge-admin">
                        <i class="bi bi-key"></i> Set Password
                    </div>
                    <h2>Create Your Password</h2>
                    <p>Hi <strong>{{ $vendor->vendor_name }}</strong>, please set a secure password for your account.</p>
                </div>

                <!-- Error Alert -->
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Set Password Form -->
                <form method="POST" action="{{ route('vendor.password.set', $token) }}" id="setPasswordForm">
                    @csrf

                    <div class="form-group">
                        <label>New Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   placeholder="Enter new password"
                                   required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password', 'toggleIcon1')">
                                <i class="bi bi-eye" id="toggleIcon1"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="strengthBar"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock-fill input-icon"></i>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation"
                                   placeholder="Confirm your password"
                                   required>
                            <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', 'toggleIcon2')">
                                <i class="bi bi-eye" id="toggleIcon2"></i>
                            </button>
                        </div>
                        <small id="matchMessage" style="display: none; font-size: 12px;"></small>
                    </div>

                    <!-- Password Requirements -->
                    <div class="password-requirements">
                        <strong style="color: #374151;">Password must contain:</strong>
                        <ul>
                            <li id="req-length">At least 8 characters</li>
                            <li id="req-upper">One uppercase letter (A-Z)</li>
                            <li id="req-lower">One lowercase letter (a-z)</li>
                            <li id="req-number">One number (0-9)</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn-login" id="submitBtn" style="margin-top: 24px;" disabled>
                        Set Password <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <!-- Footer -->
                <div class="login-footer">
                    <p>&copy; {{ date('Y') }} Vendor Portal — Powered by <a href="#">Kredo</a></p>
                </div>

            </div>
        </div>

    </div>

    <!-- Canvas Animation Script -->
    <script src="{{ asset('js/login-canvas.js') }}"></script>
    
    <!-- Password Validation Script -->
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const submitBtn = document.getElementById('submitBtn');
        const strengthBar = document.getElementById('strengthBar');
        const matchMessage = document.getElementById('matchMessage');

        const requirements = {
            length: document.getElementById('req-length'),
            upper: document.getElementById('req-upper'),
            lower: document.getElementById('req-lower'),
            number: document.getElementById('req-number')
        };

        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', checkMatch);

        function validatePassword() {
            const value = password.value;
            
            // Check requirements
            const hasLength = value.length >= 8;
            const hasUpper = /[A-Z]/.test(value);
            const hasLower = /[a-z]/.test(value);
            const hasNumber = /\d/.test(value);

            // Update requirement indicators
            updateRequirement(requirements.length, hasLength);
            updateRequirement(requirements.upper, hasUpper);
            updateRequirement(requirements.lower, hasLower);
            updateRequirement(requirements.number, hasNumber);

            // Calculate strength
            let strength = 0;
            if (hasLength) strength++;
            if (hasUpper) strength++;
            if (hasLower) strength++;
            if (hasNumber) strength++;

            // Update strength bar
            strengthBar.className = 'password-strength-bar';
            if (strength <= 1) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 3) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }

            checkMatch();
        }

        function updateRequirement(element, isValid) {
            if (isValid) {
                element.classList.add('valid');
            } else {
                element.classList.remove('valid');
            }
        }

        function checkMatch() {
            const isValid = password.value.length >= 8 &&
                           /[A-Z]/.test(password.value) &&
                           /[a-z]/.test(password.value) &&
                           /\d/.test(password.value);

            const passwordsMatch = password.value === confirmPassword.value && confirmPassword.value.length > 0;

            // Show match message
            if (confirmPassword.value.length > 0) {
                matchMessage.style.display = 'block';
                if (passwordsMatch) {
                    matchMessage.style.color = '#16a34a';
                    matchMessage.innerHTML = '<i class="bi bi-check-circle"></i> Passwords match';
                } else {
                    matchMessage.style.color = '#dc2626';
                    matchMessage.innerHTML = '<i class="bi bi-x-circle"></i> Passwords do not match';
                }
            } else {
                matchMessage.style.display = 'none';
            }

            // Enable/disable submit button
            submitBtn.disabled = !(isValid && passwordsMatch);
        }
    </script>
</body>
</html>