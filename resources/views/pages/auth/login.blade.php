<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type == 'vendor' ? 'Vendor Login' : 'Login' }} - Vendor Portal</title>
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
                <p class="tagline">Streamline your vendor management process with our powerful platform</p>
                
                <div class="features">
                    @if($type == 'vendor')
                        <div class="feature-item">
                            <i class="bi bi-person-check"></i>
                            <span>Manage Your Profile</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Submit Invoices</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-cloud-upload"></i>
                            <span>Upload Documents</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-graph-up"></i>
                            <span>Track Payments</span>
                        </div>
                    @else
                        <div class="feature-item">
                            <i class="bi bi-shield-check"></i>
                            <span>Secure Authentication</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-people"></i>
                            <span>Vendor Management</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-graph-up-arrow"></i>
                            <span>Real-time Analytics</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-file-earmark-check"></i>
                            <span>Document Processing</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="right-panel">
            <div class="login-form-container">
                
                <!-- Login Header -->
                <div class="login-header">
                    @if($type == 'vendor')
                        <div class="badge-admin">
                            <i class="bi bi-building"></i> Vendor Access
                        </div>
                        <h2>Vendor Login</h2>
                        <p>Sign in to access your vendor portal</p>
                    @else
                        <div class="badge-admin">
                            <i class="bi bi-shield-lock"></i> Internal Team Access
                        </div>
                        <h2>Welcome Back!</h2>
                        <p>Sign in to access the admin dashboard</p>
                    @endif
                </div>

                <!-- Error Alert -->
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Success Alert -->
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ $type == 'vendor' ? route('vendor.login.submit') : route('login.submit') }}">
                    @csrf

                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" 
                                   name="email" 
                                   placeholder="Enter your email"
                                   value="{{ old('email') }}"
                                   required>
                        </div>
                        @error('email')
                            <small style="color: #dc2626; font-size: 12px;">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" 
                                   name="password" 
                                   id="password"
                                   placeholder="Enter your password"
                                   required>
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <small style="color: #dc2626; font-size: 12px;">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-options">
                        <div class="form-check-custom">
                            <input type="checkbox" name="remember" id="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        @if($type == 'vendor')
                            <a href="#" class="forgot-link">Forgot Password?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-login">
                        Sign In <i class="bi bi-arrow-right"></i>
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
    
    <!-- Password Toggle -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>