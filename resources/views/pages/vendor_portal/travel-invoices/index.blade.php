@extends('layouts.Vendor')
@section('title', 'Travel Invoices')

@section('content')
<style>
    .card { border: none; border-radius: 8px; }
    
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
        color: #0f172a;
        margin: 0;
    }
    .page-subtitle {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }

    /* Tabs */
    .nav-tabs {
        border-bottom: 1px solid #e5e7eb;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #6b7280;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 500;
    }
    .nav-tabs .nav-link.active {
        color: #2563eb;
        border-bottom: 2px solid #2563eb;
        background: transparent;
    }
    .nav-tabs .nav-link:hover:not(.active) {
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
    }
    .tab-count {
        background: #f3f4f6;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        margin-left: 4px;
    }
    .nav-link.active .tab-count {
        background: #dbeafe;
        color: #2563eb;
    }

    /* Table */
    .table th {
        background: #f9fafb;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }
    .table td {
        padding: 14px 16px;
        vertical-align: middle;
        font-size: 14px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
    }
    .table tbody tr:hover {
        background: #f9fafb;
    }

    /* Batch Row */
    .batch-row {
        cursor: pointer;
        transition: background 0.15s;
    }
    .batch-row:hover {
        background: #f0f9ff !important;
    }
    .batch-number {
        font-weight: 600;
        color: #1e40af;
    }
    .batch-toggle {
        transition: transform 0.2s;
    }
    .batch-toggle.expanded {
        transform: rotate(180deg);
    }

    /* Invoice Sub-rows */
    .invoice-details {
        display: none;
        background: #f8fafc;
    }
    .invoice-details.show {
        display: table-row;
    }
    .invoice-sub-table {
        margin: 0;
        background: #fff;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .invoice-sub-table th {
        background: #f1f5f9;
        font-size: 11px;
        padding: 10px 12px;
    }
    .invoice-sub-table td {
        font-size: 13px;
        padding: 10px 12px;
    }

    /* Badges */
    .badge-status {
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
    }
    .badge-draft { background: #f3f4f6; color: #4b5563; }
    .badge-submitted { background: #dbeafe; color: #1e40af; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }
    .badge-paid { background: #e0e7ff; color: #3730a3; }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-state i {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 16px;
    }
    .empty-state h5 {
        color: #374151;
        margin-bottom: 8px;
    }
    .empty-state p {
        color: #6b7280;
        margin-bottom: 20px;
    }

    /* Amount */
    .amount-text {
        font-weight: 600;
        color: #059669;
    }
</style>

<div class="container-fluid py-3">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="page-icon">
                <i class="bi bi-airplane"></i>
            </div>
            <div>
                <h2 class="page-title">Travel Invoices</h2>
                <p class="page-subtitle">Submit and track travel expense invoices</p>
            </div>
        </div>
        <a href="{{ route('vendor.travel-invoices.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>New Submission
        </a>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm">
        
        <!-- Tabs + Search -->
        <div class="card-header bg-white py-0">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <ul class="nav nav-tabs border-0" id="statusTabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-status="all">
                                All <span class="tab-count" id="countAll">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="draft">
                                Draft <span class="tab-count" id="countDraft">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="pending">
                                Pending <span class="tab-count" id="countPending">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="approved">
                                Approved <span class="tab-count" id="countApproved">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="rejected">
                                Rejected <span class="tab-count" id="countRejected">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="paid">
                                Paid <span class="tab-count" id="countPaid">0</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-4 py-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control border-start-0" 
                               placeholder="Search batch or invoice...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>Batch / Invoice</th>
                        <th>Employee</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="ms-2 text-muted">Loading...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="card-footer bg-white py-2 d-flex justify-content-between align-items-center">
            <small class="text-muted" id="paginationInfo">Showing 0 of 0</small>
            <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const API_BASE = '/api/vendor/travel-invoices';
let currentPage = 1;
let currentStatus = 'all';
let searchQuery = '';
let batchesData = [];

// =====================================================
// INITIALIZATION
// =====================================================
$(document).ready(function() {
    loadStatistics();
    loadBatches();

    // Tab click
    $('#statusTabs .nav-link').on('click', function(e) {
        e.preventDefault();
        $('#statusTabs .nav-link').removeClass('active');
        $(this).addClass('active');
        currentStatus = $(this).data('status');
        currentPage = 1;
        loadBatches();
    });

    // Search with debounce
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchQuery = $(this).val();
            currentPage = 1;
            loadBatches();
        }, 300);
    });
});

// =====================================================
// LOAD STATISTICS
// =====================================================
function loadStatistics() {
    axios.get(`${API_BASE}/statistics`)
        .then(res => {
            if (res.data.success) {
                const s = res.data.data;
                $('#countAll').text(s.total_invoices || 0);
                $('#countDraft').text(s.draft || 0);
                $('#countPending').text(s.pending || 0);
                $('#countApproved').text(s.approved || 0);
                $('#countRejected').text(s.rejected || 0);
                $('#countPaid').text(s.paid || 0);
            }
        })
        .catch(err => {});
}

// =====================================================
// LOAD BATCHES
// =====================================================
function loadBatches() {
    const tbody = $('#tableBody');
    tbody.html(`
        <tr>
            <td colspan="7" class="text-center py-5">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <span class="ms-2 text-muted">Loading...</span>
            </td>
        </tr>
    `);

    let url = `${API_BASE}/batches?page=${currentPage}&per_page=10`;
    
    if (searchQuery) {
        url += `&search=${encodeURIComponent(searchQuery)}`;
    }
    
    // FIX: Map 'pending' to include submitted and other pending statuses
    if (currentStatus && currentStatus !== 'all') {
        url += `&status=${currentStatus}`;
    }

    axios.get(url)
        .then(res => {
            if (res.data.success) {
                batchesData = res.data.data.data || [];
                renderBatches(res.data.data);
            }
        })
        .catch(err => {
            tbody.html(`
                <tr>
                    <td colspan="7" class="text-center py-5 text-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>Failed to load data
                    </td>
                </tr>
            `);
            Toast.error('Failed to load travel invoices');
        });
}

