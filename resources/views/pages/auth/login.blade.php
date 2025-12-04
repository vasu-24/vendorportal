<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Vendor Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Login Page CSS -->
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body class="login-page">
    <div class="login-wrapper">
        
        <!-- Left Panel - Branding -->
        <div class="left-panel">
            <canvas id="bgCanvas"></canvas>
            
            <div class="brand-content">
                <div class="logo-container">
                    <img src="{{ asset('image/logo.png') }}" alt="Logo">
                </div>
                <h1>Vendor Portal</h1>
                <p class="tagline">Streamline your vendor management process with our powerful platform</p>

                <div class="features">
                    <div class="feature-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Secure Authentication</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-people"></i>
                        <span>Vendor Management</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-graph-up"></i>
                        <span>Real-time Analytics</span>
                    </div>
                    <div class="feature-item">
                        <i class="bi bi-file-earmark-check"></i>
                        <span>Document Processing</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="right-panel">
            <div class="login-form-container">
                
                <!-- Header -->
                <div class="login-header">
                    <div class="badge-admin">
                        <i class="bi bi-building"></i>
                        Internal Team Access
                    </div>
                    <h2>Welcome Back!</h2>
                    <p>Sign in to access the admin dashboard</p>
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
                <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                    @csrf

                    <!-- Email -->
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope input-icon"></i>
                            <input 
                                type="email" 
                                name="email" 
                                placeholder="Enter your email"
                                value="{{ old('email') }}"
                                required
                            >
                        </div>
                        @error('email')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock input-icon"></i>
                            <input 
                                type="password" 
                                name="password" 
                                id="password"
                                placeholder="Enter your password"
                                required
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="form-options">
                        <div class="form-check-custom">
                            <input type="checkbox" name="remember" id="remember">
                            <label for="remember">Remember me</label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login" id="loginBtn">
                        <span id="btnText">Sign In</span>
                        <i class="bi bi-arrow-right" id="btnIcon"></i>
                    </button>
                </form>

                <!-- Footer -->
                <div class="login-footer">
                    <p>&copy; {{ date('Y') }} Vendor Portal — Powered by <a href="https://kredo.in" target="_blank">Kredo</a></p>
                </div>

            </div>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Canvas Animation -->
    <script src="{{ asset('js/login.js') }}"></script>

    <!-- Login Functions -->
    <script>
        // Toggle password visibility
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

        // Form submit loading
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const btnText = document.getElementById('btnText');
            const btnIcon = document.getElementById('btnIcon');
            
            btn.disabled = true;
            btnText.textContent = 'Signing in...';
            btnIcon.classList.remove('bi-arrow-right');
            btnIcon.classList.add('spinner-border', 'spinner-border-sm');
        });
    </script>
</body>
</html>