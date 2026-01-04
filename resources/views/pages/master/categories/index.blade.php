@extends('layouts.app')
@section('title', 'Category Master')

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
        color: #174081;
        margin: 0;
    }
    .page-subtitle {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }

    /* Table */
    .index-table th { 
        color: #174081;
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-size: 12px;
        font-weight: 600;
    }
    .index-table td { 
        vertical-align: middle; 
        font-size: 14px; 
        color: #495057; 
    }
    .index-table tbody tr:hover { 
        background-color: #f1f5f9; 
    }

    /* Badges */
    .badge-active {
        background: #d4edda;
        color: #155724;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
    .badge-inactive {
        background: #f8d7da;
        color: #721c24;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
    .badge-zoho {
        background: #e7f3ff;
        color: #0d6efd;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
    .badge-zoho-unmapped {
        background: #fff3cd;
        color: #856404;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
    .badge-travel {
        background: #d1ecf1;
        color: #0c5460;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }

    /* Travel Filter Checkbox */
    .travel-filter {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 6px;
        padding: 6px 12px;
    }
    .travel-filter .form-check-input:checked {
        background-color: #0ea5e9;
        border-color: #0ea5e9;
    }
</style>

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon">
                <i class="bi bi-tags"></i>
            </div>
            <div>
                <h2 class="page-title">Category Master</h2>
                <p class="page-subtitle">Manage service/product categories for contracts</p>
            </div>
        </div>
        <button class="btn btn-primary btn-sm" onclick="showAddModal()">
            <i class="bi bi-plus-lg me-1"></i>Add Category
        </button>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        
        {{-- Filters --}}
        <div class="card-header bg-white py-2">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" 
                               placeholder="Search categories...">
                    </div>
                </div>
                <div class="col-md-4">
                    {{-- Travel Invoice Filter --}}
                    <div class="travel-filter d-inline-flex align-items-center">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="travelFilterCheckbox">
                            <label class="form-check-label small fw-medium" for="travelFilterCheckbox">
                                <i class="bi bi-airplane me-1"></i>Travel Invoice Only
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted">Total: <strong id="totalCount">0</strong> categories</small>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 index-table">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Category Name</th>
                        <th>Code</th>
                        <th>HSN/SAC</th>
                        <th>Zoho Account</th>
                        <th class="text-center">Travel</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <span class="ms-2 text-muted">Loading...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Footer with Pagination --}}
        <div class="card-footer bg-white py-2 d-flex justify-content-between align-items-center">
            <small class="text-muted" id="paginationInfo">Showing 0 of 0</small>
            <ul class="pagination pagination-sm mb-0" id="pagination"></ul>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold" id="modalTitle">Add Category</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="categoryId">
                    
                    {{-- Name --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" placeholder="e.g., IT Services" required>
                    </div>

                    {{-- Code --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Code</label>
                        <input type="text" class="form-control" id="categoryCode" placeholder="e.g., ITS">
                        <small class="text-muted">Short code for quick reference</small>
                    </div>

                    {{-- HSN/SAC Code --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">HSN/SAC Code</label>
                        <input type="text" class="form-control" id="hsnSacCode" placeholder="e.g., 998311">
                        <small class="text-muted">For GST purposes</small>
                    </div>

                    {{-- Zoho Account Dropdown --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            <i class="bi bi-link-45deg me-1"></i>Zoho Account (COA)
                        </label>
                        <select class="form-select" id="zohoAccountId">
                            <option value="">-- Select Zoho Account --</option>
                        </select>
                        <small class="text-muted">Link this category to a Zoho Chart of Account</small>
                        <div id="zohoLoadingText" class="text-muted small mt-1" style="display: none;">
                            <span class="spinner-border spinner-border-sm me-1"></span>Loading accounts from Zoho...
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea class="form-control" id="categoryDescription" rows="2" placeholder="Brief description..."></textarea>
                    </div>

                    {{-- Travel Invoice Checkbox --}}
                    <div class="mb-3">
                        <div class="form-check p-3 bg-light rounded">
                            <input class="form-check-input" type="checkbox" id="isTravelCategory">
                            <label class="form-check-label fw-medium" for="isTravelCategory">
                                <i class="bi bi-airplane me-1 text-primary"></i>Mark as Travel Invoice Category
                            </label>
                            <div class="text-muted small mt-1">
                                This category will appear in Travel Invoice dropdown
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select class="form-select" id="categoryStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-2 bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="saveBtn">
                        <i class="bi bi-check-lg me-1"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light border-0">
                <h6 class="modal-title fw-semibold text-danger">
                    <i class="bi bi-trash me-2"></i>Delete Category
                </h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteCategoryName"></strong>?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer py-2 bg-light border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const API_BASE = '/api/admin/categories';
const ZOHO_API = '/api/zoho';
let categories = [];
let allCategories = [];
let zohoAccounts = [];
let currentPage = 1;
let deleteId = null;

$(document).ready(function() {
    loadCategories();

    // Search debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            filterAndRenderTable();
        }, 300);
    });

    // Travel filter checkbox
    $('#travelFilterCheckbox').on('change', function() {
        currentPage = 1;
        filterAndRenderTable();
    });

    // Form submit
    $('#categoryForm').on('submit', handleFormSubmit);

    // Delete confirm
    $('#confirmDeleteBtn').on('click', deleteCategory);
});

// =====================================================
// LOAD CATEGORIES
// =====================================================
function loadCategories() {
    const tbody = $('#tableBody');
    tbody.html(`
        <tr><td colspan="8" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <span class="ms-2 text-muted">Loading...</span>
        </td></tr>
    `);

    axios.get(`${API_BASE}?per_page=1000`)
        .then(res => {
            if (res.data.success) {
                allCategories = res.data.data.data || res.data.data;
                filterAndRenderTable();
            }
        })
        .catch(err => {
            tbody.html(`<tr><td colspan="8" class="text-center py-4 text-danger">Failed to load categories</td></tr>`);
            Toast.error('Failed to load categories');
        });
}

// =====================================================
// FILTER AND RENDER TABLE
// =====================================================
function filterAndRenderTable() {
    const searchQuery = $('#searchInput').val().toLowerCase().trim();
    const travelOnly = $('#travelFilterCheckbox').is(':checked');
    
    // Filter categories
    categories = allCategories.filter(cat => {
        // Search filter
        const matchesSearch = !searchQuery || 
            (cat.name && cat.name.toLowerCase().includes(searchQuery)) ||
            (cat.code && cat.code.toLowerCase().includes(searchQuery)) ||
            (cat.zoho_account_name && cat.zoho_account_name.toLowerCase().includes(searchQuery));
        
        // Travel filter
        const matchesTravel = !travelOnly || cat.is_travel_category;
        
        return matchesSearch && matchesTravel;
    });

    // Paginate
    const perPage = 10;
    const total = categories.length;
    const lastPage = Math.ceil(total / perPage) || 1;
    const from = total > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
    const to = Math.min(currentPage * perPage, total);
    
    const paginatedCategories = categories.slice((currentPage - 1) * perPage, currentPage * perPage);

    renderTable(paginatedCategories, { 
        current_page: currentPage, 
        last_page: lastPage, 
        from: from, 
        to: to, 
        total: total,
        per_page: perPage
    });

    $('#totalCount').text(total);
}

// =====================================================
// RENDER TABLE
// =====================================================
function renderTable(categoriesToRender, paginationData) {
    const tbody = $('#tableBody');
    
    if (!categoriesToRender || categoriesToRender.length === 0) {
        tbody.html(`
            <tr><td colspan="8" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>No categories found
            </td></tr>
        `);
        $('#paginationInfo').text('Showing 0 of 0');
        $('#pagination').html('');
        return;
    }

    let html = '';
    categoriesToRender.forEach((cat, index) => {
        const rowNum = paginationData.from + index;
        
        // Zoho Account Badge
        const zohoStatus = cat.zoho_account_id 
            ? `<span class="badge-zoho">${escapeHtml(cat.zoho_account_name) || 'Mapped'}</span>`
            : `<span class="badge-zoho-unmapped">Not Mapped</span>`;

        // Travel Badge
        const travelBadge = cat.is_travel_category 
            ? '<span class="badge-travel"><i class="bi bi-airplane me-1"></i>Yes</span>'
            : '<span class="text-muted">-</span>';

        // Status Badge
        const statusBadge = cat.status === 'active'
            ? '<span class="badge-active">Active</span>'
            : '<span class="badge-inactive">Inactive</span>';

        html += `
            <tr>
                <td class="ps-3">${rowNum}</td>
                <td>
                    <div class="fw-medium">${escapeHtml(cat.name)}</div>
                    ${cat.description ? `<small class="text-muted">${escapeHtml(cat.description.substring(0, 30))}...</small>` : ''}
                </td>
                <td>${cat.code || '-'}</td>
                <td>${cat.hsn_sac_code || '-'}</td>
                <td>${zohoStatus}</td>
                <td class="text-center">${travelBadge}</td>
                <td>${statusBadge}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="editCategory(${cat.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-${cat.status === 'active' ? 'warning' : 'success'}" 
                                onclick="toggleStatus(${cat.id})" 
                                title="${cat.status === 'active' ? 'Deactivate' : 'Activate'}">
                            <i class="bi bi-${cat.status === 'active' ? 'pause-circle' : 'check-circle'}"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="showDeleteModal(${cat.id}, '${escapeHtml(cat.name)}')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.html(html);
    renderPagination(paginationData);
}

// =====================================================
// PAGINATION
// =====================================================
function renderPagination(data) {
    $('#paginationInfo').text(`Showing ${data.from || 0} to ${data.to || 0} of ${data.total || 0}`);
    
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
    filterAndRenderTable();
}

// =====================================================
// LOAD ZOHO ACCOUNTS
// =====================================================
function loadZohoAccounts(selectedAccountId = null) {
    if (zohoAccounts.length > 0) {
        populateZohoDropdown(selectedAccountId);
        return;
    }

    $('#zohoLoadingText').show();
    $('#zohoAccountId').prop('disabled', true);

    axios.get(`${ZOHO_API}/chart-of-accounts`)
        .then(res => {
            $('#zohoLoadingText').hide();
            $('#zohoAccountId').prop('disabled', false);

            if (res.data.success) {
                zohoAccounts = res.data.data || [];
                populateZohoDropdown(selectedAccountId);
            }
        })
        .catch(err => {
            $('#zohoLoadingText').hide();
            $('#zohoAccountId').prop('disabled', false);
            console.error('Failed to load Zoho accounts:', err);
        });
}

function populateZohoDropdown(selectedAccountId = null) {
    let html = '<option value="">-- Select Zoho Account --</option>';
    
    zohoAccounts.forEach(account => {
        const isSelected = selectedAccountId && account.account_id === selectedAccountId ? 'selected' : '';
        const accountType = account.account_type ? ` (${account.account_type})` : '';
        html += `<option value="${account.account_id}" data-name="${escapeHtml(account.account_name)}" ${isSelected}>
            ${escapeHtml(account.account_name)}${accountType}
        </option>`;
    });

    $('#zohoAccountId').html(html);
}

// =====================================================
// SHOW ADD MODAL
// =====================================================
function showAddModal() {
    $('#modalTitle').text('Add Category');
    $('#categoryId').val('');
    $('#categoryForm')[0].reset();
    $('#categoryStatus').val('active');
    $('#zohoAccountId').val('');
    $('#isTravelCategory').prop('checked', false);
    
    loadZohoAccounts();
    new bootstrap.Modal('#categoryModal').show();
}

// =====================================================
// EDIT CATEGORY
// =====================================================
function editCategory(id) {
    const cat = allCategories.find(c => c.id === id);
    if (!cat) return;

    $('#modalTitle').text('Edit Category');
    $('#categoryId').val(cat.id);
    $('#categoryName').val(cat.name);
    $('#categoryCode').val(cat.code || '');
    $('#hsnSacCode').val(cat.hsn_sac_code || '');
    $('#categoryDescription').val(cat.description || '');
    $('#categoryStatus').val(cat.status);
    $('#isTravelCategory').prop('checked', cat.is_travel_category || false);
    
    loadZohoAccounts(cat.zoho_account_id);
    new bootstrap.Modal('#categoryModal').show();
}

// =====================================================
// HANDLE FORM SUBMIT
// =====================================================
function handleFormSubmit(e) {
    e.preventDefault();

    const id = $('#categoryId').val();
    const selectedOption = $('#zohoAccountId option:selected');
    
    const data = {
        name: $('#categoryName').val(),
        code: $('#categoryCode').val() || null,
        hsn_sac_code: $('#hsnSacCode').val() || null,
        description: $('#categoryDescription').val() || null,
        status: $('#categoryStatus').val(),
        zoho_account_id: $('#zohoAccountId').val() || null,
        zoho_account_name: selectedOption.data('name') || null,
     is_travel_category: $('#isTravelCategory').is(':checked') ? 1 : 0
    };

    const btn = $('#saveBtn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

    const request = id 
        ? axios.put(`${API_BASE}/${id}`, data)
        : axios.post(API_BASE, data);

    request
        .then(res => {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                Toast.success(id ? 'Category updated!' : 'Category created!');
                loadCategories();
            }
        })
        .catch(err => {
            Toast.error(err.response?.data?.message || 'Failed to save category');
        })
        .finally(() => {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Save');
        });
}

// =====================================================
// TOGGLE STATUS
// =====================================================
function toggleStatus(id) {
    axios.post(`${API_BASE}/${id}/toggle-status`)
        .then(res => {
            if (res.data.success) {
                Toast.success('Status updated!');
                loadCategories();
            }
        })
        .catch(err => {
            Toast.error('Failed to update status');
        });
}

// =====================================================
// DELETE CATEGORY
// =====================================================
function showDeleteModal(id, name) {
    deleteId = id;
    $('#deleteCategoryName').text(name);
    new bootstrap.Modal('#deleteModal').show();
}

function deleteCategory() {
    if (!deleteId) return;

    const btn = $('#confirmDeleteBtn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting...');

    axios.delete(`${API_BASE}/${deleteId}`)
        .then(res => {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                Toast.success('Category deleted!');
                loadCategories();
            }
        })
        .catch(err => {
            Toast.error(err.response?.data?.message || 'Failed to delete category');
        })
        .finally(() => {
            btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i>Delete');
            deleteId = null;
        });
}

// =====================================================
// HELPER
// =====================================================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush