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
    .page-subtitle {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }

    /* Header Stats */
    .header-stats {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    .header-stat {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 13px;
        color: #6b7280;
    }
    .header-stat .count {
        font-weight: 600;
        color: #1f2937;
    }

    /* Clean Tabs with Curved Underline */
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
    .nav-tabs .nav-link:hover:not(.active) {
        color: #495057;
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
</style>

<div class="container-fluid py-3">

    {{-- Page Header with Stats --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="page-icon">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <h2 class="page-title">Vendor Management</h2>
                <div class="header-stats mt-1">
                    <span class="header-stat">
                        <i class="bi bi-send text-secondary"></i>
                        <span class="count" id="statInvited">0</span> Invited
                    </span>
                    <span class="header-stat">
                        <i class="bi bi-clock text-warning"></i>
                        <span class="count" id="statPending">0</span> Pending
                    </span>
                    <span class="header-stat">
                        <i class="bi bi-check-circle text-success"></i>
                        <span class="count" id="statApproved">0</span> Approved
                    </span>
                    <span class="header-stat">
                        <i class="bi bi-x-circle text-danger"></i>
                        <span class="count" id="statRejected">0</span> Rejected
                    </span>
                    <span class="header-stat">
                        <i class="bi bi-arrow-repeat text-primary"></i>
                        <span class="count" id="statRevision">0</span> Revision
                    </span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importVendorModal">
                <i class="bi bi-upload me-1"></i>Import
            </button>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                <i class="bi bi-plus-lg me-1"></i>Invite Vendor
            </button>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        
        {{-- Tabs --}}
        <div class="card-header bg-white py-0">
            <ul class="nav nav-tabs border-0" id="vendorTabs">
                <li class="nav-item">
                    <a class="nav-link active" href="#" data-tab="invited">Invited</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-tab="pending_approval">Pending Approval</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-tab="approved">Approved</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-tab="rejected">Rejected</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-tab="revision_requested">Revision</a>
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
                    </tr>
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
        <div class="card-footer bg-white py-2">
            <small class="text-muted" id="tableInfo">Showing 0 vendors</small>
        </div>
    </div>
</div>

{{-- ADD VENDOR MODAL --}}
<div class="modal fade" id="addVendorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold">Invite New Vendor</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <form id="addVendorForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Vendor Name <span class="text-danger">*</span></label>
                        <input type="text" name="vendor_name" id="vendorName" class="form-control" placeholder="Enter vendor name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Vendor Email <span class="text-danger">*</span></label>
                        <input type="email" name="vendor_email" id="vendorEmail" class="form-control" placeholder="vendor@example.com" required>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="addVendorBtn">
                        <i class="bi bi-plus-lg me-1"></i>Add Vendor
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
                  {{-- Change this line in Import Modal --}}
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

{{-- SEND MAIL MODAL --}}
<div class="modal fade" id="sendMailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold">Send Invitation Email</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sendMailVendorId">
                
                {{-- Vendor Info --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label mb-1 small text-muted">Vendor Name</label>
                        <input type="text" id="sendMailVendorName" class="form-control form-control-sm bg-light" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label mb-1 small text-muted">Email</label>
                        <input type="text" id="sendMailVendorEmail" class="form-control form-control-sm bg-light" readonly>
                    </div>
                </div>

                <hr>

                {{-- Template Selection --}}
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Select Template <span class="text-danger">*</span></label>
                    <select id="sendMailTemplate" class="form-select form-select-sm">
                        <option value="">-- Choose Template --</option>
                        @foreach($templates ?? [] as $template)
                            <option value="{{ $template->id }}" 
                                    data-subject="{{ $template->subject }}" 
                                    data-body="{{ $template->body }}">
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject Preview --}}
                <div class="mb-3">
                    <label class="form-label mb-1 small text-muted">Email Subject</label>
                    <div class="p-2 bg-light rounded border small" id="templateSubjectPreview">Select a template to preview</div>
                </div>

                {{-- Body Preview --}}
                <div class="mb-3">
                    <label class="form-label mb-1 small text-muted">Email Content</label>
                    <div class="p-3 bg-light rounded border small" id="templateBodyPreview" style="max-height: 200px; overflow-y: auto; white-space: pre-line;">Select a template to preview</div>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="sendMailBtn" disabled>
                    <i class="bi bi-send me-1"></i>Send Email
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const API_BASE = '/api/vendor/approval';
    let currentTab = 'invited';
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
        $('#vendorTabs .nav-link').on('click', function(e) {
            e.preventDefault();
            $('#vendorTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            currentTab = $(this).data('tab');
            updateTableHeaders();
            loadVendors();
        });

        // Add Vendor Form
        $('#addVendorForm').on('submit', function(e) {
            e.preventDefault();
            addVendor();
        });

        // Template selection in Send Mail modal
        $('#sendMailTemplate').on('change', function() {
            const opt = this.options[this.selectedIndex];
            if (this.value) {
                $('#templateSubjectPreview').text(opt.dataset.subject || 'No subject');
                $('#templateBodyPreview').text(opt.dataset.body || 'No content');
                $('#sendMailBtn').prop('disabled', false);
            } else {
                $('#templateSubjectPreview').text('Select a template to preview');
                $('#templateBodyPreview').text('Select a template to preview');
                $('#sendMailBtn').prop('disabled', true);
            }
        });

        // Send Mail button
        $('#sendMailBtn').on('click', sendMail);
    }

    // =====================================================
    // IMPORT EVENTS
    // =====================================================
    function bindImportEvents() {
        const dropzone = document.getElementById('importDropzone');
        const fileInput = document.getElementById('importFile');

        // Click to browse
        dropzone.addEventListener('click', () => fileInput.click());

        // File selected
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        // Drag & Drop
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

        // Import button
        $('#importBtn').on('click', importVendors);

        // Reset on modal close
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

                // Show details
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

                // Show errors if any
                if (data.errors && data.errors.length > 0) {
                    detailsHtml += `<div class="mt-3"><strong class="text-danger">Errors:</strong><ul class="mb-0 small">`;
                    data.errors.forEach(err => {
                        detailsHtml += `<li>${err}</li>`;
                    });
                    detailsHtml += `</ul></div>`;
                }

                $('#importDetails').html(detailsHtml);

                // Reload after 2 seconds
                setTimeout(() => {
                    location.reload();
                }, 2000);
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
        } else {
            $('#colThree').text('Company');
            $('#colFour').text('Submitted');
        }
    }

    // =====================================================
    // LOAD STATISTICS
    // =====================================================
    function loadStatistics() {
        $('#statInvited').text(invitedVendors.filter(v => v.status === 'pending').length);

        axios.get(`${API_BASE}/statistics`)
            .then(res => {
                if (res.data.success) {
                    const stats = res.data.data;
                    $('#statPending').text(stats.pending_approval || 0);
                    $('#statApproved').text(stats.approved || 0);
                    $('#statRejected').text(stats.rejected || 0);
                    $('#statRevision').text(stats.revision_requested || 0);
                }
            })
            .catch(err => console.error('Stats error:', err));
    }

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
            renderInvitedVendors(invitedVendors);
        } else {
            axios.get(`${API_BASE}/status/${currentTab}`)
                .then(res => {
                    if (res.data.success) {
                        renderApprovalVendors(res.data.data);
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
    function renderInvitedVendors(vendors) {
        const tbody = $('#vendorsTableBody');

        if (!vendors || vendors.length === 0) {
            tbody.html(`<tr><td colspan="6" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No invited vendors
            </td></tr>`);
            $('#tableInfo').text('Showing 0 vendors');
            return;
        }

        let html = '';
        vendors.forEach((v, i) => {
            const statusBadge = v.status === 'pending' 
                ? '<span class="badge badge-invited">Invited</span>'
                : '<span class="badge badge-accepted">Accepted</span>';

            const emailSent = v.email_sent_at 
                ? `<i class="bi bi-check-circle text-success me-1"></i>${formatDate(v.email_sent_at)}`
                : '<span class="text-muted"><i class="bi bi-x-circle me-1"></i>Not sent</span>';

            const templateName = v.template ? v.template.name : '<span class="text-muted">Not selected</span>';

            const actions = v.status === 'pending' 
                ? `<button class="btn btn-sm btn-outline-primary" onclick="openSendMailModal(${v.id}, '${escapeHtml(v.vendor_name)}', '${escapeHtml(v.vendor_email)}', ${v.template_id || 'null'})">
                        <i class="bi bi-send me-1"></i>Send Mail
                   </button>`
                : `<button class="btn btn-sm btn-outline-secondary" onclick="viewVendor(${v.id})">
                        <i class="bi bi-eye"></i>
                   </button>`;

            html += `
                <tr>
                    <td class="ps-3">${i + 1}</td>
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
        $('#tableInfo').text(`Showing ${vendors.length} vendors`);
    }

    // =====================================================
    // RENDER APPROVAL VENDORS
    // =====================================================
    function renderApprovalVendors(vendors) {
        const tbody = $('#vendorsTableBody');

        if (!vendors || vendors.length === 0) {
            tbody.html(`<tr><td colspan="6" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No vendors found
            </td></tr>`);
            $('#tableInfo').text('Showing 0 vendors');
            return;
        }

        let html = '';
        vendors.forEach((v, i) => {
            const companyName = v.company_info?.legal_entity_name || '-';
            const submittedDate = v.registration_completed_at ? formatDate(v.registration_completed_at) : '-';

            html += `
                <tr>
                    <td class="ps-3">${i + 1}</td>
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
                </tr>
            `;
        });

        tbody.html(html);
        $('#tableInfo').text(`Showing ${vendors.length} vendors`);
    }

    // =====================================================
    // ADD VENDOR
    // =====================================================
    function addVendor() {
        const btn = $('#addVendorBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Adding...');

        axios.post('{{ route("vendors.store") }}', {
            vendor_name: $('#vendorName').val(),
            vendor_email: $('#vendorEmail').val()
        })
        .then(res => {
            bootstrap.Modal.getInstance('#addVendorModal').hide();
            Toast.success('Vendor added successfully!');
            $('#addVendorForm')[0].reset();
            setTimeout(() => location.reload(), 1000);
        })
        .catch(err => {
            Toast.error(err.response?.data?.message || 'Failed to add vendor');
        })
        .finally(() => {
            btn.prop('disabled', false).html('<i class="bi bi-plus-lg me-1"></i>Add Vendor');
        });
    }

    // =====================================================
    // OPEN SEND MAIL MODAL
    // =====================================================
    function openSendMailModal(vendorId, vendorName, vendorEmail, templateId) {
        $('#sendMailVendorId').val(vendorId);
        $('#sendMailVendorName').val(vendorName);
        $('#sendMailVendorEmail').val(vendorEmail);
        
        // Auto-select template
        if (templateId) {
            $('#sendMailTemplate').val(templateId).trigger('change');
        } else {
            const firstTemplateId = $('#sendMailTemplate option:eq(1)').val();
            if (firstTemplateId) {
                $('#sendMailTemplate').val(firstTemplateId).trigger('change');
            }
        }
        
        new bootstrap.Modal('#sendMailModal').show();
    }

    // =====================================================
    // SEND MAIL
    // =====================================================
    function sendMail() {
        const vendorId = $('#sendMailVendorId').val();
        const templateId = $('#sendMailTemplate').val();

        if (!templateId) {
            Toast.warning('Please select a template');
            return;
        }

        const btn = $('#sendMailBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Sending...');

        axios.post(`/vendors/${vendorId}/update-template`, { template_id: templateId })
            .then(() => axios.post(`/vendors/${vendorId}/send-email`))
            .then(res => {
                bootstrap.Modal.getInstance('#sendMailModal').hide();
                Toast.success('Email sent successfully!');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => {
                Toast.error(err.response?.data?.message || 'Failed to send email');
            })
            .finally(() => {
                btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Send Email');
            });
    }

    // =====================================================
    // VIEW VENDOR
    // =====================================================
    function viewVendor(id) {
        Toast.info('View details coming soon');
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
</script>
@endpush