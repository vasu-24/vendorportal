@extends('layouts.app')

@section('title', 'Vendor Registration')

@push('head')
    <link href="{{ asset('css/wizard.css') }}" rel="stylesheet">
@endpush

@section('content')

    <!-- 🔥 REJECTION ALERT (Shows if vendor was rejected) -->
    @if($vendor->approval_status === 'rejected' && $vendor->rejection_reason)
    <div class="alert alert-warning border-0 mb-3 shadow-sm" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle text-warning me-3" style="font-size: 1.5rem;"></i>
            <div>
                <h6 class="fw-bold mb-1 text-dark">⚠️ Registration Requires Updates</h6>
                <p class="mb-0 small text-dark">{{ $vendor->rejection_reason }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Welcome Card -->
    <div class="welcome-card mb-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h6 class="mb-1 fw-semibold">Vendor Registration</h6>
                <small class="text-muted">Welcome <strong>{{ $vendor->vendor_name }}</strong>. Complete your registration below.</small>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                @if($vendor->approval_status === 'rejected')
                    <span class="badge bg-warning text-dark border">Correction Required</span>
                @else
                    <span class="badge bg-light text-muted border">Registration Pending</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Wizard Steps -->
    <div class="wizard-steps mb-4">
        <div class="wizard-step active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-label">Company Info</div>
        </div>
        <div class="wizard-step" data-step="2">
            <div class="step-number">2</div>
            <div class="step-label">Banking</div>
        </div>
        <div class="wizard-step" data-step="3">
            <div class="step-number">3</div>
            <div class="step-label">Tax Details</div>
        </div>
        <div class="wizard-step" data-step="4">
            <div class="step-number">4</div>
            <div class="step-label">Documents</div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card border shadow-sm">
        <div class="card-body p-3">
            
            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <form id="vendorRegistrationForm" enctype="multipart/form-data">
                <input type="hidden" name="vendor_token" value="{{ $vendor->token }}">
                
                <!-- Step 1 -->
                <div class="step-content active" data-step="1">
                    @include('pages.vendors.wizard.partials.step1')
                </div>
                
                <!-- Step 2 -->
                <div class="step-content" data-step="2">
                    @include('pages.vendors.wizard.partials.step2')
                </div>
                
                <!-- Step 3 -->
                <div class="step-content" data-step="3">
                    @include('pages.vendors.wizard.partials.step3')
                </div>
                
                <!-- Step 4 -->
                <div class="step-content" data-step="4">
                    @include('pages.vendors.wizard.partials.step4')
                </div>

                <!-- Navigation Buttons -->
                <div class="wizard-nav">
                    <button type="button" class="btn btn-sm btn-secondary" id="prevBtn" style="display: none;">
                        <i class="bi bi-arrow-left"></i> Previous
                    </button>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-dark" id="nextBtn">
                            Next <i class="bi bi-arrow-right"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-dark" id="submitBtn" style="display: none;">
                            <i class="bi bi-check-circle"></i> Submit
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Set token FIRST
        window.vendorToken = '{{ $vendor->token }}';
        
        // 🔥 PRE-FILL DATA IF EXISTS (for rejected vendors)
        window.existingData = {
            companyInfo: @json($vendor->companyInfo ?? null),
            contact: @json($vendor->contact ?? null),
            statutoryInfo: @json($vendor->statutoryInfo ?? null),
            bankDetails: @json($vendor->bankDetails ?? null),
            taxInfo: @json($vendor->taxInfo ?? null),
            businessProfile: @json($vendor->businessProfile ?? null)
        };
        
        console.log('Token:', window.vendorToken);
        console.log('Existing Data:', window.existingData);
    </script>
    
    <script src="{{ asset('js/wizard.js') }}"></script>
@endpush