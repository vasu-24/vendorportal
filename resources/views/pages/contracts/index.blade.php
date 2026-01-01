@extends('layouts.app')
@section('title', 'Contracts')

@section('content')
<style>
    .card { border: none; border-radius: 8px; }
    .modal-content { border: none; border-radius: 8px; }

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

    /* Table Styling */
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
    .table th {
        color: var(--primary-blue) !important;
        font-size: 13px;
        font-weight: 600;
    }

    /* Contract Number - Blue Link */
    .contract-link {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 500;
    }
    .contract-link:hover {
        color: #0a58ca;
        text-decoration: underline;
    }

    /* Type Badges */
    .badge-normal {
        background: #e0e7ff;
        color: #3730a3;
        font-weight: 500;
        font-size: 11px;
    }
    .badge-adhoc {
        background: #fef3c7;
        color: #92400e;
        font-weight: 500;
        font-size: 11px;
    }

    /* Tag Badge */
    .tag-badge {
        background: #f0fdf4;
        color: #166534;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 8px;
        border-radius: 4px;
        display: inline-block;
        margin: 1px 2px;
    }
</style>

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="page-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <h2 class="page-title">Contract Management</h2>
                <p class="page-subtitle">Manage vendor contracts and documents</p>
            </div>
        </div>
        <a href="{{ route('contracts.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>New Contract
        </a>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search contracts...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select id="vendorFilter" class="form-select form-select-sm">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">
                                {{ $vendor->companyInfo->legal_entity_name ?? $vendor->vendor_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select id="typeFilter" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="normal">Normal</option>
                        <option value="adhoc">ADHOC</option>
                    </select>
                </div>
                <div class="col text-end">
                    <span class="text-muted small">Total: <strong id="totalCount">0</strong> contracts</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 index-table">
                <thead>
                    <tr class="bg-light">
                        <th class="ps-3" style="width: 40px;">#</th>
                        <th>Contract No</th>
                        <th style="width: 90px;">Type</th>
                        <th>Vendor</th>
                        <th>Tags</th>
                        <th>Value</th>
                        <th>Period</th>
                        <th class="text-center" style="width: 50px;">Doc</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="contractsTableBody">
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="ms-2 text-muted">Loading...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-2 d-flex justify-content-between align-items-center">
            <small class="text-muted" id="paginationInfo">Showing 0 of 0</small>
            <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
        </div>
    </div>
</div>

{{-- VIEW MODAL --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold">Contract Details</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalContent"></div>
        </div>
    </div>
</div>

{{-- UPLOAD MODAL --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold">Upload Contract Document</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="uploadContractId">
                
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label mb-1 small text-muted">Contract No</label>
                        <input type="text" id="uploadContractNo" class="form-control form-control-sm bg-light" readonly>
                    </div>
                    <div class="col-6">
                        <label class="form-label mb-1 small text-muted">Value</label>
                        <input type="text" id="uploadContractValue" class="form-control form-control-sm bg-light" readonly>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label mb-1 small text-muted">Vendor</label>
                    <input type="text" id="uploadContractVendor" class="form-control form-control-sm bg-light" readonly>
                </div>

                <hr>

                <div class="mb-3">
                    <label class="form-label mb-1 small text-muted">Current Document</label>
                    <div class="d-flex align-items-center gap-2">
                        <span id="currentDocName" class="text-muted">None</span>
                        <a href="#" id="currentDocView" class="btn btn-sm btn-outline-primary d-none" target="_blank">
                            <i class="bi bi-eye me-1"></i>View
                        </a>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label mb-1 small fw-semibold">Upload New Document</label>
                    <input type="file" id="uploadFile" class="form-control form-control-sm" accept=".doc,.docx,.pdf">
                    <small class="text-muted">Accepts: .doc, .docx, .pdf (Max 10MB)</small>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="uploadBtn" onclick="uploadDocument()">
                    <i class="bi bi-cloud-upload me-1"></i>Upload
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const API_BASE = '/api/admin/contracts';
let currentPage = 1;
let searchQuery = '';
let vendorFilter = '';
let typeFilter = '';

// =====================================================
// INIT
// =====================================================
$(document).ready(function() {
    loadContracts();

    // Search
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchQuery = $(this).val();
            currentPage = 1;
            loadContracts();
        }, 300);
    });

    // Vendor filter
    $('#vendorFilter').on('change', function() {
        vendorFilter = $(this).val();
        currentPage = 1;
        loadContracts();
    });

    // Type filter
    $('#typeFilter').on('change', function() {
        typeFilter = $(this).val();
        currentPage = 1;
        loadContracts();
    });
});

// =====================================================
// LOAD CONTRACTS
// =====================================================
function loadContracts() {
    const tbody = $('#contractsTableBody');
    tbody.html(`
        <tr><td colspan="9" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <span class="ms-2 text-muted">Loading...</span>
        </td></tr>
    `);

    const params = new URLSearchParams({ page: currentPage, per_page: 10 });
    if (searchQuery) params.append('search', searchQuery);
    if (vendorFilter) params.append('vendor_id', vendorFilter);
    if (typeFilter) params.append('contract_type', typeFilter);

    axios.get(`${API_BASE}?${params}`)
        .then(res => {
            if (res.data.success) {
                renderContracts(res.data.data);
                $('#totalCount').text(res.data.data.total || 0);
            }
        })
        .catch(() => {
            tbody.html(`<tr><td colspan="9" class="text-center py-4 text-danger">Failed to load contracts</td></tr>`);
            Toast.error('Failed to load contracts');
        });
}

// =====================================================
// RENDER CONTRACTS
// =====================================================
function renderContracts(data) {
    const contracts = data.data;
    const tbody = $('#contractsTableBody');

    if (!contracts || contracts.length === 0) {
        tbody.html(`<tr><td colspan="9" class="text-center py-4 text-muted">
            <i class="bi bi-inbox fs-3 d-block mb-2"></i>No contracts found
        </td></tr>`);
        $('#paginationInfo').text('Showing 0 of 0');
        $('#pagination').html('');
        return;
    }

    let html = '';
    contracts.forEach((c, i) => {
        // Type Badge
        const isAdhoc = c.contract_type === 'adhoc';
        const typeBadge = isAdhoc 
            ? '<span class="badge badge-adhoc"><i class="bi bi-lightning me-1"></i>ADHOC</span>'
            : '<span class="badge badge-normal"><i class="bi bi-file-earmark-text me-1"></i>Normal</span>';

        // Value Display
        const valueDisplay = isAdhoc
            ? `<div class="fw-semibold">${formatCurrency(c.sow_value)}</div><small class="text-muted">SOW</small>`
            : `<div class="fw-semibold">${formatCurrency(c.contract_value)}</div>`;

        // Tags from items
        let tagsHtml = '-';
        if (c.items && c.items.length > 0) {
            const uniqueTags = [...new Set(c.items.map(item => item.tag_name).filter(t => t))];
            if (uniqueTags.length > 0) {
                tagsHtml = uniqueTags.map(tag => `<span class="tag-badge">${tag}</span>`).join('');
            }
        }

        // Document Icon (only for Normal contracts)
        const docIcon = isAdhoc 
            ? '<i class="bi bi-dash text-muted" title="N/A"></i>'
            : (c.document_path 
                ? '<i class="bi bi-check-circle text-success" title="Uploaded"></i>' 
                : '<i class="bi bi-dash-circle text-muted" title="Not uploaded"></i>');

        // Period
        const startDate = c.start_date ? formatDate(c.start_date) : '-';
        const endDate = c.end_date ? formatDate(c.end_date) : '-';
        const period = (c.start_date || c.end_date) ? `${startDate} — ${endDate}` : '-';

        // Actions - No upload for ADHOC
        let actionsHtml = `
            <button class="btn btn-outline-primary" onclick="viewContract(${c.id})" title="View">
                <i class="bi bi-eye"></i>
            </button>
        `;
        
        if (!isAdhoc) {
            actionsHtml += `
                <button class="btn btn-outline-secondary" onclick="openUploadModal(${c.id})" title="Upload">
                    <i class="bi bi-cloud-upload"></i>
                </button>
            `;
        }
        
        actionsHtml += `
            <a href="/contracts/${c.id}/edit" class="btn btn-outline-secondary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
        `;

        html += `
            <tr>
                <td class="ps-3">${(data.current_page - 1) * data.per_page + i + 1}</td>
                <td>
                    <a href="javascript:void(0)" onclick="viewContract(${c.id})" class="contract-link">
                        ${c.contract_number}
                    </a>
                </td>
                <td>${typeBadge}</td>
                <td>
                    <div class="fw-medium">${c.vendor_name || '-'}</div>
                </td>
                <td>${tagsHtml}</td>
                <td>${valueDisplay}</td>
                <td class="small">${period}</td>
                <td class="text-center">${docIcon}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        ${actionsHtml}
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.html(html);
    $('#paginationInfo').text(`Showing ${data.from || 0}-${data.to || 0} of ${data.total || 0}`);
    renderPagination(data);
}

// =====================================================
// PAGINATION
// =====================================================
function renderPagination(data) {
    const container = $('#pagination');
    if (data.last_page <= 1) { container.html(''); return; }

    let html = `
        <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${data.current_page - 1}); return false;"><i class="bi bi-chevron-left"></i></a>
        </li>
    `;

    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 1 && i <= data.current_page + 1)) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a></li>`;
        } else if (i === data.current_page - 2 || i === data.current_page + 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    html += `
        <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${data.current_page + 1}); return false;"><i class="bi bi-chevron-right"></i></a>
        </li>
    `;

    container.html(html);
}

function goToPage(page) {
    currentPage = page;
    loadContracts();
}

// =====================================================
// VIEW CONTRACT
// =====================================================
function viewContract(id) {
    $('#viewModalContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <span class="ms-2 text-muted">Loading...</span>
        </div>
    `);
    new bootstrap.Modal('#viewModal').show();

    axios.get(`${API_BASE}/${id}`)
        .then(res => {
            if (res.data.success) {
                const c = res.data.data;
                const isAdhoc = c.contract_type === 'adhoc';
                
                // Type Badge
                const typeBadge = isAdhoc 
                    ? '<span class="badge badge-adhoc"><i class="bi bi-lightning me-1"></i>ADHOC Contract</span>'
                    : '<span class="badge badge-normal"><i class="bi bi-file-earmark-text me-1"></i>Normal Contract</span>';

                // Items HTML
                let itemsHtml = '';
                if (c.items?.length) {
                    if (isAdhoc) {
                        // ADHOC: Show only Category + Tag
                        c.items.forEach(item => {
                            itemsHtml += `<tr>
                                <td>${item.category?.name || '-'}</td>
                                <td><span class="tag-badge">${item.tag_name || '-'}</span></td>
                            </tr>`;
                        });
                    } else {
                        // Normal: Show Category, Qty, Unit, Rate, Tag
                        c.items.forEach(item => {
                            itemsHtml += `<tr>
                                <td>${item.category?.name || '-'}</td>
                                <td>${item.quantity}</td>
                                <td>${item.unit}</td>
                                <td>${formatCurrency(item.rate)}</td>
                                <td><span class="tag-badge">${item.tag_name || '-'}</span></td>
                            </tr>`;
                        });
                    }
                }

                // Items Table Header based on type
                const itemsTableHeader = isAdhoc 
                    ? `<tr><th class="small">Category</th><th class="small">Tag</th></tr>`
                    : `<tr><th class="small">Category</th><th class="small">Qty</th><th class="small">Unit</th><th class="small">Rate</th><th class="small">Tag</th></tr>`;

                const itemsColspan = isAdhoc ? 2 : 5;

                // Document Section (only for Normal)
                const documentSection = isAdhoc ? '' : `
                    <h6 class="fw-semibold mt-4 mb-2 text-muted small text-uppercase">Document</h6>
                    ${c.document_path ? `
                        <a href="${'{{ asset("storage") }}/' + c.document_path}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i>View Document
                        </a>
                    ` : '<span class="text-muted small">No document uploaded</span>'}
                `;

                // Value Display
                const valueLabel = isAdhoc ? 'SOW Value' : 'Contract Value';
                const valueAmount = isAdhoc ? c.sow_value : c.contract_value;

                $('#viewModalContent').html(`
                    <div class="mb-3">
                        ${typeBadge}
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Contract Info</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr><td class="text-muted" width="40%">Contract No</td><td class="fw-medium">${c.contract_number}</td></tr>
                                <tr><td class="text-muted">Start Date</td><td>${c.start_date ? formatDate(c.start_date) : '-'}</td></tr>
                                <tr><td class="text-muted">End Date</td><td>${c.end_date ? formatDate(c.end_date) : '-'}</td></tr>
                                <tr><td class="text-muted">${valueLabel}</td><td class="fw-semibold">${formatCurrency(valueAmount)}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Vendor</h6>
                            <table class="table table-sm table-borderless mb-0">
                                ${!isAdhoc ? `<tr><td class="text-muted" width="35%">Company</td><td>${c.company_name || '-'}</td></tr>` : ''}
                                <tr><td class="text-muted" width="35%">Vendor</td><td>${c.vendor_name || '-'}</td></tr>
                            </table>
                            ${documentSection}
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Configurations</h6>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="bg-light">
                            ${itemsTableHeader}
                        </thead>
                        <tbody>${itemsHtml || `<tr><td colspan="${itemsColspan}" class="text-center text-muted small">No items</td></tr>`}</tbody>
                    </table>
                `);
            }
        });
}

// =====================================================
// OPEN UPLOAD MODAL (Only for Normal contracts)
// =====================================================
function openUploadModal(id) {
    $('#uploadContractId').val(id);
    $('#uploadFile').val('');

    axios.get(`${API_BASE}/${id}`)
        .then(res => {
            if (res.data.success) {
                const c = res.data.data;
                
                // Don't allow upload for ADHOC
                if (c.contract_type === 'adhoc') {
                    Toast.warning('ADHOC contracts do not require document upload');
                    return;
                }
                
                $('#uploadContractNo').val(c.contract_number);
                $('#uploadContractVendor').val(c.vendor_name || '-');
                $('#uploadContractValue').val(formatCurrency(c.contract_value));

                if (c.document_path) {
                    const fileName = c.document_path.split('/').pop();
                    $('#currentDocName').text(fileName);
                    $('#currentDocView').attr('href', '{{ asset("storage") }}/' + c.document_path).removeClass('d-none');
                } else {
                    $('#currentDocName').text('None');
                    $('#currentDocView').addClass('d-none');
                }

                new bootstrap.Modal('#uploadModal').show();
            }
        });
}

// =====================================================
// UPLOAD DOCUMENT
// =====================================================
function uploadDocument() {
    const contractId = $('#uploadContractId').val();
    const fileInput = document.getElementById('uploadFile');
    const file = fileInput.files[0];

    if (!file) {
        Toast.warning('Please select a file');
        return;
    }

    const formData = new FormData();
    formData.append('contract_id', contractId);
    formData.append('contract_file', file);

    $('#uploadBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Uploading...');

    axios.post(`${API_BASE}/upload-document`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    })
    .then(res => {
        if (res.data.success) {
            bootstrap.Modal.getInstance('#uploadModal').hide();
            Toast.success('Document uploaded successfully!');
            loadContracts();
        }
    })
    .catch(err => {
        Toast.error(err.response?.data?.message || 'Upload failed');
    })
    .finally(() => {
        $('#uploadBtn').prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i>Upload');
    });
}

// =====================================================
// HELPERS
// =====================================================
function formatDate(str) {
    return new Date(str).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatCurrency(amount) {
    return '₹' + parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endpush