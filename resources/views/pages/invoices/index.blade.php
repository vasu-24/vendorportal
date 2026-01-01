@extends('layouts.app')
@section('title', 'Invoice Management')

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

/* Status Badges - STANDARD (Same as Travel Invoice) */
.badge-status { 
    padding: 4px 10px; 
    border-radius: 12px; 
    font-size: 10px; 
    font-weight: 600; 
    text-transform: uppercase; 
}
.badge-draft { background: #e9ecef; color: #495057; }
.badge-submitted { background: #cfe2ff; color: #084298; }
.badge-resubmitted { background: #e2d9f3; color: #6f42c1; }
.badge-under-review { background: #fff3cd; color: #856404; }
.badge-pending-rm { background: #fff3cd; color: #856404; }
.badge-pending-vp { background: #ffe5b4; color: #7a4f01; }
.badge-pending-ceo { background: #f8d7da; color: #842029; }
.badge-pending-finance { background: #d1e7dd; color: #0f5132; }
.badge-approved { background: #d4edda; color: #155724; }
.badge-rejected { background: #f8d7da; color: #721c24; }
.badge-paid { background: #d1ecf1; color: #0c5460; }
    /* Zoho Badge */
    .badge-zoho-synced { background-color: #d4edda; color: #155724; font-weight: 500; }
    .badge-zoho-pending { background-color: #e9ecef; color: #6c757d; font-weight: 500; }
</style>

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="page-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <h2 class="page-title">Invoice Management</h2>
                <p class="page-subtitle">Review and manage vendor invoices</p>
            </div>
        </div>
        <button class="btn btn-outline-secondary btn-sm" onclick="loadInvoices()">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        
        {{-- Tabs + Search/Filter --}}
        <div class="card-header bg-white py-0">
            <div class="row align-items-center">
                {{-- Tabs --}}
                <div class="col-lg-7 mb-2 mb-lg-0">
                    <ul class="nav nav-tabs border-0" id="statusTabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-status="all">All <span class="text-muted small" id="tabAll">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="submitted">Submitted <span class="text-muted small" id="tabSubmitted">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="under_review">Under Review <span class="text-muted small" id="tabUnderReview">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="approved">Approved <span class="text-muted small" id="tabApproved">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="rejected">Rejected <span class="text-muted small" id="tabRejected">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="paid">Paid <span class="text-muted small" id="tabPaid">0</span></a>
                        </li>
                    </ul>
                </div>
                {{-- Search/Filter --}}
                <div class="col-lg-5 py-2">
                    <div class="input-group input-group-sm">
                        <select class="form-select form-select-sm" id="vendorFilter" style="max-width: 160px;">
                            <option value="">All Vendors</option>
                        </select>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search invoices...">
                        <button class="btn btn-outline-secondary" type="button" onclick="loadInvoices()">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 index-table">
                <thead>
                    <tr class="bg-light">
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Invoice</th>
                        <th>Type</th>
                        <th>Contract</th>
                        <th>Vendor</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Zoho</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="invoiceTableBody">
                    <tr>
                        <td colspan="10" class="text-center py-4">
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
            <ul class="pagination pagination-sm mb-0" id="paginationContainer"></ul>
        </div>
    </div>
</div>

{{-- Quick Action Modal --}}
<div class="modal fade" id="quickActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold" id="quickActionTitle">Action</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="quickActionContent"></div>
                <div class="mb-3" id="reasonContainer" style="display: none;">
                    <label class="form-label small fw-semibold">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="actionReason" rows="3" placeholder="Enter reason..."></textarea>
                </div>
                <div class="mb-3" id="paymentRefContainer" style="display: none;">
                    <label class="form-label small fw-semibold">Payment Reference</label>
                    <input type="text" class="form-control" id="paymentReference" placeholder="Transaction ID / UTR">
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const API_BASE = '/api/admin/invoices';
    let currentStatus = 'all';
    let currentPage = 1;
    let currentVendor = '';
    let currentSearch = '';

    // =====================================================
    // INIT
    // =====================================================
    $(document).ready(function() {
        $('#vendorFilter').select2({
            placeholder: 'All Vendors',
            allowClear: true,
            width: '160px'
        });

        loadStatistics();
        loadVendors();
        loadInvoices();

        // Tab click
        $('#statusTabs .nav-link').on('click', function(e) {
            e.preventDefault();
            $('#statusTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            currentStatus = $(this).data('status');
            currentPage = 1;
            loadInvoices();
        });

        // Search on enter
        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                currentSearch = $(this).val();
                currentPage = 1;
                loadInvoices();
            }
        });

        // Vendor filter
        $('#vendorFilter').on('change', function() {
            currentVendor = $(this).val();
            currentPage = 1;
            loadInvoices();
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
                    $('#tabAll').text(s.total || 0);
                    $('#tabSubmitted').text(s.submitted || 0);
                    $('#tabUnderReview').text(s.under_review || 0);
                    $('#tabApproved').text(s.approved || 0);
                    $('#tabRejected').text(s.rejected || 0);
                    $('#tabPaid').text(s.paid || 0);
                }
            })
            .catch(err => console.error('Stats error:', err));
    }

    // =====================================================
    // LOAD VENDORS
    // =====================================================
    function loadVendors() {
        axios.get(`${API_BASE}/vendors`)
            .then(res => {
                if (res.data.success) {
                    let html = '<option value="">All Vendors</option>';
                    res.data.data.forEach(v => {
                        html += `<option value="${v.id}">${escapeHtml(v.vendor_name)}</option>`;
                    });
                    $('#vendorFilter').html(html);
                }
            })
            .catch(err => console.error('Vendors error:', err));
    }

    // =====================================================
    // LOAD INVOICES
    // =====================================================
    function loadInvoices() {
        const tbody = $('#invoiceTableBody');
        tbody.html(`
            <tr><td colspan="10" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <span class="ms-2 text-muted">Loading...</span>
            </td></tr>
        `);

        let url = `${API_BASE}?page=${currentPage}&per_page=10`;
        if (currentStatus !== 'all') url += `&status=${currentStatus}`;
        if (currentVendor) url += `&vendor_id=${currentVendor}`;
        if (currentSearch) url += `&search=${encodeURIComponent(currentSearch)}`;

        axios.get(url)
            .then(res => {
                if (res.data.success) {
                    renderInvoices(res.data.data.data);
                    renderPagination(res.data.data);
                }
            })
            .catch(err => {
                tbody.html(`<tr><td colspan="10" class="text-center py-4 text-danger">Failed to load invoices</td></tr>`);
                Toast.error('Failed to load invoices');
            });
    }

    // =====================================================
    // RENDER INVOICES
    // =====================================================
    function renderInvoices(invoices) {
        const tbody = $('#invoiceTableBody');

        if (!invoices || invoices.length === 0) {
            tbody.html(`<tr><td colspan="10" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No invoices found
            </td></tr>`);
            return;
        }

        let html = '';
        invoices.forEach((inv, i) => {
            html += `
                <tr>
                    <td class="ps-3">${i + 1}</td>
                    <td>
                        <div class="fw-medium">${escapeHtml(inv.invoice_number)}</div>
                        <small class="text-muted">${inv.description ? escapeHtml(inv.description.substring(0, 25)) + '...' : '-'}</small>
                    </td>
                    <td>${getTypeBadge(inv.invoice_type)}</td>
                    <td>
                        ${inv.contract ? `
                            <div class="fw-medium">${escapeHtml(inv.contract.contract_number)}</div>
                            <small class="text-muted">₹${formatNumber(inv.contract.contract_value)}</small>
                        ` : '<span class="text-muted">-</span>'}
                    </td>
                    <td>
                        <div class="fw-medium">${escapeHtml(inv.vendor?.vendor_name || '-')}</div>
                        <small class="text-muted">${escapeHtml(inv.vendor?.vendor_email || '')}</small>
                    </td>
                    <td>
                        <div class="fw-semibold">₹${formatNumber(inv.grand_total)}</div>
                        <small class="text-muted">Base: ₹${formatNumber(inv.base_total)}</small>
                    </td>
                    <td>
                        <div class="small">${formatDate(inv.invoice_date)}</div>
                        <small class="text-muted">Due: ${inv.due_date ? formatDate(inv.due_date) : '-'}</small>
                    </td>
                    <td>${getStatusBadge(inv.status)}</td>
                    <td>${getZohoBadge(inv)}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ url('invoices') }}/${inv.id}" class="btn btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            ${getActionButtons(inv)}
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.html(html);
    }

    // =====================================================
    // TYPE BADGE
    // =====================================================
    function getTypeBadge(type) {
        if (type === 'adhoc') {
            return '<span class="badge-type-adhoc"><i class="bi bi-lightning me-1"></i>ADHOC</span>';
        }
        return '<span class="badge-type-normal"><i class="bi bi-file-earmark-text me-1"></i>Normal</span>';
    }

    // =====================================================
    // ZOHO BADGE
    // =====================================================
    function getZohoBadge(inv) {
        if (inv.zoho_invoice_id) {
            return `<span class="badge badge-zoho-synced" title="Zoho ID: ${inv.zoho_invoice_id}">
                <i class="bi bi-check-circle me-1"></i>Synced
            </span>`;
        }
        return '<span class="badge badge-zoho-pending">Not Synced</span>';
    }

    // =====================================================
    // ACTION BUTTONS
    // =====================================================
    function getActionButtons(inv) {
        let btns = '';

        if (inv.status === 'submitted') {
            btns += `<button class="btn btn-outline-warning" onclick="startReview(${inv.id})" title="Start Review"><i class="bi bi-hourglass-split"></i></button>`;
        }

        if (inv.status === 'under_review') {
            btns += `
                <button class="btn btn-outline-success" onclick="showApproveModal(${inv.id})" title="Approve"><i class="bi bi-check-lg"></i></button>
                <button class="btn btn-outline-danger" onclick="showRejectModal(${inv.id})" title="Reject"><i class="bi bi-x-lg"></i></button>
            `;
        }

        if (inv.status === 'approved') {
            btns += `<button class="btn btn-outline-primary" onclick="showMarkPaidModal(${inv.id})" title="Mark Paid"><i class="bi bi-currency-rupee"></i></button>`;
        }

        return btns;
    }

    // =====================================================
    // QUICK ACTIONS
    // =====================================================
    let currentActionId = null;
    let currentAction = null;

    function startReview(id) {
        if (!confirm('Start reviewing this invoice?')) return;

        axios.post(`${API_BASE}/${id}/start-review`)
            .then(res => {
                if (res.data.success) {
                    Toast.success('Invoice is now under review');
                    loadInvoices();
                    loadStatistics();
                }
            })
            .catch(err => Toast.error('Failed to start review'));
    }

    function showApproveModal(id) {
        currentActionId = id;
        currentAction = 'approve';
        $('#quickActionTitle').text('Approve Invoice');
        $('#quickActionContent').html('<p>Are you sure you want to approve this invoice?</p><p class="small text-muted"><i class="bi bi-info-circle me-1"></i>This will also push the bill to Zoho Books.</p>');
        $('#reasonContainer').hide();
        $('#paymentRefContainer').hide();
        $('#confirmActionBtn').removeClass().addClass('btn btn-success btn-sm').text('Approve');
        new bootstrap.Modal('#quickActionModal').show();
    }

    function showRejectModal(id) {
        currentActionId = id;
        currentAction = 'reject';
        $('#quickActionTitle').text('Reject Invoice');
        $('#quickActionContent').html('<p class="text-danger">Are you sure you want to reject this invoice?</p>');
        $('#reasonContainer').show();
        $('#actionReason').val('');
        $('#paymentRefContainer').hide();
        $('#confirmActionBtn').removeClass().addClass('btn btn-danger btn-sm').text('Reject');
        new bootstrap.Modal('#quickActionModal').show();
    }

    function showMarkPaidModal(id) {
        currentActionId = id;
        currentAction = 'mark_paid';
        $('#quickActionTitle').text('Mark as Paid');
        $('#quickActionContent').html('<p>Mark this invoice as paid?</p>');
        $('#reasonContainer').hide();
        $('#paymentRefContainer').show();
        $('#paymentReference').val('');
        $('#confirmActionBtn').removeClass().addClass('btn btn-primary btn-sm').text('Mark Paid');
        new bootstrap.Modal('#quickActionModal').show();
    }

    $('#confirmActionBtn').on('click', function() {
        if (!currentActionId || !currentAction) return;

        let url = '';
        let data = {};

        switch (currentAction) {
            case 'approve':
                url = `${API_BASE}/${currentActionId}/approve`;
                break;
            case 'reject':
                const reason = $('#actionReason').val().trim();
                if (!reason) { Toast.warning('Please enter a rejection reason'); return; }
                url = `${API_BASE}/${currentActionId}/reject`;
                data = { rejection_reason: reason };
                break;
            case 'mark_paid':
                url = `${API_BASE}/${currentActionId}/mark-paid`;
                data = { payment_reference: $('#paymentReference').val() };
                break;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

        axios.post(url, data)
            .then(res => {
                if (res.data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('quickActionModal')).hide();
                    Toast.success(res.data.message || 'Action completed successfully');
                    loadInvoices();
                    loadStatistics();
                }
            })
            .catch(err => Toast.error(err.response?.data?.message || 'Action failed'))
            .finally(() => {
                btn.prop('disabled', false).text('Confirm');
                currentActionId = null;
                currentAction = null;
            });
    });

    // =====================================================
    // PAGINATION
    // =====================================================
    function renderPagination(data) {
        const { current_page, last_page, from, to, total } = data;
        $('#paginationInfo').text(`Showing ${from || 0} to ${to || 0} of ${total || 0}`);

        if (last_page <= 1) { $('#paginationContainer').html(''); return; }

        let html = `
            <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current_page - 1}); return false;"><i class="bi bi-chevron-left"></i></a>
            </li>
        `;

        for (let i = 1; i <= last_page; i++) {
            if (i === 1 || i === last_page || (i >= current_page - 1 && i <= current_page + 1)) {
                html += `<li class="page-item ${i === current_page ? 'active' : ''}"><a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a></li>`;
            } else if (i === current_page - 2 || i === current_page + 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += `
            <li class="page-item ${current_page === last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current_page + 1}); return false;"><i class="bi bi-chevron-right"></i></a>
            </li>
        `;

        $('#paginationContainer').html(html);
    }

    function goToPage(page) {
        currentPage = page;
        loadInvoices();
    }

    // =====================================================
    // HELPERS
    // =====================================================
   function getStatusBadge(status) {
    const badges = {
        'draft': '<span class="badge-status badge-draft">Draft</span>',
        'submitted': '<span class="badge-status badge-submitted">Submitted</span>',
        'resubmitted': '<span class="badge-status badge-resubmitted">Resubmitted</span>',
        'under_review': '<span class="badge-status badge-under-review">Under Review</span>',
        'pending_rm': '<span class="badge-status badge-pending-rm">Pending RM</span>',
        'pending_vp': '<span class="badge-status badge-pending-vp">Pending VP</span>',
        'pending_ceo': '<span class="badge-status badge-pending-ceo">Pending CEO</span>',
        'pending_finance': '<span class="badge-status badge-pending-finance">Pending Finance</span>',
        'approved': '<span class="badge-status badge-approved">Approved</span>',
        'rejected': '<span class="badge-status badge-rejected">Rejected</span>',
        'paid': '<span class="badge-status badge-paid">Paid</span>'
    };
    return badges[status] || `<span class="badge-status badge-draft">${status}</span>`;
}

    function formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

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
</script>
@endpush