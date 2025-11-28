@extends('layouts.app')
@section('title', 'Vendor Approval Queue')

@section('content')
<div class="container-fluid">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold" style="color: var(--primary-blue);">
                <i class="bi bi-check-circle me-2"></i>Vendor Approval Queue
            </h2>
            <p class="text-muted mb-0">Review and approve vendor registrations</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary btn-sm" id="refreshBtn">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Statistics - Small & Compact -->
    <div class="d-flex flex-wrap gap-3 mb-4">
        <div class="d-flex align-items-center px-3 py-2 bg-white border rounded shadow-sm">
            <i class="bi bi-clock text-muted me-2"></i>
            <span class="text-muted small me-2">Pending</span>
            <span class="fw-bold" id="statPending">0</span>
        </div>
        <div class="d-flex align-items-center px-3 py-2 bg-white border rounded shadow-sm">
            <i class="bi bi-check-circle text-muted me-2"></i>
            <span class="text-muted small me-2">Approved</span>
            <span class="fw-bold" id="statApproved">0</span>
        </div>
        <div class="d-flex align-items-center px-3 py-2 bg-white border rounded shadow-sm">
            <i class="bi bi-x-circle text-muted me-2"></i>
            <span class="text-muted small me-2">Rejected</span>
            <span class="fw-bold" id="statRejected">0</span>
        </div>
        <div class="d-flex align-items-center px-3 py-2 bg-white border rounded shadow-sm">
            <i class="bi bi-arrow-repeat text-muted me-2"></i>
            <span class="text-muted small me-2">Revision</span>
            <span class="fw-bold" id="statRevision">0</span>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm">
        
        <!-- Filter Tabs - Clean -->
        <div class="card-header bg-white border-bottom py-0">
            <ul class="nav nav-tabs border-0" id="statusTabs">
                <li class="nav-item">
                    <a class="nav-link active border-0 py-3 px-4" href="#" data-status="pending_approval">
                        Pending <span class="badge bg-light text-dark ms-1" id="badgePending">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link border-0 py-3 px-4" href="#" data-status="approved">
                        Approved <span class="badge bg-light text-dark ms-1" id="badgeApproved">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link border-0 py-3 px-4" href="#" data-status="rejected">
                        Rejected <span class="badge bg-light text-dark ms-1" id="badgeRejected">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link border-0 py-3 px-4" href="#" data-status="revision_requested">
                        Revision <span class="badge bg-light text-dark ms-1" id="badgeRevision">0</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            
            <!-- Loading Spinner -->
            <div class="text-center py-5" id="loadingSpinner">
                <div class="spinner-border spinner-border-sm text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted small mt-2 mb-0">Loading...</p>
            </div>

            <!-- Vendors Table -->
            <div class="table-responsive" id="vendorsTableContainer" style="display: none;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Vendor</th>
                            <th>Company</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th class="pe-4 text-end" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="vendorsTableBody">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div class="text-center py-5" id="emptyState" style="display: none;">
                <i class="bi bi-inbox fs-2 text-muted"></i>
                <p class="text-muted small mt-2 mb-0" id="emptyMessage">No vendors found.</p>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor-approval-queue.js') }}"></script>
@endpush