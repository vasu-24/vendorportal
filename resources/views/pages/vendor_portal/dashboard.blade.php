@extends('layouts.vendor')

@section('title', 'Dashboard - Vendor Portal')

@section('content')
<div class="container-fluid py-4">
    
    @php
        $vendor = Auth::guard('vendor')->user();
        $companyInfo = $vendor->companyInfo;
        $contact = $vendor->contact;
    @endphp

    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1" style="color: var(--primary-blue);">
                        Welcome back, {{ $vendor->vendor_name }}!
                    </h4>
                    <p class="text-muted mb-0">
                        <i class="bi bi-envelope me-1"></i>{{ $vendor->vendor_email }}
                    </p>
                </div>
                <div>
                    <span class="badge bg-success px-3 py-2">
                        <i class="bi bi-check-circle me-1"></i>Approved
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.1);">
                        <i class="bi bi-receipt fs-4" style="color: var(--accent-blue);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold" style="color: var(--text-dark);">0</h3>
                        <small class="text-muted">Total Invoices</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: rgba(34, 197, 94, 0.1);">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold" style="color: var(--text-dark);">0</h3>
                        <small class="text-muted">Approved</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: rgba(234, 179, 8, 0.1);">
                        <i class="bi bi-clock fs-4 text-warning"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold" style="color: var(--text-dark);">0</h3>
                        <small class="text-muted">Pending</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                         style="width: 50px; height: 50px; background: rgba(23, 64, 129, 0.1);">
                        <i class="bi bi-folder fs-4" style="color: var(--primary-blue);"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold" style="color: var(--text-dark);">0</h3>
                        <small class="text-muted">Documents</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-bold mb-3" style="color: var(--primary-blue);">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                   
                        <a href="{{ route('vendor.documents') }}" class="btn btn-outline-primary">
                            <i class="bi bi-cloud-upload me-1"></i>Upload Document
                        </a>
                        <a href="{{ route('vendor.profile') }}" class="btn btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i>Update Profile
                        </a>
                        <a href="{{ route('vendor.settings') }}" class="btn btn-outline-primary">
                            <i class="bi bi-gear me-1"></i>Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        
        <!-- Company Information -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color: var(--primary-blue);">
                            <i class="bi bi-building me-2"></i>Company Information
                        </h6>
                        <a href="{{ route('vendor.profile') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    
                    @if($companyInfo)
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Legal Entity</td>
                                <td class="fw-medium">{{ $companyInfo->legal_entity_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Business Type</td>
                                <td class="fw-medium">{{ $companyInfo->business_type ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Website</td>
                                <td class="fw-medium">
                                    @if($companyInfo->website)
                                        <a href="{{ $companyInfo->website }}" target="_blank" style="color: var(--accent-blue);">
                                            {{ $companyInfo->website }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Address</td>
                                <td class="fw-medium">{{ $companyInfo->registered_address ?? '-' }}</td>
                            </tr>
                        </table>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-building fs-1 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">No company information available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color: var(--primary-blue);">
                            <i class="bi bi-person-lines-fill me-2"></i>Contact Information
                        </h6>
                        <a href="{{ route('vendor.profile') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    
                    @if($contact)
                        <table class="table table-borderless table-sm mb-0">
                            <tr>
                                <td class="text-muted" style="width: 40%;">Contact Person</td>
                                <td class="fw-medium">{{ $contact->contact_person ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Designation</td>
                                <td class="fw-medium">{{ $contact->designation ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Mobile</td>
                                <td class="fw-medium">{{ $contact->mobile ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td class="fw-medium">
                                    @if($contact->email)
                                        <a href="mailto:{{ $contact->email }}" style="color: var(--accent-blue);">
                                            {{ $contact->email }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-person fs-1 text-muted"></i>
                            <p class="text-muted mt-2 mb-0">No contact information available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Activity & Account Info -->
    <div class="row">
        
        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3" style="color: var(--primary-blue);">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h6>
                    
                    <div class="d-flex flex-column gap-3">
                        @if($vendor->approved_at)
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 36px; height: 36px; background: rgba(34, 197, 94, 0.1); flex-shrink: 0;">
                                <i class="bi bi-check text-success"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-medium">Account Approved</p>
                                <small class="text-muted">{{ $vendor->approved_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($vendor->registration_completed_at)
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 36px; height: 36px; background: rgba(59, 130, 246, 0.1); flex-shrink: 0;">
                                <i class="bi bi-file-earmark-check" style="color: var(--accent-blue);"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-medium">Registration Completed</p>
                                <small class="text-muted">{{ $vendor->registration_completed_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($vendor->responded_at)
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                 style="width: 36px; height: 36px; background: rgba(23, 64, 129, 0.1); flex-shrink: 0;">
                                <i class="bi bi-envelope-check" style="color: var(--primary-blue);"></i>
                            </div>
                            <div>
                                <p class="mb-0 fw-medium">Invitation Accepted</p>
                                <small class="text-muted">{{ $vendor->responded_at->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3" style="color: var(--primary-blue);">
                        <i class="bi bi-shield-check me-2"></i>Account Information
                    </h6>
                    
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width: 40%;">Vendor ID</td>
                            <td class="fw-medium">
                                <code class="bg-light px-2 py-1 rounded">VND-{{ str_pad($vendor->id, 5, '0', STR_PAD_LEFT) }}</code>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account Status</td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Approval Status</td>
                            <td>
                                <span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $vendor->approval_status)) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Member Since</td>
                            <td class="fw-medium">{{ $vendor->created_at->format('d M Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection