@extends('layouts.app')
@section('title', 'Vendors')

@section('content')
<style>
    .card { border: none; border-radius: 8px; }
    .modal-content { border: none; border-radius: 8px; }
    
    .index-table th { 
        color: var(--primary-blue);
        background: var(--bg-light);
        border-bottom: 2px solid var(--border-grey);
        font-size: 12px;
    }
    .index-table td { 
        vertical-align: middle; 
        font-size: 14px; 
        color: #495057; 
    }
    .index-table tbody tr:hover { 
        background-color: #f1f5f9; 
    }


    .badge-imported { 
    background-color: #e0e7ff; 
    color: #3730a3; 
    font-weight: 500; 
}
    /* Soft Badge Colors */
    .badge-pending { background-color: #fff3cd; color: #856404; font-weight: 500; }
    .badge-approved { background-color: #d4edda; color: #155724; font-weight: 500; }
    .badge-rejected { background-color: #f8d7da; color: #721c24; font-weight: 500; }
    .badge-revision { background-color: #cce5ff; color: #004085; font-weight: 500; }
    .badge-invited { background-color: #e9ecef; color: #495057; font-weight: 500; }
    .badge-accepted { background-color: #d4edda; color: #155724; font-weight: 500; }

    /* Page Header */
    .page-icon {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        background: #eef4ff;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #174081;
        letter-spacing: 0.3px;
        margin: 0;
    }

    /* Clean Tabs */
    .nav-tabs {
        border-bottom: none;
    }
    .nav-tabs .nav-item {
        position: relative;
    }
    .nav-tabs .nav-item:not(:first-child)::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 20px;
        width: 1px;
        background-color: #dee2e6;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        padding: 0.75rem 1rem;
        font-size: 13px;
        font-weight: 500;
        position: relative;
        background: transparent;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background: transparent;
    }
    .nav-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 70%;
        height: 3px;
        background-color: #0d6efd;
        border-radius: 3px 3px 0 0;
    }

    .table th {
        color: var(--primary-blue) !important;
        font-size: 13px;
        font-weight: 600;
    }

    /* Import Dropzone */
    .import-dropzone {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8f9fa;
    }
    .import-dropzone:hover, .import-dropzone.dragover {
        border-color: #0d6efd;
        background: #eef4ff;
    }
    .import-dropzone i {
        font-size: 2.5rem;
        color: #6c757d;
    }
    .import-dropzone.dragover i {
        color: #0d6efd;
    }
    .import-dropzone .file-name {
        margin-top: 0.5rem;
        font-weight: 500;
        color: #198754;
    }

    /* Template Info Box */
    .template-info-box {
        background: linear-gradient(135deg, #f0f7ff 0%, #e8f4fd 100%);
        border: 1px solid #c7dffb;
        border-radius: 8px;
        padding: 12px 16px;
    }
    .template-info-box .template-label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .template-info-box .template-name {
        font-weight: 600;
        color: #1e40af;
        font-size: 14px;
    }
    .template-info-box .template-subject {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
    }
</style>

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon">
                <i class="bi bi-people"></i>
            </div>
            <h2 class="page-title">Vendor Management</h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importVendorModal">
                <i class="bi bi-upload me-1"></i>Import
            </button>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteVendorModal">
                <i class="bi bi-send me-1"></i>Invite Vendor
            </button>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        
        {{-- Tabs with Counts --}}
        <div class="card-header bg-white py-0">
            <ul class="nav nav-tabs border-0" id="vendorTabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#" data-tab="invited">
                        Invited <span class="badge bg-light text-dark ms-1" id="tabCountInvited">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-tab="pending_approval">
                        Pending Approval <span class="badge bg-light text-dark ms-1" id="tabCountPending">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-tab="approved">
                        Approved <span class="badge bg-light text-dark ms-1" id="tabCountApproved">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-tab="rejected">
                        Rejected <span class="badge bg-light text-dark ms-1" id="tabCountRejected">0</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 index-table">
                <thead>
                    <tr class="bg-light">
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Vendor</th>
                        <th id="colThree">Template</th>
                        <th id="colFour">Email Sent</th>
                        <th>Status</th>
<th class="text-center" style="width: 150px;">Actions</th>
<th id="colTravel" class="text-center" style="width: 80px; display: none;">Travel</th>
                </thead>
                <tbody id="vendorsTableBody">
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="ms-2 text-muted">Loading...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

       {{-- Footer --}}
<div class="card-footer bg-white py-2 d-flex justify-content-between align-items-center">
    <small class="text-muted" id="paginationInfo">Showing 0 of 0</small>
    <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
</div>
    </div>
</div>

{{-- INVITE VENDOR MODAL (Single Step - Auto Template) --}}
<div class="modal fade" id="inviteVendorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-primary text-white">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-send me-2"></i>Invite Vendor
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <form id="inviteVendorForm">
                <div class="modal-body">
                    {{-- Vendor Details --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Vendor Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="vendor_name" id="inviteVendorName" 
                               class="form-control" placeholder="Enter vendor name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Vendor Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="vendor_email" id="inviteVendorEmail" 
                               class="form-control" placeholder="vendor@example.com" required>
                    </div>

                    {{-- Auto Selected Template Info with Preview --}}
                    @if(isset($templates) && count($templates) > 0)
                        @php $defaultTemplate = $templates->first(); @endphp
                        <div class="template-info-box mb-3">
                            <div class="template-label">
                                <i class="bi bi-envelope-check me-1"></i>Email Template
                            </div>
                            <div class="template-name">{{ $defaultTemplate->name }}</div>
                        </div>
                        
                        {{-- Template Preview (Read Only) --}}
                        <div class="border rounded">
                            <div class="bg-light px-3 py-2 border-bottom">
                                <small class="text-muted fw-semibold">
                                    <i class="bi bi-eye me-1"></i>Email Preview
                                </small>
                            </div>
                            <div class="p-3">
                                <div class="mb-2">
                                    <label class="small text-muted d-block mb-1">Subject:</label>
                                    <div class="form-control form-control-sm bg-light" readonly style="pointer-events: none;">
                                        {{ $defaultTemplate->subject }}
                                    </div>
                                </div>
                                <div>
                                    <label class="small text-muted d-block mb-1">Body:</label>
                                    <div class="bg-light border rounded p-2" style="min-height: 120px; max-height: 180px; overflow-y: scroll; white-space: pre-line; font-size: 13px;">{{ $defaultTemplate->body }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" id="defaultTemplateId" value="{{ $defaultTemplate->id }}">
                    @else
                        <div class="alert alert-warning py-2 mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            No email template available. Please create one first.
                        </div>
                    @endif
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm" id="inviteVendorBtn" 
                            @if(!isset($templates) || count($templates) == 0) disabled @endif>
                        <i class="bi bi-send me-1"></i>Send Invite
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- IMPORT VENDOR MODAL --}}
<div class="modal fade" id="importVendorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold">Import Vendors</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Download Template --}}
                <div class="mb-3">
                    <a href="{{ asset('templates/vendor_import_template.xlsx') }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-download me-1"></i>Download Excel Template
                    </a>
                    <small class="text-muted d-block mt-1">Fill the template and upload below</small>
                </div>

                <hr>

                {{-- Upload Area --}}
                <div class="import-dropzone" id="importDropzone">
                    <input type="file" id="importFile" accept=".xlsx,.xls" hidden>
                    <i class="bi bi-cloud-upload"></i>
                    <p class="mb-1 mt-2">Drag & drop Excel file here</p>
                    <small class="text-muted">or click to browse</small>
                    <div class="file-name" id="selectedFileName" style="display: none;"></div>
                </div>

                {{-- Import Progress --}}
                <div id="importProgress" style="display: none;" class="mt-3">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                        <span>Importing vendors...</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                    </div>
                </div>

                {{-- Import Results --}}
                <div id="importResults" style="display: none;" class="mt-3">
                    <div class="alert alert-success mb-2 py-2" id="importSuccessAlert" style="display: none;">
                        <i class="bi bi-check-circle me-1"></i>
                        <span id="importSuccessMsg"></span>
                    </div>
                    <div class="alert alert-danger mb-2 py-2" id="importErrorAlert" style="display: none;">
                        <i class="bi bi-x-circle me-1"></i>
                        <span id="importErrorMsg"></span>
                    </div>
                    <div id="importDetails" class="small"></div>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="importBtn" disabled>
                    <i class="bi bi-upload me-1"></i>Import Vendors
                </button>
            </div>
        </div>
    </div>
</div>



{{-- VIEW VENDOR MODAL --}}
<div class="modal fade" id="viewVendorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold">
                    <i class="bi bi-eye me-2"></i>Vendor Details
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="small text-muted d-block mb-1">Vendor Name</label>
                    <div class="form-control bg-light" id="viewVendorName" style="pointer-events: none;"></div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block mb-1">Vendor Email</label>
                    <div class="form-control bg-light" id="viewVendorEmail" style="pointer-events: none;"></div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block mb-1">Template</label>
                    <div class="form-control bg-light" id="viewVendorTemplate" style="pointer-events: none;"></div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="small text-muted d-block mb-1">Status</label>
                        <div id="viewVendorStatus"></div>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted d-block mb-1">Email Sent</label>
                        <div class="form-control bg-light" id="viewVendorEmailSent" style="pointer-events: none;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
  const API_BASE = '/api/vendor/approval';
let currentTab = 'invited';
let currentPage = 1;
let searchQuery = '';
let invitedVendors = @json($vendors ?? []);
let templates = @json($templates ?? []);
let selectedImportFile = null;

    // =====================================================
    // INIT
    // =====================================================
    $(document).ready(function() {
        loadStatistics();
        loadVendors();
        bindEvents();
        bindImportEvents();
    });

    // =====================================================
    // BIND EVENTS
    // =====================================================
    function bindEvents() {
        // Tab click
       // Tab click
$('#vendorTabs .nav-link').on('click', function(e) {
    e.preventDefault();
    $('#vendorTabs .nav-link').removeClass('active');
    $(this).addClass('active');
    currentTab = $(this).data('tab');
    currentPage = 1; // Reset to page 1
    updateTableHeaders();
    loadVendors();
});

        // Invite Vendor Form (Single Step)
        $('#inviteVendorForm').on('submit', function(e) {
            e.preventDefault();
            inviteAndSendEmail();
        });

        // Resend Mail button
        $('#resendMailBtn').on('click', resendMail);

        // Search with debounce
let searchTimeout;
$('#searchInput').on('keyup', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        searchQuery = $(this).val();
        currentPage = 1;
        loadVendors();
    }, 300);
});
    }

    // =====================================================
    // INVITE AND SEND EMAIL (Single Step)
    // =====================================================
    function inviteAndSendEmail() {
        const btn = $('#inviteVendorBtn');
        const vendorName = $('#inviteVendorName').val().trim();
        const vendorEmail = $('#inviteVendorEmail').val().trim();
        const templateId = $('#defaultTemplateId').val();

        if (!vendorName || !vendorEmail) {
            Toast.warning('Please fill all fields');
            return;
        }

        if (!templateId) {
            Toast.error('No email template available');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Sending...');

        // Step 1: Create vendor with template
        axios.post('{{ route("vendors.store") }}', {
            vendor_name: vendorName,
            vendor_email: vendorEmail,
            template_id: templateId
        })
        .then(res => {
            const vendorId = res.data.data?.id || res.data.vendor?.id || res.data.id;
            
            // Step 2: Send email immediately
            return axios.post(`/vendors/${vendorId}/send-email`);
        })
        .then(res => {
            bootstrap.Modal.getInstance('#inviteVendorModal').hide();
            Toast.success('Vendor invited and email sent successfully!');
            $('#inviteVendorForm')[0].reset();
            setTimeout(() => location.reload(), 1000);
        })
        .catch(err => {
            Toast.error(err.response?.data?.message || 'Failed to invite vendor');
        })
        .finally(() => {
            btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Send Invite');
        });
    }

    // =====================================================
    // RESEND MAIL (For existing vendors)
    // =====================================================
    function openResendModal(vendorId, vendorName, vendorEmail) {
        $('#resendVendorId').val(vendorId);
        $('#resendVendorName').text(vendorName);
        $('#resendVendorEmail').text(vendorEmail);
        new bootstrap.Modal('#resendMailModal').show();
    }

    function resendMail() {
        const vendorId = $('#resendVendorId').val();
        const btn = $('#resendMailBtn');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Sending...');

        axios.post(`/vendors/${vendorId}/send-email`)
            .then(res => {
                bootstrap.Modal.getInstance('#resendMailModal').hide();
                Toast.success('Email resent successfully!');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => {
                Toast.error(err.response?.data?.message || 'Failed to resend email');
            })
            .finally(() => {
                btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Resend Email');
            });
    }

    // =====================================================
    // IMPORT EVENTS
    // =====================================================
    function bindImportEvents() {
        const dropzone = document.getElementById('importDropzone');
        const fileInput = document.getElementById('importFile');

        dropzone.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });

        $('#importBtn').on('click', importVendors);
        $('#importVendorModal').on('hidden.bs.modal', resetImportModal);
    }

    function handleFileSelect(file) {
        const validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        
        if (!validTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/i)) {
            Toast.error('Please upload an Excel file (.xlsx or .xls)');
            return;
        }

        selectedImportFile = file;
        $('#selectedFileName').text(file.name).show();
        $('#importBtn').prop('disabled', false);
    }

    function resetImportModal() {
        selectedImportFile = null;
        $('#importFile').val('');
        $('#selectedFileName').hide().text('');
        $('#importBtn').prop('disabled', true);
        $('#importProgress').hide();
        $('#importResults').hide();
        $('#importSuccessAlert').hide();
        $('#importErrorAlert').hide();
        $('#importDetails').html('');
    }

    function importVendors() {
        if (!selectedImportFile) {
            Toast.warning('Please select a file first');
            return;
        }

        const formData = new FormData();
        formData.append('file', selectedImportFile);

        $('#importBtn').prop('disabled', true);
        $('#importProgress').show();
        $('#importResults').hide();

        axios.post('{{ route("vendors.import") }}', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(res => {
            $('#importProgress').hide();
            $('#importResults').show();

            if (res.data.success) {
                $('#importSuccessAlert').show();
                $('#importSuccessMsg').text(res.data.message);

                const data = res.data.data;
                let detailsHtml = `
                    <div class="row text-center mt-2">
                        <div class="col">
                            <div class="fw-bold text-success">${data.imported || 0}</div>
                            <small class="text-muted">Imported</small>
                        </div>
                        <div class="col">
                            <div class="fw-bold text-primary">${data.zoho_synced || 0}</div>
                            <small class="text-muted">Zoho Synced</small>
                        </div>
                        <div class="col">
                            <div class="fw-bold text-warning">${data.zoho_failed || 0}</div>
                            <small class="text-muted">Zoho Failed</small>
                        </div>
                        <div class="col">
                            <div class="fw-bold text-info">${data.emails_sent || 0}</div>
                            <small class="text-muted">Emails Sent</small>
                        </div>
                    </div>
                `;

                if (data.errors && data.errors.length > 0) {
                    detailsHtml += `<div class="mt-3"><strong class="text-danger">Errors:</strong><ul class="mb-0 small">`;
                    data.errors.forEach(err => {
                        detailsHtml += `<li>${err}</li>`;
                    });
                    detailsHtml += `</ul></div>`;
                }

                $('#importDetails').html(detailsHtml);
                setTimeout(() => location.reload(), 2000);
            } else {
                $('#importErrorAlert').show();
                $('#importErrorMsg').text(res.data.message || 'Import failed');
            }
        })
        .catch(err => {
            $('#importProgress').hide();
            $('#importResults').show();
            $('#importErrorAlert').show();
            $('#importErrorMsg').text(err.response?.data?.message || 'Import failed. Please try again.');
        });
    }

    // =====================================================
    // UPDATE TABLE HEADERS
    // =====================================================
  function updateTableHeaders() {
    if (currentTab === 'invited') {
        $('#colThree').text('Template');
        $('#colFour').text('Email Sent');
        $('#colTravel').hide();
    } else if (currentTab === 'approved') {
        $('#colThree').text('Company');
        $('#colFour').text('Submitted');
        $('#colTravel').show();
    } else {
        $('#colThree').text('Company');
        $('#colFour').text('Submitted');
        $('#colTravel').hide();
    }
}





function toggleTravelAccess(vendorId) {
    axios.post(`${API_BASE}/${vendorId}/toggle-travel-access`)
        .then(res => {
            if (res.data.success) {
                Toast.success(res.data.message);
            } else {
                // Revert checkbox
                $(`#travel_${vendorId}`).prop('checked', !$(`#travel_${vendorId}`).prop('checked'));
                Toast.error('Failed to update');
            }
        })
        .catch(err => {
            // Revert checkbox
            $(`#travel_${vendorId}`).prop('checked', !$(`#travel_${vendorId}`).prop('checked'));
            Toast.error('Error updating travel access');
        });
}







    // =====================================================
    // LOAD STATISTICS (Update Tab Counts)
    // =====================================================
    function loadStatistics() {
        // Invited count from local data
        $('#tabCountInvited').text(invitedVendors.filter(v => v.status === 'pending').length);

        // Get other counts from API
        axios.get(`${API_BASE}/statistics`)
            .then(res => {
                if (res.data.success) {
                    const stats = res.data.data;
                    $('#tabCountPending').text(stats.pending_approval || 0);
                    $('#tabCountApproved').text(stats.approved || 0);
                    $('#tabCountRejected').text(stats.rejected || 0);
                }
            })
            .catch(err => console.error('Stats error:', err));
    }

    // =====================================================
    // LOAD VENDORS
    // =====================================================
    // =====================================================
// LOAD VENDORS
// =====================================================
function loadVendors() {
    const tbody = $('#vendorsTableBody');
    
    tbody.html(`
        <tr><td colspan="6" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <span class="ms-2 text-muted">Loading...</span>
        </td></tr>
    `);

    if (currentTab === 'invited') {
        // Filter invited vendors locally (from blade data)
        let filtered = invitedVendors.filter(v => v.status === 'pending' || v.email_sent_at);
        
        // Apply search filter
        if (searchQuery) {
            const query = searchQuery.toLowerCase();
            filtered = filtered.filter(v => 
                (v.vendor_name && v.vendor_name.toLowerCase().includes(query)) ||
                (v.vendor_email && v.vendor_email.toLowerCase().includes(query))
            );
        }
        
        // Paginate locally
        const perPage = 10;
        const total = filtered.length;
        const lastPage = Math.ceil(total / perPage) || 1;
        const from = total > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
        const to = Math.min(currentPage * perPage, total);
        
        const paginatedVendors = filtered.slice((currentPage - 1) * perPage, currentPage * perPage);
        
        renderInvitedVendors(paginatedVendors, { 
            current_page: currentPage, 
            last_page: lastPage, 
            from: from, 
            to: to, 
            total: total 
        });
    } else {
        // Build API URL with pagination
        const params = new URLSearchParams({
            page: currentPage,
            per_page: 10
        });
        if (searchQuery) params.append('search', searchQuery);
        
        axios.get(`${API_BASE}/status/${currentTab}?${params}`)
            .then(res => {
                if (res.data.success) {
                    const data = res.data.data;
                    
                    // Check if paginated response or array
                    if (Array.isArray(data)) {
                        // Not paginated - paginate locally
                        let filtered = data;
                        if (searchQuery) {
                            const query = searchQuery.toLowerCase();
                            filtered = data.filter(v => 
                                (v.vendor_name && v.vendor_name.toLowerCase().includes(query)) ||
                                (v.vendor_email && v.vendor_email.toLowerCase().includes(query))
                            );
                        }
                        
                        const perPage = 10;
                        const total = filtered.length;
                        const lastPage = Math.ceil(total / perPage) || 1;
                        const from = total > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
                        const to = Math.min(currentPage * perPage, total);
                        
                        const paginatedVendors = filtered.slice((currentPage - 1) * perPage, currentPage * perPage);
                        
                        renderApprovalVendors(paginatedVendors, {
                            current_page: currentPage,
                            last_page: lastPage,
                            from: from,
                            to: to,
                            total: total
                        });
                    } else {
                        // Already paginated from API
                        renderApprovalVendors(data.data || [], data);
                    }
                }
            })
            .catch(err => {
                tbody.html(`<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load vendors</td></tr>`);
            });
    }
}
    // =====================================================
    // RENDER INVITED VENDORS
    // =====================================================
    // =====================================================
// RENDER INVITED VENDORS
// =====================================================
function renderInvitedVendors(vendors, paginationData = null) {
    const tbody = $('#vendorsTableBody');

    if (!vendors || vendors.length === 0) {
        tbody.html(`<tr><td colspan="6" class="text-center py-4 text-muted">
            <i class="bi bi-inbox fs-3 d-block mb-2"></i>No invited vendors
        </td></tr>`);
        $('#paginationInfo').text('Showing 0 of 0');
        $('#pagination').html('');
        return;
    }

    let html = '';
    const startIndex = paginationData ? (paginationData.from || 1) : 1;
    
    vendors.forEach((v, i) => {
        const statusBadge = v.status === 'pending' 
            ? '<span class="badge badge-invited">Invited</span>'
            : '<span class="badge badge-accepted">Accepted</span>';

        // Check if imported vendor (approved but no email sent)
        let emailSent;
        if (v.approval_status === 'approved' && !v.email_sent_at) {
            emailSent = '<span class="badge badge-imported"><i class="bi bi-file-earmark-arrow-up me-1"></i>Imported</span>';
        } else if (v.email_sent_at) {
            emailSent = `<i class="bi bi-check-circle text-success me-1"></i>${formatDate(v.email_sent_at)}`;
        } else {
            emailSent = '<span class="text-muted"><i class="bi bi-clock me-1"></i>Pending</span>';
        }

        const templateName = v.template ? v.template.name : '<span class="text-muted">-</span>';

        // Show resend button for pending vendors who already got email
        let actions = '';
        if (v.status === 'pending') {
            if (v.email_sent_at) {
                actions = `<button class="btn btn-sm btn-outline-secondary" onclick="openResendModal(${v.id}, '${escapeHtml(v.vendor_name)}', '${escapeHtml(v.vendor_email)}')">
                    <i class="bi bi-arrow-repeat me-1"></i>Resend
                </button>`;
            } else {
                actions = `<span class="text-muted small">Email queued</span>`;
            }
        } else {
            actions = `<button class="btn btn-sm btn-outline-secondary" onclick="viewVendor(${v.id})">
                <i class="bi bi-eye"></i>
            </button>`;
        }

        html += `
            <tr>
                <td class="ps-3">${startIndex + i}</td>
                <td>
                    <div class="fw-medium">${escapeHtml(v.vendor_name)}</div>
                    <small class="text-muted">${escapeHtml(v.vendor_email)}</small>
                </td>
                <td>${templateName}</td>
                <td class="small">${emailSent}</td>
                <td>${statusBadge}</td>
                <td class="text-center">${actions}</td>
            </tr>
        `;
    });

    tbody.html(html);
    
    // Render pagination
    if (paginationData) {
        renderPagination(paginationData);
    }
}


// RENDER APPROVAL VENDORS
// =====================================================
function renderApprovalVendors(vendors, paginationData = null) {
    const tbody = $('#vendorsTableBody');
    const colspan = currentTab === 'approved' ? 7 : 6;

    if (!vendors || vendors.length === 0) {
        tbody.html(`<tr><td colspan="${colspan}" class="text-center py-4 text-muted">
            <i class="bi bi-inbox fs-3 d-block mb-2"></i>No vendors found
        </td></tr>`);
        $('#paginationInfo').text('Showing 0 of 0');
        $('#pagination').html('');
        return;
    }

    let html = '';
    const startIndex = paginationData ? (paginationData.from || 1) : 1;
    
    vendors.forEach((v, i) => {
        const companyName = v.company_info?.legal_entity_name || '-';
        
        let submittedDate;
        if (v.approval_status === 'approved' && !v.email_sent_at) {
            submittedDate = '<span class="badge badge-imported"><i class="bi bi-file-earmark-arrow-up me-1"></i>Imported</span>';
        } else if (v.registration_completed_at) {
            submittedDate = formatDate(v.registration_completed_at);
        } else {
            submittedDate = '-';
        }

        // Travel checkbox (only for approved tab)
        const travelCheckbox = currentTab === 'approved' 
            ? `<td class="text-center">
                   <div class="form-check form-switch d-flex justify-content-center mb-0">
                       <input class="form-check-input" type="checkbox" 
                              id="travel_${v.id}" 
                              ${v.has_travel_access ? 'checked' : ''} 
                              onchange="toggleTravelAccess(${v.id})"
                              style="cursor: pointer; width: 40px; height: 20px;">
                   </div>
               </td>` 
            : '';

        html += `
            <tr>
                <td class="ps-3">${startIndex + i}</td>
                <td>
                    <div class="fw-medium">${escapeHtml(v.vendor_name)}</div>
                    <small class="text-muted">${escapeHtml(v.vendor_email)}</small>
                </td>
                <td>${escapeHtml(companyName)}</td>
                <td class="small">${submittedDate}</td>
                <td>${getStatusBadge(v.approval_status)}</td>
                <td class="text-center">
                    <a href="/vendors/approval/review/${v.id}" class="btn btn-sm btn-outline-primary">
                        Review
                    </a>
                </td>
                ${travelCheckbox}
            </tr>
        `;
    });

    tbody.html(html);
    
    if (paginationData) {
        renderPagination(paginationData);
    }
}






    // =====================================================
    // VIEW VENDOR
    // =====================================================
    function viewVendor(id) {
        const vendor = invitedVendors.find(v => v.id === id);
        
        if (!vendor) {
            Toast.error('Vendor not found');
            return;
        }

        $('#viewVendorName').text(vendor.vendor_name || '-');
        $('#viewVendorEmail').text(vendor.vendor_email || '-');
        $('#viewVendorTemplate').text(vendor.template ? vendor.template.name : 'Not selected');
        
        // Status badge
        const statusBadge = vendor.status === 'pending' 
            ? '<span class="badge badge-invited">Invited</span>'
            : '<span class="badge badge-accepted">Accepted</span>';
        $('#viewVendorStatus').html(statusBadge);
        
        // Email sent date
        const emailSent = vendor.email_sent_at 
            ? formatDate(vendor.email_sent_at)
            : 'Not sent';
        $('#viewVendorEmailSent').text(emailSent);

        new bootstrap.Modal('#viewVendorModal').show();
    }

    // =====================================================
    // HELPERS
    // =====================================================
    function formatDate(str) {
        if (!str) return '-';
        return new Date(str).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getStatusBadge(status) {
        const badges = {
            'pending_approval': '<span class="badge badge-pending">Pending</span>',
            'approved': '<span class="badge badge-approved">Approved</span>',
            'rejected': '<span class="badge badge-rejected">Rejected</span>',
            'revision_requested': '<span class="badge badge-revision">Revision</span>'
        };
        return badges[status] || `<span class="badge badge-invited">${status}</span>`;
    }



    // =====================================================
// PAGINATION
// =====================================================
function renderPagination(data) {
    const container = $('#pagination');
    
    // Update info text
    $('#paginationInfo').text(`Showing ${data.from || 0} to ${data.to || 0} of ${data.total || 0}`);
    
    // Hide pagination if only 1 page
    if (!data.last_page || data.last_page <= 1) {
        container.html('');
        return;
    }

    let html = '';

    // Previous button
    html += `
        <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${data.current_page - 1}); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 1 && i <= data.current_page + 1)) {
            html += `
                <li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === data.current_page - 2 || i === data.current_page + 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // Next button
    html += `
        <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${data.current_page + 1}); return false;">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;

    container.html(html);
}

function goToPage(page) {
    currentPage = page;
    loadVendors();
}


</script>
@endpush