// =====================================================
// RENDER BATCHES
// =====================================================
function renderBatches(data) {
    const batches = data.data || [];
    const tbody = $('#tableBody');

    if (!batches || batches.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <i class="bi bi-airplane d-block"></i>
                        <h5>No travel invoices yet</h5>
                        <p>Travel invoices will appear here once submitted</p>
                    </div>
                </td>
            </tr>
        `);
        $('#paginationInfo').text('Showing 0 of 0');
        $('#pagination').html('');
        return;
    }

    let html = '';
    batches.forEach((batch, idx) => {
        const rowNum = ((data.current_page - 1) * data.per_page) + idx + 1;
        const invoiceCount = batch.invoices_count || 0;
        const totalAmount = parseFloat(batch.total_amount || 0);
        
        // Get employee and location summary from batch
        const employeeSummary = batch.employee_summary || '-';
        const locationSummary = batch.location_summary || '-';
        
        // Batch row
        const isDraft = batch.status === 'draft';
        html += `
            <tr class="batch-row" onclick="toggleBatch(${batch.id})">
                <td class="text-center">
                    <i class="bi bi-chevron-down batch-toggle" id="toggle-${batch.id}"></i>
                </td>
                <td>
                    <span class="batch-number">${batch.batch_number}</span>
                    <span class="text-muted small ms-2">(${invoiceCount} invoice${invoiceCount !== 1 ? 's' : ''})</span>
                </td>
                <td>${employeeSummary}</td>
                <td>${locationSummary}</td>
                <td class="small">${formatDate(batch.created_at)}</td>
                <td class="amount-text">₹${formatNumber(totalAmount)}</td>
                <td>
                    ${getStatusBadge(batch.status)}
                    ${isDraft ? `
                        <div class="mt-2" onclick="event.stopPropagation();">
                            <a href="{{ route('vendor.travel-invoices.create') }}?batch_id=${batch.id}" 
                               class="btn btn-outline-primary btn-sm me-1" title="Add Invoice">
                                <i class="bi bi-plus-lg me-1"></i>Add
                            </a>
                            <button class="btn btn-success btn-sm" onclick="submitBatch(${batch.id})" title="Submit Batch">
                                <i class="bi bi-send me-1"></i>Submit
                            </button>
                        </div>
                    ` : ''}
                </td>
            </tr>
            <tr class="invoice-details" id="details-${batch.id}">
                <td colspan="7" class="p-3">
                    <div id="invoices-${batch.id}">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
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
// TOGGLE BATCH (Show/Hide Invoices)
// =====================================================
function toggleBatch(batchId) {
    const detailsRow = $(`#details-${batchId}`);
    const toggle = $(`#toggle-${batchId}`);

    if (detailsRow.hasClass('show')) {
        detailsRow.removeClass('show');
        toggle.removeClass('expanded');
    } else {
        // Close other open batches
        $('.invoice-details.show').removeClass('show');
        $('.batch-toggle.expanded').removeClass('expanded');

        detailsRow.addClass('show');
        toggle.addClass('expanded');
        loadBatchInvoices(batchId);
    }
}

// =====================================================
// LOAD BATCH INVOICES
// =====================================================
function loadBatchInvoices(batchId) {
    const container = $(`#invoices-${batchId}`);
    
    // Show loading
    container.html(`
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <span class="ms-2 text-muted">Loading invoices...</span>
        </div>
    `);

    axios.get(`${API_BASE}/batches/${batchId}`)
        .then(res => {
            if (res.data.success) {
                renderBatchInvoices(batchId, res.data.data);
            } else {
                container.html(`<div class="text-danger text-center py-3">Failed to load invoices</div>`);
            }
        })
        .catch(err => {
            container.html(`
                <div class="text-danger text-center py-3">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Failed to load invoices. Please try again.
                </div>
            `);
        });
}

// =====================================================
// RENDER BATCH INVOICES
// =====================================================
function renderBatchInvoices(batchId, data) {
    const invoices = data.invoices || [];
    const container = $(`#invoices-${batchId}`);

    if (!invoices || invoices.length === 0) {
        container.html(`
            <div class="text-center text-muted py-3">
                <i class="bi bi-inbox me-2"></i>
                <p class="mb-0">No invoices in this batch</p>
            </div>
        `);
        return;
    }

    let html = `
        <table class="table invoice-sub-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice No</th>
                    <th>Type</th>
                    <th>Employee</th>
                    <th>Project</th>
                    <th>Location</th>
                    <th>Invoice Date</th>
                    <th>Travel Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
    `;

    invoices.forEach((inv, idx) => {
        const isCreditNote = inv.invoice_type === 'credit_note';
        const invoiceType = isCreditNote ? 'Credit Note' : 'Tax Invoice';
        
        let amountHtml;
        if (isCreditNote) {
            amountHtml = `<span class="text-danger">-₹${formatNumber(inv.gross_amount || 0)}</span>`;
        } else {
            amountHtml = `<span class="amount-text">₹${formatNumber(inv.gross_amount || 0)}</span>`;
        }
        
        html += `
            <tr>
                <td>${idx + 1}</td>
                <td><strong>${inv.invoice_number || '-'}</strong></td>
                <td>${invoiceType}</td>
                <td>${inv.employee?.employee_name || '-'}</td>
                <td><span class="badge bg-light text-dark">${inv.tag_name || '-'}</span></td>
                <td>${inv.location || '-'}</td>
                <td>${formatDate(inv.invoice_date)}</td>
                <td>${formatDate(inv.travel_date)}</td>
                <td>${amountHtml}</td>
                <td>${getStatusBadge(inv.status)}</td>
            </tr>
        `;
    });

    html += `</tbody></table>`;
    container.html(html);
}
// =====================================================
// SUBMIT BATCH
// =====================================================
function submitBatch(batchId) {
    if (!confirm('Submit this batch? Once submitted, you cannot edit the invoices.')) {
        return;
    }

    axios.post(`${API_BASE}/batches/${batchId}/submit`)
        .then(res => {
            if (res.data.success) {
                Toast.success('Batch submitted successfully!');
                loadStatistics();
                loadBatches();
            }
        })
        .catch(err => {
            Toast.error(err.response?.data?.message || 'Failed to submit batch');
        });
}

// =====================================================
// PAGINATION
// =====================================================
function renderPagination(data) {
    const container = $('#pagination');
    if (data.last_page <= 1) {
        container.html('');
        return;
    }

    let html = `
        <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${data.current_page - 1}); return false;">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;

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
    loadBatches();
}

// =====================================================
// HELPERS
// =====================================================
function getStatusBadge(status) {
    const badges = {
        'draft': '<span class="badge-status badge-draft">Draft</span>',
        'submitted': '<span class="badge-status badge-pending">Pending</span>',
        'resubmitted': '<span class="badge-status badge-pending">Pending</span>',
        'pending': '<span class="badge-status badge-pending">Pending</span>',
        'pending_rm': '<span class="badge-status badge-pending">Pending RM</span>',
        'pending_vp': '<span class="badge-status badge-pending">Pending VP</span>',
        'pending_ceo': '<span class="badge-status badge-pending">Pending CEO</span>',
        'pending_finance': '<span class="badge-status badge-pending">Pending Finance</span>',
        'approved': '<span class="badge-status badge-approved">Approved</span>',
        'rejected': '<span class="badge-status badge-rejected">Rejected</span>',
        'paid': '<span class="badge-status badge-paid">Paid</span>',
        'partial': '<span class="badge-status badge-pending">Partial</span>',
        'completed': '<span class="badge-status badge-approved">Completed</span>'
    };
    return badges[status] || `<span class="badge-status badge-draft">${status || 'Unknown'}</span>`;
}

function formatDate(str) {
    if (!str) return '-';
    try {
        const date = new Date(str);
        if (isNaN(date.getTime())) return '-';
        return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch (e) {
        return '-';
    }
}

function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endpush