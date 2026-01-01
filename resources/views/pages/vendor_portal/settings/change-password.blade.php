@extends('layouts.Vendor')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')
<style>
    .settings-card {
        max-width: 600px;
        margin: 0 auto;
    }
    .settings-header {
        text-align: center;
        padding: 30px 20px 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .settings-header .icon-circle {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #174081 0%, #3B82F6 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }
    .settings-header .icon-circle i {
        font-size: 32px;
        color: white;
    }
    .settings-header h4 {
        font-size: 20px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .settings-header p {
        font-size: 14px;
        color: #64748b;
        margin: 0;
    }
    .settings-body {
        padding: 30px;
    }
    .form-label {
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
        font-size: 14px;
    }
    .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .input-group-text {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px 0 0 8px;
        color: #64748b;
    }
    .input-group .form-control {
        border-radius: 0 8px 8px 0;
    }
    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 0;
        z-index: 10;
    }
    .toggle-password:hover {
        color: #3B82F6;
    }
    .password-field {
        position: relative;
    }
    .password-field .form-control {
        padding-right: 40px;
    }
    .password-requirements {
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 8px;
        padding: 14px 16px;
        margin-top: 16px;
        font-size: 13px;
    }
    .password-requirements ul {
        margin: 0;
        padding-left: 20px;
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
    .btn-save {
        background: linear-gradient(135deg, #174081 0%, #3B82F6 100%);
        border: none;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 8px;
        color: white;
        width: 100%;
        transition: all 0.2s;
    }
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        color: white;
    }
    .btn-save:disabled {
        background: #cbd5e1;
        transform: none;
        box-shadow: none;
        cursor: not-allowed;
    }
    .alert {
        border-radius: 8px;
        font-size: 14px;
        padding: 12px 16px;
        margin-bottom: 20px;
    }
    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }
    .last-changed {
        text-align: center;
        padding: 16px;
        background: #EFF6FF;
        border-top: 1px solid #BFDBFE;
        font-size: 12px;
        color: #174081;
    }
    .last-changed i {
        margin-right: 6px;
        color: #3B82F6;
    }
</style>

<div class="settings-card">
    <div class="card shadow-sm">
        
        <!-- Header -->
        <div class="settings-header">
            <div class="icon-circle">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h4>Change Your Password</h4>
            <p>Keep your account secure by updating your password regularly</p>
        </div>

        <!-- Body -->
        <div class="settings-body">
            
            <!-- Success Alert -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Alert -->
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Change Password Form -->
            <form method="POST" action="{{ route('vendor.change-password.update') }}" id="changePasswordForm">
                @csrf

                <!-- Current Password -->
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <div class="password-field">
                        <input type="password" 
                               name="current_password" 
                               id="current_password"
                               class="form-control" 
                               placeholder="Enter your current password"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('current_password', 'icon1')">
                            <i class="bi bi-eye" id="icon1"></i>
                        </button>
                    </div>
                </div>

                <hr class="my-4">

                <!-- New Password -->
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <div class="password-field">
                        <input type="password" 
                               name="password" 
                               id="password"
                               class="form-control" 
                               placeholder="Enter new password"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', 'icon2')">
                            <i class="bi bi-eye" id="icon2"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>

                <!-- Confirm New Password -->
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <div class="password-field">
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation"
                               class="form-control" 
                               placeholder="Confirm new password"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', 'icon3')">
                            <i class="bi bi-eye" id="icon3"></i>
                        </button>
                    </div>
                    <small id="matchMessage" style="display: none; font-size: 12px; margin-top: 6px;"></small>
                </div>

                <!-- Password Requirements -->
                <div class="password-requirements">
                    <strong style="color: #374151; display: block; margin-bottom: 8px;">
                        <i class="bi bi-info-circle me-1"></i> Password Requirements:
                    </strong>
                    <ul>
                        <li id="req-length">At least 8 characters</li>
                        <li id="req-upper">One uppercase letter (A-Z)</li>
                        <li id="req-lower">One lowercase letter (a-z)</li>
                        <li id="req-number">One number (0-9)</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-save mt-4" id="submitBtn" disabled>
                    <i class="bi bi-check-lg me-2"></i>Update Password
                </button>
            </form>
        </div>

        <!-- Last Changed Info -->
        @if(Auth::guard('vendor')->user()->password_changed_at)
            <div class="last-changed">
                <i class="bi bi-clock-history"></i>
                Last changed: {{ Auth::guard('vendor')->user()->password_changed_at->diffForHumans() }}
            </div>
        @endif

    </div>
</div>

@endsection

@push('scripts')
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

    const currentPassword = document.getElementById('current_password');
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

    currentPassword.addEventListener('input', checkFormValidity);
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
        if (value.length === 0) {
            strengthBar.style.width = '0';
        } else if (strength <= 1) {
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

        checkFormValidity();
    }

    function checkFormValidity() {
        const isCurrentFilled = currentPassword.value.length > 0;
        const isPasswordValid = password.value.length >= 8 &&
                               /[A-Z]/.test(password.value) &&
                               /[a-z]/.test(password.value) &&
                               /\d/.test(password.value);
        const passwordsMatch = password.value === confirmPassword.value && confirmPassword.value.length > 0;

        submitBtn.disabled = !(isCurrentFilled && isPasswordValid && passwordsMatch);
    }
</script>
@endpush