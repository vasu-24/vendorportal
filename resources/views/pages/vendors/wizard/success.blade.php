<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Complete - Vendor Portal</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    
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
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <!-- Success Card -->
                <div class="card shadow text-center">
                    <div class="card-body p-5">
                        
                        <!-- Success Icon -->
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        
                        <!-- Success Message -->
                        <h2 class="fw-bold mb-3" style="color: var(--primary-blue);">
                            Registration Complete!
                        </h2>
                        
                        <p class="text-muted fs-5 mb-4">
                            Thank you <strong>{{ $vendor->vendor_name }}</strong>! Your vendor registration has been submitted successfully.
                        </p>
                        
                        <!-- Registration Details -->
                        <div class="p-3 rounded mb-4" style="background: var(--bg-light);">
                            <div class="row text-start">
                                <div class="col-6 mb-2">
                                    <small class="text-muted">Vendor Name</small>
                                    <p class="mb-0 fw-medium">{{ $vendor->vendor_name }}</p>
                                </div>
                                <div class="col-6 mb-2">
                                    <small class="text-muted">Email</small>
                                    <p class="mb-0 fw-medium">{{ $vendor->vendor_email }}</p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Registration Date</small>
                                    <p class="mb-0 fw-medium">{{ $vendor->registration_completed_at ? $vendor->registration_completed_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Status</small>
                                    <p class="mb-0">
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Submitted
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- What's Next -->
                        <div class="alert alert-info text-start mb-4">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-info-circle me-1"></i>What's Next?
                            </h6>
                            <ul class="mb-0 ps-3">
                                <li>Our team will review your registration details</li>
                                <li>You will receive an email once your registration is approved</li>
                                <li>For any queries, contact our support team</li>
                            </ul>
                        </div>
                        
                        <!-- Reference Number -->
                        <div class="p-3 rounded mb-4" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                            <small class="text-muted d-block mb-1">Reference Number</small>
                            <span class="fw-bold fs-5" style="color: var(--primary-blue); letter-spacing: 1px;">
                                VND-{{ strtoupper(substr($vendor->token, 0, 8)) }}
                            </span>
                            <br>
                            <small class="text-muted">Please save this for future reference</small>
                        </div>
                        
                        <!-- Contact Support -->
                        <div class="border-top pt-4">
                            <p class="text-muted mb-2">Need help?</p>
                            <a href="mailto:support@vendorportal.com" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-envelope me-1"></i>Contact Support
                            </a>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Footer Note -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="bi bi-shield-check me-1"></i>Your data is secure and encrypted
                    </small>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-3 mt-4" style="background: #f1f5fa; border-top: 1.5px solid var(--border-grey);">
        <a href="https://kredo.in" target="_blank" style="color: var(--primary-blue); font-weight: 600; text-decoration: none;">
            &copy; {{ date('Y') }} Vendor Portal — Powered by Kredo
        </a>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>