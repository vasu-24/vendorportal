@extends('layouts.Vendor')
@section('title', 'My Invoices')

@section('content')
<style>
    .card { border: none; border-radius: 8px; }
    .modal-content { border: none; border-radius: 8px; }

    /* Page Header - Same as other pages */
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

    /* Clean Tabs */
    .nav-tabs {
        border-bottom: 1px solid #dee2e6;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        padding: 0.75rem 1rem;
        font-size: 13px;
        font-weight: 500;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
        background: transparent;
    }
    .nav-tabs .nav-link:hover:not(.active) {
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }

    /* Soft Badge Colors */
    .badge-draft { background-color: #e9ecef; color: #495057; font-weight: 500; }
    .badge-submitted { background-color: #e2e3f1; color: #383d6e; font-weight: 500; }
    .badge-under-review { background-color: #fff3cd; color: #856404; font-weight: 500; }
    .badge-approved { background-color: #d4edda; color: #155724; font-weight: 500; }
    .badge-rejected { background-color: #f8d7da; color: #721c24; font-weight: 500; }
    .badge-resubmitted { background-color: #cce5ff; color: #004085; font-weight: 500; }
    .badge-paid { background-color: #d1ecf1; color: #0c5460; font-weight: 500; }
</style>

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="page-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <h2 class="page-title">My Invoices</h2>
                <p class="page-subtitle">Manage and track your invoices</p>
            </div>
        </div>
        <a href="{{ route('vendor.invoices.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>New Invoice
        </a>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        
        {{-- Tabs + Filters --}}
        <div class="card-header bg-white py-0">
            <div class="row align-items-center">
                {{-- Tabs --}}
                <div class="col-lg-7 mb-2 mb-lg-0">
                    <ul class="nav nav-tabs border-0" id="statusTabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-status="">All <span class="text-muted small" id="statTotal">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="submitted">Submitted <span class="text-muted small" id="statSubmitted">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="under_review">Under Review <span class="text-muted small" id="statUnderReview">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="approved">Approved <span class="text-muted small" id="statApproved">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="rejected">Rejected <span class="text-muted small" id="statRejected">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="paid">Paid <span class="text-muted small" id="statPaid">0</span></a>
                        </li>
                    </ul>
                </div>
                {{-- Search --}}
                <div class="col-lg-5 py-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search invoice number...">
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
                        <th>Invoice No</th>
                        <th>Contract</th>
                        <th>Date</th>
                        <th>Base Amount</th>
                        <th>GST</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="invoiceTableBody">
                    <tr>
                        <td colspan="9" class="text-center py-4">
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

@endsection

@push('scripts')
<script>
    const API_BASE = '/api/vendor/invoices';
    let currentPage = 1;
    let currentStatus = '';
    let searchQuery = '';

    // =====================================================
    // INIT
    // =====================================================
    $(document).ready(function() {
        loadStatistics();
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

        // Search
        let searchTimeout;
        $('#searchInput').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchQuery = $(this).val();
                currentPage = 1;
                loadInvoices();
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
                    $('#statTotal').text(s.total || 0);
                    $('#statSubmitted').text(s.submitted || 0);
                    $('#statUnderReview').text(s.under_review || 0);
                    $('#statApproved').text(s.approved || 0);
                    $('#statRejected').text(s.rejected || 0);
                    $('#statPaid').text(s.paid || 0);
                }
            })
            .catch(err => console.error('Stats error:', err));
    }

    // =====================================================
    // LOAD INVOICES
    // =====================================================
    function loadInvoices() {
        const tbody = $('#invoiceTableBody');
        tbody.html(`
            <tr><td colspan="9" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <span class="ms-2 text-muted">Loading...</span>
            </td></tr>
        `);

        const params = new URLSearchParams({ page: currentPage, per_page: 10 });
        if (currentStatus) params.append('status', currentStatus);
        if (searchQuery) params.append('search', searchQuery);

        axios.get(`${API_BASE}?${params}`)
            .then(res => {
                if (res.data.success) {
                    renderInvoices(res.data.data);
                }
            })
            .catch(err => {
                tbody.html(`<tr><td colspan="9" class="text-center py-4 text-danger">Failed to load invoices</td></tr>`);
                Toast.error('Failed to load invoices');
            });
    }

    // =====================================================
    // RENDER INVOICES
    // =====================================================
    function renderInvoices(data) {
        const invoices = data.data;
        const tbody = $('#invoiceTableBody');

        if (!invoices || invoices.length === 0) {
            tbody.html(`<tr><td colspan="9" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No invoices found
                <div class="mt-2">
                    <a href="{{ route('vendor.invoices.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Create Invoice
                    </a>
                </div>
            </td></tr>`);
            $('#paginationInfo').text('Showing 0 of 0');
            $('#paginationContainer').html('');
            return;
        }

        let html = '';
        invoices.forEach((inv, i) => {
            const rowNum = ((data.current_page - 1) * data.per_page) + i + 1;
            
            // Actions
            let actions = `
                <a href="/vendor/invoices/${inv.id}" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                </a>
            `;
            if (inv.status === 'rejected') {
                actions += `
                    <a href="/vendor/invoices/${inv.id}/edit" class="btn btn-outline-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                `;
            }

            html += `
                <tr>
                    <td class="ps-3">${rowNum}</td>
                    <td>
                        <div class="fw-medium">${inv.invoice_number}</div>
                    </td>
                    <td>
                        ${inv.contract ? `<span class="text-primary fw-medium">${inv.contract.contract_number}</span>` : '<span class="text-muted">-</span>'}
                    </td>
                    <td class="small">${formatDate(inv.invoice_date)}</td>
                    <td>₹${formatNumber(inv.base_total)}</td>
                    <td>₹${formatNumber(inv.gst_total)}</td>
                    <td>
                        <div class="fw-semibold">₹${formatNumber(inv.grand_total)}</div>
                    </td>
                    <td>${getStatusBadge(inv.status)}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">${actions}</div>
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
        const container = $('#paginationContainer');
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
        loadInvoices();
    }

    // =====================================================
    // HELPERS
    // =====================================================
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-draft">Draft</span>',
            'submitted': '<span class="badge badge-submitted">Submitted</span>',
            'under_review': '<span class="badge badge-under-review">Under Review</span>',
            'approved': '<span class="badge badge-approved">Approved</span>',
            'rejected': '<span class="badge badge-rejected">Rejected</span>',
            'resubmitted': '<span class="badge badge-resubmitted">Resubmitted</span>',
            'paid': '<span class="badge badge-paid">Paid</span>'
        };
        return badges[status] || `<span class="badge badge-draft">${status}</span>`;
    }

    function formatDate(str) {
        if (!str) return '-';
        return new Date(str).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
</script>
@endpush