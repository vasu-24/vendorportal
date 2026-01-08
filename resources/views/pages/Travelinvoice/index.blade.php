@extends('layouts.app')
@section('title', 'Travel Invoice Management')

@section('content')
<style>
    .card { border: none; border-radius: 8px; }

    /* Page Header */
    .page-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #eef4ff;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .page-title { font-size: 20px; font-weight: 700; color: #174081; margin: 0; }
    .page-subtitle { font-size: 12px; color: #6b7280; margin: 0; }

    /* Tabs */
    .nav-tabs { border-bottom: none; }
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 500;
        background: transparent;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
    }

    /* Table */
    .batch-table th {
        background: #f8f9fa;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #174081;
        padding: 10px 12px;
        border-bottom: 2px solid #dee2e6;
    }
    .batch-table td {
        padding: 12px;
        vertical-align: middle;
        font-size: 13px;
    }
    .batch-table tbody tr:hover { background: #f8fafc; }

    /* Badges */
    .badge-batch { background: #6c757d; color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .badge-count { background: #e7f3ff; color: #0d6efd; padding: 3px 8px; border-radius: 10px; font-size: 11px; }
    
    /* Status Badges - ALL STATUSES */
    .badge-status { padding: 4px 10px; border-radius: 12px; font-size: 10px; font-weight: 600; text-transform: uppercase; }
    .badge-draft { background: #e9ecef; color: #495057; }
    .badge-submitted { background: #cfe2ff; color: #084298; }
    .badge-pending_rm { background: #fff3cd; color: #856404; }
    .badge-pending_vp { background: #ffe5b4; color: #7a4f01; }
    .badge-pending_ceo { background: #f8d7da; color: #842029; }
    .badge-pending_finance { background: #d1e7dd; color: #0f5132; }
    .badge-approved { background: #d4edda; color: #155724; }
    .badge-rejected { background: #f8d7da; color: #721c24; }
    .badge-paid { background: #d1ecf1; color: #0c5460; }
    .badge-partial { background: #ffc107; color: #000; }

    .amount-text { font-weight: 600; color: #198754; }
    
    /* Buttons */
    .btn-view { padding: 4px 12px; font-size: 11px; }

    /* ============================================= */
    /* SELECT2 STYLING */
    /* ============================================= */
    .select2-container--default .select2-selection--single {
        height: 31px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 13px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px;
        color: #495057;
        padding-left: 0;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 30px;
    }
    
    .select2-dropdown {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin-top: 4px;
    }
    
    .select2-container--default .select2-results__option {
        padding: 8px 12px;
        font-size: 13px;
    }
    
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
        color: white;
    }
    
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #e7f1ff;
        color: #0d6efd;
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 6px 10px;
        font-size: 13px;
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #86b7fe;
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    /* Filter Row */
    .filter-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <div class="page-icon"><i class="bi bi-airplane"></i></div>
            <div>
                <h2 class="page-title">Travel Invoices</h2>
                <p class="page-subtitle">Review and approve travel expenses</p>
            </div>
        </div>
        <a href="{{ route('admin.travel-employees.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-person-vcard me-1"></i>Employee Master
        </a>
    </div>

    <!-- Card -->
    <div class="card shadow-sm">
        <!-- Tabs + Filter + Search -->
        <div class="card-header bg-white py-0 d-flex justify-content-between align-items-center">
            <ul class="nav nav-tabs border-0" id="statusTabs">
                <li class="nav-item"><a class="nav-link active" href="#" data-status="all">All <span class="text-muted" id="tabAll">0</span></a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-status="pending">Pending <span class="text-muted" id="tabPending">0</span></a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-status="approved">Approved <span class="text-muted" id="tabApproved">0</span></a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-status="rejected">Rejected <span class="text-muted" id="tabRejected">0</span></a></li>
                <li class="nav-item"><a class="nav-link" href="#" data-status="paid">Paid <span class="text-muted" id="tabPaid">0</span></a></li>
            </ul>
            <div class="filter-row py-2">
                <select id="vendorFilter" class="form-select form-select-sm">
                    <option value="">All Vendors</option>
                </select>
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search..." style="width: 180px;">
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table batch-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Batch</th>
                        <th>Invoices</th>
                        <th>Vendor</th>
                        <th>Employee</th>
                        <th>Location</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>
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
const API_BASE = '/api/admin/travel-invoices';
const USER_ROLE = '{{ auth()->user()->role->slug ?? "super-admin" }}';

let currentStatus = 'all';
let currentPage = 1;
let currentVendor = '';
let searchQuery = '';

$(document).ready(function() {
    // Initialize Select2
    $('#vendorFilter').select2({
        placeholder: 'All Vendors',
        allowClear: false,
        width: '180px',
        minimumResultsForSearch: 5
    });

    loadStatistics();
    loadVendors();
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

    // Vendor filter change
    $('#vendorFilter').on('change', function() {
        currentVendor = $(this).val();
        currentPage = 1;
        loadBatches();
    });

    // Search
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
// LOAD VENDORS
// =====================================================
function loadVendors() {
    axios.get(`${API_BASE}/vendors`)
        .then(res => {
            if (res.data.success) {
                let options = [{ id: '', text: 'All Vendors' }];
                res.data.data.forEach(v => {
                    options.push({ id: v.id, text: v.vendor_name });
                });
                
                $('#vendorFilter').empty();
                $('#vendorFilter').select2({
                    placeholder: 'All Vendors',
                    allowClear: false,
                    width: '180px',
                    minimumResultsForSearch: 5,
                    data: options
                });
            }
        })
        .catch(err => console.error('Vendors load error:', err));
}

// =====================================================
// LOAD STATISTICS
// =====================================================
function loadStatistics() {
    axios.get(`${API_BASE}/statistics`)
        .then(res => {
            if (res.data.success) {
                const s = res.data.data;
                const pending = (s.submitted || 0) + (s.pending_rm || 0) + (s.pending_vp || 0) + (s.pending_ceo || 0) + (s.pending_finance || 0);
                $('#tabAll').text(s.total_batches || 0);
                $('#tabPending').text(pending);
                $('#tabApproved').text(s.approved || 0);
                $('#tabRejected').text(s.rejected || 0);
                $('#tabPaid').text(s.paid || 0);
            }
        });
}

// =====================================================
// LOAD BATCHES
// =====================================================
function loadBatches() {
    const tbody = $('#tableBody');
    tbody.html('<tr><td colspan="10" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>');

    let url = `${API_BASE}/batches?page=${currentPage}&per_page=15`;
    if (currentStatus !== 'all') url += `&status=${currentStatus}`;
    if (currentVendor) url += `&vendor_id=${currentVendor}`;
    if (searchQuery) url += `&search=${encodeURIComponent(searchQuery)}`;

    axios.get(url)
        .then(res => {
            if (res.data.success) {
                renderBatches(res.data.data, res.data.user_role);
            }
        })
        .catch(() => {
            tbody.html('<tr><td colspan="10" class="text-center py-4 text-danger">Failed to load</td></tr>');
        });
}

// =====================================================
// RENDER BATCHES
// =====================================================
function renderBatches(data, userRole) {
    const batches = data.data || [];
    const tbody = $('#tableBody');

    if (batches.length === 0) {
        tbody.html('<tr><td colspan="10" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>No batches found</td></tr>');
        return;
    }

    let html = '';
    batches.forEach((batch, i) => {
      
        const approvableCount = batch.approvable_count || 0;
        
        html += `
            <tr>
                <td>${(data.current_page - 1) * 15 + i + 1}</td>
                <td><span class="badge-batch">${batch.batch_number}</span></td>
                <td><span class="badge-count">${batch.invoices_count || 0} invoices</span></td>
                <td>${batch.vendor?.vendor_name || '-'}</td>
                <td>${batch.employee_summary || '-'}</td>
                <td>${batch.location_summary || '-'}</td>
                <td class="amount-text">₹${formatNumber(batch.total_amount)}</td>
                <td>${getStatusBadge(batch.status)}</td>
                <td>${formatDate(batch.created_at)}</td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="/admin/travel-invoices/batch/${batch.id}" class="btn btn-primary btn-view">
                            <i class="bi bi-eye me-1"></i>View${approvableCount > 0 ? ` (${approvableCount})` : ''}
                        </a>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.html(html);
    renderPagination(data);
}

// =====================================================
// RENDER PAGINATION
// =====================================================
function renderPagination(data) {
    $('#paginationInfo').text(`Showing ${data.from || 0} to ${data.to || 0} of ${data.total || 0}`);
    
    if (data.last_page <= 1) {
        $('#pagination').html('');
        return;
    }

    let html = `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="goToPage(${data.current_page - 1}); return false;">«</a></li>`;
    
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 1 && i <= data.current_page + 1)) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a></li>`;
        } else if (i === data.current_page - 2 || i === data.current_page + 2) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="goToPage(${data.current_page + 1}); return false;">»</a></li>`;
    
    $('#pagination').html(html);
}

function goToPage(page) {
    currentPage = page;
    loadBatches();
}

// =====================================================
// HELPERS
// =====================================================
function getStatusBadge(status) {
    const labels = {
        'draft': 'Draft',
        'submitted': 'Submitted',
        'pending_rm': 'Pending RM',
        'pending_vp': 'Pending VOO',
        'pending_ceo': 'Pending CEO',
        'pending_finance': 'Pending Finance',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'paid': 'Paid',
        'partial': 'Partial'
    };
    return `<span class="badge-status badge-${status}">${labels[status] || status}</span>`;
}

function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(str) {
    if (!str) return '-';
    return new Date(str).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>
@endpush