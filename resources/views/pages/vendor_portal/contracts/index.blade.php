@extends('layouts.Vendor')
@section('title', 'My Contracts')

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
    .badge-active { background-color: #d4edda; color: #155724; font-weight: 500; }
    .badge-expired { background-color: #f8d7da; color: #721c24; font-weight: 500; }
    .badge-signed { background-color: #cce5ff; color: #004085; font-weight: 500; }
    .badge-pending { background-color: #fff3cd; color: #856404; font-weight: 500; }
    .badge-terminated { background-color: #d6d8db; color: #383d41; font-weight: 500; }
</style>

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="page-icon">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <h2 class="page-title">My Contracts</h2>
                <p class="page-subtitle">View your contracts and upload bills</p>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        
        {{-- Tabs + Search --}}
        <div class="card-header bg-white py-0">
            <div class="row align-items-center">
                {{-- Tabs --}}
                <div class="col-lg-7 mb-2 mb-lg-0">
                    <ul class="nav nav-tabs border-0" id="statusTabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-status="all">All <span class="text-muted small" id="count-all">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="active">Active <span class="text-muted small" id="count-active">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="draft">Draft <span class="text-muted small" id="count-draft">0</span></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-status="expired">Expired <span class="text-muted small" id="count-expired">0</span></a>
                        </li>
                    </ul>
                </div>
                {{-- Search --}}
                <div class="col-lg-5 py-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search contracts...">
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
                        <th>Contract No</th>
                        <th>Value</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="contractsTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-4">
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

{{-- VIEW MODAL --}}
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold">Contract Details</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalContent"></div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-success btn-sm" id="modalUploadBillBtn">
                    <i class="bi bi-upload me-1"></i>Upload Bill
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const API_BASE = '/api/vendor/contracts';
    let currentPage = 1;
    let currentStatus = 'all';
    let searchQuery = '';

    // =====================================================
    // INIT
    // =====================================================
    $(document).ready(function() {
        loadStatistics();
        loadContracts();

        // Tab click
        $('#statusTabs .nav-link').on('click', function(e) {
            e.preventDefault();
            $('#statusTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            currentStatus = $(this).data('status');
            currentPage = 1;
            loadContracts();
        });

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
    });

    // =====================================================
    // LOAD STATISTICS
    // =====================================================
    function loadStatistics() {
        axios.get(`${API_BASE}/statistics`)
            .then(res => {
                if (res.data.success) {
                    const s = res.data.data;
                    $('#count-all').text(s.total || 0);
                    $('#count-active').text(s.active || 0);
                    $('#count-draft').text(s.draft || 0);
                    $('#count-expired').text(s.expired || 0);
                }
            })
            .catch(err => console.error('Stats error:', err));
    }

    // =====================================================
    // LOAD CONTRACTS
    // =====================================================
    function loadContracts() {
        const tbody = $('#contractsTableBody');
        tbody.html(`
            <tr><td colspan="7" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
                <span class="ms-2 text-muted">Loading...</span>
            </td></tr>
        `);

        const params = new URLSearchParams({ page: currentPage, per_page: 10 });
        if (currentStatus !== 'all') params.append('status', currentStatus);
        if (searchQuery) params.append('search', searchQuery);

        axios.get(`${API_BASE}?${params}`)
            .then(res => {
                if (res.data.success) {
                    renderContracts(res.data.data);
                }
            })
            .catch(err => {
                tbody.html(`<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load contracts</td></tr>`);
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
            tbody.html(`<tr><td colspan="7" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>No contracts found
            </td></tr>`);
            $('#paginationInfo').text('Showing 0 of 0');
            $('#paginationContainer').html('');
            return;
        }

        let html = '';
        contracts.forEach((c, i) => {
            html += `
                <tr>
                    <td class="ps-3">${i + 1}</td>
                    <td>
                        <a href="javascript:void(0)" onclick="viewContract(${c.id})" class="contract-link">
                            ${c.contract_number}
                        </a>
                    </td>
                    <td>
                        <div class="fw-semibold">${formatCurrency(c.contract_value)}</div>
                    </td>
                    <td class="small">${c.start_date ? formatDate(c.start_date) : '-'}</td>
                    <td class="small">${c.end_date ? formatDate(c.end_date) : '-'}</td>
                    <td>${getStatusBadge(c.status)}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewContract(${c.id})" title="View">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="{{ url('vendor/invoices/create') }}?contract_id=${c.id}" class="btn btn-success" title="Upload Bill">
                                <i class="bi bi-upload me-1"></i>Upload Bill
                            </a>
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
        $('#modalUploadBillBtn').attr('href', `{{ url('vendor/invoices/create') }}?contract_id=${id}`);

        axios.get(`${API_BASE}/${id}`)
            .then(res => {
                if (res.data.success) {
                    renderContractDetails(res.data.data);
                }
            })
            .catch(err => {
                $('#viewModalContent').html(`<div class="text-center py-4 text-danger">Failed to load contract details</div>`);
            });
    }

    function renderContractDetails(c) {
        $('#viewModalContent').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Contract Info</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" width="40%">Contract No</td><td class="fw-medium">${c.contract_number}</td></tr>
                        <tr><td class="text-muted">Contract Value</td><td class="fw-semibold text-success">${formatCurrency(c.contract_value)}</td></tr>
                        <tr><td class="text-muted">Status</td><td>${getStatusBadge(c.status)}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold mb-3 text-muted small text-uppercase">Period</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" width="40%">Start Date</td><td>${c.start_date ? formatDate(c.start_date) : '-'}</td></tr>
                        <tr><td class="text-muted">End Date</td><td>${c.end_date ? formatDate(c.end_date) : '-'}</td></tr>
                    </table>
                </div>
            </div>
        `);
    }

    // =====================================================
    // HELPERS
    // =====================================================
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-draft">Draft</span>',
            'sent_for_signature': '<span class="badge badge-pending">Pending Signature</span>',
            'signed': '<span class="badge badge-signed">Signed</span>',
            'active': '<span class="badge badge-active">Active</span>',
            'expired': '<span class="badge badge-expired">Expired</span>',
            'terminated': '<span class="badge badge-terminated">Terminated</span>',
        };
        return badges[status] || `<span class="badge badge-draft">${status}</span>`;
    }

    function formatDate(str) {
        return new Date(str).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatCurrency(amount) {
        return '₹' + parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
</script>
@endpush