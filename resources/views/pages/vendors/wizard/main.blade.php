<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vendor Registration - Vendor Portal</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <style>
        /* Wizard Step Indicator */
        .wizard-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 15%;
            right: 15%;
            height: 3px;
            background: var(--border-grey);
            z-index: 0;
        }
        
        .wizard-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
            max-width: 180px;
        }
        
        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--white);
            border: 3px solid var(--border-grey);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-grey);
            transition: all 0.3s ease;
        }
        
        .wizard-step.active .step-circle {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(23, 64, 129, 0.3);
        }
        
        .wizard-step.completed .step-circle {
            background: #10b981;
            border-color: #10b981;
            color: var(--white);
        }
        
        .step-label {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-grey);
            text-align: center;
        }
        
        .wizard-step.active .step-label {
            color: var(--primary-blue);
            font-weight: 600;
        }
        
        .wizard-step.completed .step-label {
            color: #10b981;
        }
        
        /* Step Content */
        .step-content {
            display: none;
        }
        
        .step-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Form Sections */
        .form-section {
            background: var(--white);
            border: 1.5px solid var(--border-grey);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--bg-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* File Upload */
        .file-upload-box {
            border: 2px dashed var(--border-grey);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: var(--bg-light);
        }
        
        .file-upload-box:hover {
            border-color: var(--accent-blue);
            background: rgba(59, 130, 246, 0.05);
        }
        
        .file-upload-box.has-file {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }
        
        /* Navigation Buttons */
        .wizard-nav {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1.5px solid var(--border-grey);
        }
        
        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        
        .loading-overlay.show {
            display: flex;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .wizard-steps::before {
                left: 5%;
                right: 5%;
            }
            
            .step-circle {
                width: 40px;
                height: 40px;
                font-size: 0.95rem;
            }
            
            .step-label {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body class="bg-light">
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted fw-medium">Saving your data...</p>
    </div>

    <!-- Header -->
    <div class="py-3" style="background: var(--white); border-bottom: 2px solid var(--primary-blue);">
        <div class="container">
            <div class="d-flex align-items-center">
                <img src="{{ asset('image/logo.png') }}" alt="Logo" style="height: 32px;" class="me-2">
                <span class="fw-bold" style="color: var(--primary-blue); font-size: 1.15rem;">Vendor Portal</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-4">
        
        <!-- Welcome Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bold mb-1" style="color: var(--primary-blue);">
                            <i class="bi bi-building me-2"></i>Vendor Registration
                        </h4>
                        <p class="text-muted mb-0">
                            Welcome <strong>{{ $vendor->vendor_name }}</strong>! Please complete your registration by filling the details below.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="bi bi-clock me-1"></i>Registration Pending
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wizard Steps Indicator -->
        <div class="wizard-steps">
            <div class="wizard-step active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Company & Contact</div>
            </div>
            <div class="wizard-step" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">Statutory & Banking</div>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">Tax & Business</div>
            </div>
            <div class="wizard-step" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label">Documents & Review</div>
            </div>
        </div>

        <!-- Wizard Form -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <form id="vendorRegistrationForm" enctype="multipart/form-data">
                    <input type="hidden" name="vendor_token" value="{{ $vendor->token }}">
                    
                    <!-- Step 1: Company Info + Contact -->
                    <div class="step-content active" data-step="1">
                        @include('pages.vendors.wizard.partials.step1')
                    </div>
                    
                    <!-- Step 2: Statutory + Banking -->
                    <div class="step-content" data-step="2">
                        @include('pages.vendors.wizard.partials.step2')
                    </div>
                    
                    <!-- Step 3: Tax + Business Profile -->
                    <div class="step-content" data-step="3">
                        @include('pages.vendors.wizard.partials.step3')
                    </div>
                    
                    <!-- Step 4: Documents + Review -->
                    <div class="step-content" data-step="4">
                        @include('pages.vendors.wizard.partials.step4')
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="wizard-nav">
                        <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                            <i class="bi bi-arrow-left me-1"></i>Previous
                        </button>
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="nextBtn">
                                Next<i class="bi bi-arrow-right ms-1"></i>
                            </button>
                            <button type="button" class="btn btn-success" id="submitBtn" style="display: none;">
                                <i class="bi bi-check-circle me-1"></i>Submit Registration
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="text-center py-3 mt-4" style="background: #f1f5fa; border-top: 1.5px solid var(--border-grey);">
        <small class="text-muted">
            <i class="bi bi-shield-check me-1"></i>Your data is secure and encrypted
        </small>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        // CSRF Token Setup
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Wizard Variables
        let currentStep = 1;
        const totalSteps = 4;
        const vendorToken = '{{ $vendor->token }}';
        
        // API Endpoints
        const apiEndpoints = {
            1: `/api/vendor/registration/step1/${vendorToken}`,
            2: `/api/vendor/registration/step2/${vendorToken}`,
            3: `/api/vendor/registration/step3/${vendorToken}`,
            4: `/api/vendor/registration/step4/${vendorToken}`
        };

        // DOM Elements
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const alertContainer = document.getElementById('alertContainer');

        // Show/Hide Loading
        function showLoading() {
            loadingOverlay.classList.add('show');
        }

        function hideLoading() {
            loadingOverlay.classList.remove('show');
        }

        // Show Alert
        function showAlert(message, type = 'danger') {
            alertContainer.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alert = alertContainer.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }

        // Update Step Indicator
        function updateStepIndicator() {
            document.querySelectorAll('.wizard-step').forEach((step, index) => {
                const stepNum = index + 1;
                step.classList.remove('active', 'completed');
                
                if (stepNum < currentStep) {
                    step.classList.add('completed');
                } else if (stepNum === currentStep) {
                    step.classList.add('active');
                }
            });
        }

        // Show Step Content
        function showStep(step) {
            document.querySelectorAll('.step-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelector(`.step-content[data-step="${step}"]`).classList.add('active');
            
            // Update buttons
            prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
            nextBtn.style.display = step === totalSteps ? 'none' : 'inline-flex';
            submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';
            
            updateStepIndicator();
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Get Step Form Data
        function getStepFormData(step) {
            const formData = new FormData();
            const stepContent = document.querySelector(`.step-content[data-step="${step}"]`);
            
            // Get all inputs in current step
            stepContent.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.type === 'file') {
                    if (input.files.length > 0) {
                        formData.append(input.name, input.files[0]);
                    }
                } else if (input.type === 'checkbox') {
                    formData.append(input.name, input.checked ? '1' : '0');
                } else if (input.type === 'radio') {
                    if (input.checked) {
                        formData.append(input.name, input.value);
                    }
                } else {
                    formData.append(input.name, input.value);
                }
            });
            
            return formData;
        }

        // Validate Step
        function validateStep(step) {
            const stepContent = document.querySelector(`.step-content[data-step="${step}"]`);
            const requiredFields = stepContent.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                field.classList.remove('is-invalid');
                
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            if (!isValid) {
                showAlert('Please fill all required fields.');
            }
            
            return isValid;
        }

        // Save Step Data via API
        async function saveStepData(step) {
            if (!validateStep(step)) {
                return false;
            }
            
            showLoading();
            
            try {
                const formData = getStepFormData(step);
                
                const response = await axios.post(apiEndpoints[step], formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                
                hideLoading();
                
                if (response.data.success) {
                    return true;
                } else {
                    showAlert(response.data.message || 'Something went wrong.');
                    return false;
                }
                
            } catch (error) {
                hideLoading();
                
                if (error.response && error.response.data) {
                    // Validation errors
                    if (error.response.data.errors) {
                        const errors = Object.values(error.response.data.errors).flat();
                        showAlert(errors.join('<br>'));
                    } else {
                        showAlert(error.response.data.message || 'Something went wrong.');
                    }
                } else {
                    showAlert('Network error. Please try again.');
                }
                
                return false;
            }
        }

        // Next Button Click
        nextBtn.addEventListener('click', async function() {
            const saved = await saveStepData(currentStep);
            
            if (saved) {
                currentStep++;
                showStep(currentStep);
                showAlert('Step saved successfully!', 'success');
            }
        });

        // Previous Button Click
        prevBtn.addEventListener('click', function() {
            currentStep--;
            showStep(currentStep);
        });

        // Submit Button Click
        submitBtn.addEventListener('click', async function() {
            const saved = await saveStepData(currentStep);
            
            if (saved) {
                // Redirect to success page
                window.location.href = `/vendor/registration/success/${vendorToken}`;
            }
        });

        // File Upload Preview
        document.querySelectorAll('.file-upload-input').forEach(input => {
            input.addEventListener('change', function() {
                const box = this.closest('.file-upload-box');
                const label = box.querySelector('.file-upload-label');
                
                if (this.files.length > 0) {
                    box.classList.add('has-file');
                    label.innerHTML = `<i class="bi bi-check-circle text-success me-1"></i>${this.files[0].name}`;
                } else {
                    box.classList.remove('has-file');
                    label.innerHTML = '<i class="bi bi-cloud-upload me-1"></i>Click to upload or drag & drop';
                }
            });
        });

        // Remove invalid class on input
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });

        // Initialize
        showStep(1);
    </script>

</body>
</html>