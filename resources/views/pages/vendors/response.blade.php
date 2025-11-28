<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response - Vendor Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        
                        @if($type === 'success')
                            <i class="bi bi-check-circle text-success" style="font-size: 5rem;"></i>
                            <h2 class="mt-3 fw-bold" style="color: var(--primary-blue);">Welcome Aboard!</h2>
                            <p class="text-muted mt-3 fs-5">{{ $message }}</p>
                            
                            @if(isset($vendor))
                            <div class="mt-4 p-3 bg-light rounded">
                                <p class="mb-1"><strong>Vendor:</strong> {{ $vendor->vendor_name }}</p>
                                <p class="mb-0"><strong>Email:</strong> {{ $vendor->vendor_email }}</p>
                            </div>
                            
                            <!-- Continue to Registration Button -->
                            <div class="mt-4">
                                <p class="text-muted mb-3">Please complete your registration to proceed</p>
                                <a href="{{ route('vendor.registration', $vendor->token) }}" class="btn btn-primary btn-lg">
                                    <i class="bi bi-arrow-right-circle me-2"></i>Continue to Registration
                                </a>
                            </div>
                            @endif

                        @elseif($type === 'warning')
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
                            <h2 class="mt-3 text-warning">Already Responded</h2>
                            <p class="text-muted mt-3 fs-5">{{ $message }}</p>
                            
                        @else
                            <i class="bi bi-info-circle text-info" style="font-size: 5rem;"></i>
                            <h2 class="mt-3 text-info">Response Recorded</h2>
                            <p class="text-muted mt-3 fs-5">{{ $message }}</p>
                            
                            @if(isset($vendor))
                            <div class="mt-4 p-3 bg-light rounded">
                                <p class="mb-1"><strong>Vendor:</strong> {{ $vendor->vendor_name }}</p>
                                <p class="mb-0"><strong>Status:</strong> 
                                    <span class="badge bg-danger">{{ ucfirst($vendor->status) }}</span>
                                </p>
                            </div>
                            @endif
                        @endif
                        
                        <div class="mt-4 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>Secured by Vendor Portal
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>