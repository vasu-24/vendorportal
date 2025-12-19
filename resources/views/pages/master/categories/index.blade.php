@extends('layouts.app')
@section('title', 'Category Master')

@push('styles')
<style>
    #statusTabs .nav-link {
        font-weight: 500;
        color: #6c757d;
        border-bottom: 2px solid transparent;
        padding-bottom: 8px;
    }
    #statusTabs .nav-link:hover {
        color: #212529;
    }
    #statusTabs .nav-link.active {
        color: #212529;
        font-weight: 600;
        border-bottom: 2px solid #212529;
    }
    .zoho-badge {
        font-size: 10px;
        padding: 2px 6px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #174081;">
                <i class="bi bi-tags me-2"></i>Category Master
            </h4>
            <p class="text-muted mb-0 small">Manage service/product categories for contracts</p>
        </div>
        <button class="btn btn-primary btn-sm" onclick="showAddModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Category
        </button>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <!-- Tabs -->
                <div class="col-lg-6 mb-2 mb-lg-0">
                    <ul class="nav gap-4" id="statusTabs">
                        <li class="nav-item">
                            <a class="nav-link px-0 active" href="#" data-status="all">
                                All <span class="text-muted" id="tabAll">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#" data-status="active">
                                Active <span class="text-muted" id="tabActive">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-0" href="#" data-status="inactive">
                                Inactive <span class="text-muted" id="tabInactive">0</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Search -->
                <div class="col-lg-6">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search categories...">
                        <button class="btn btn-outline-secondary" type="button" onclick="loadCategories()">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Loading categories...</p>
            </div>

            <!-- Category Table -->
            <div id="tableContainer" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>HSN/SAC</th>
                                <th>Zoho Account</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Dynamic content -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5" style="display: none;">
                    <i class="bi bi-tags text-muted" style="font-size: 48px;"></i>
                    <p class="text-muted mt-2">No categories found</p>
                    <button class="btn btn-primary btn-sm" onclick="showAddModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add First Category
                    </button>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                    <div class="text-muted small" id="paginationInfo">Showing 0 of 0</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationContainer"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="bi bi-plus-circle me-2"></i>Add Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="categoryId">
                    
                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" placeholder="e.g., IT Services" required>
                        <div class="invalid-feedback" id="error_name"></div>
                    </div>

                    <!-- Code -->
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" id="categoryCode" placeholder="e.g., ITS">
                        <small class="text-muted">Short code for quick reference</small>
                        <div class="invalid-feedback" id="error_code"></div>
                    </div>

                    <!-- HSN/SAC Code -->
                    <div class="mb-3">
                        <label class="form-label">HSN/SAC Code</label>
                        <input type="text" class="form-control" id="hsnSacCode" placeholder="e.g., 998311">
                        <small class="text-muted">For GST purposes</small>
                        <div class="invalid-feedback" id="error_hsn_sac_code"></div>
                    </div>

                    <!-- Zoho Account Dropdown - NEW! -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Zoho Account (COA)
                        </label>
                        <select class="form-select" id="zohoAccountId">
                            <option value="">-- Select Zoho Account --</option>
                        </select>
                        <small class="text-muted">Link this category to a Zoho Chart of Account</small>
                        <div id="zohoLoadingText" class="text-muted small mt-1" style="display: none;">
                            <span class="spinner-border spinner-border-sm me-1"></span>Loading accounts from Zoho...
                        </div>
                        <div id="zohoErrorText" class="text-danger small mt-1" style="display: none;"></div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" rows="2" placeholder="Brief description..."></textarea>
                        <div class="invalid-feedback" id="error_description"></div>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="categoryStatus">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="bi bi-check-lg me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-trash me-2"></i>Delete Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteCategoryName"></strong>?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // =====================================================
    // CONFIGURATION
    // =====================================================
    const API_BASE = '/api/admin/categories';
    const ZOHO_API = '/api/zoho';
    let currentStatus = 'all';
    let currentPage = 1;
    let currentSearch = '';
    let deleteId = null;
    let zohoAccounts = []; // Store fetched accounts

    // =====================================================
    // INITIALIZATION
    // =====================================================
    $(document).ready(function() {
        loadStatistics();
        loadCategories();

        // Tab click handler
        $('#statusTabs .nav-link').on('click', function(e) {
            e.preventDefault();
            $('#statusTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            currentStatus = $(this).data('status');
            currentPage = 1;
            loadCategories();
        });

        // Search on enter
        $('#searchInput').on('keypress', function(e) {
            if (e.which === 13) {
                currentSearch = $(this).val();
                currentPage = 1;
                loadCategories();
            }
        });

        // Form submit
        $('#categoryForm').on('submit', handleFormSubmit);

        // Delete confirm
        $('#confirmDeleteBtn').on('click', deleteCategory);
    });

    // =====================================================
    // LOAD ZOHO ACCOUNTS (COA)
    // =====================================================
    function loadZohoAccounts(selectedAccountId = null) {
        // If already loaded, just populate
        if (zohoAccounts.length > 0) {
            populateZohoDropdown(selectedAccountId);
            return;
        }

        $('#zohoLoadingText').show();
        $('#zohoErrorText').hide();
        $('#zohoAccountId').prop('disabled', true);

        axios.get(`${ZOHO_API}/chart-of-accounts`)
            .then(response => {
                $('#zohoLoadingText').hide();
                $('#zohoAccountId').prop('disabled', false);

                if (response.data.success) {
                    zohoAccounts = response.data.data || [];
                    populateZohoDropdown(selectedAccountId);
                } else {
                    $('#zohoErrorText').text('Failed to load Zoho accounts').show();
                }
            })
            .catch(error => {
                $('#zohoLoadingText').hide();
                $('#zohoAccountId').prop('disabled', false);
                
                const message = error.response?.data?.message || 'Zoho not connected';
                $('#zohoErrorText').text(message).show();
                console.error('Failed to load Zoho accounts:', error);
            });
    }

    // =====================================================
    // POPULATE ZOHO DROPDOWN
    // =====================================================
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
    // LOAD STATISTICS
    // =====================================================
    function loadStatistics() {
        axios.get(`${API_BASE}/statistics`)
            .then(response => {
                if (response.data.success) {
                    const stats = response.data.data;
                    $('#tabAll').text(stats.total || 0);
                    $('#tabActive').text(stats.active || 0);
                    $('#tabInactive').text(stats.inactive || 0);
                }
            })
            .catch(error => console.error('Failed to load statistics:', error));
    }

    // =====================================================
    // LOAD CATEGORIES
    // =====================================================
    function loadCategories() {
        $('#loadingSpinner').show();
        $('#tableContainer').hide();

        let url = `${API_BASE}?page=${currentPage}&per_page=10`;
        if (currentStatus !== 'all') url += `&status=${currentStatus}`;
        if (currentSearch) url += `&search=${encodeURIComponent(currentSearch)}`;

        axios.get(url)
            .then(response => {
                $('#loadingSpinner').hide();
                $('#tableContainer').show();

                if (response.data.success) {
                    const data = response.data.data;
                    renderCategories(data.data || data);
                    if (data.current_page) {
                        renderPagination(data);
                    }
                }
            })
            .catch(error => {
                $('#loadingSpinner').hide();
                $('#tableContainer').show();
                console.error('Failed to load categories:', error);
                showAlert('danger', 'Failed to load categories');
            });
    }

    // =====================================================
    // RENDER CATEGORIES TABLE
    // =====================================================
    function renderCategories(categories) {
        if (!categories || categories.length === 0) {
            $('#tableBody').html('');
            $('#emptyState').show();
            return;
        }

        $('#emptyState').hide();
        let html = '';

        categories.forEach((category, index) => {
            const zohoStatus = category.zoho_account_id 
                ? `<span class="badge bg-info zoho-badge">${escapeHtml(category.zoho_account_name) || 'Mapped'}</span>`
                : `<span class="badge bg-warning zoho-badge">Not Mapped</span>`;

            html += `
                <tr>
                    <td class="ps-4 text-muted">${index + 1}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(category.name)}</div>
                    </td>
                    <td>
                        <span class="text-muted">${category.code || '-'}</span>
                    </td>
                    <td>
                        <span class="text-muted">${category.hsn_sac_code || '-'}</span>
                    </td>
                    <td>${zohoStatus}</td>
                    <td>${getStatusBadge(category.status)}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="editCategory(${category.id})" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-${category.status === 'active' ? 'warning' : 'success'}" 
                                    onclick="toggleStatus(${category.id})" 
                                    title="${category.status === 'active' ? 'Deactivate' : 'Activate'}">
                                <i class="bi bi-${category.status === 'active' ? 'pause' : 'play'}"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="showDeleteModal(${category.id}, '${escapeHtml(category.name)}')" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        $('#tableBody').html(html);
    }

    // =====================================================
    // SHOW ADD MODAL
    // =====================================================
    function showAddModal() {
        $('#modalTitle').html('<i class="bi bi-plus-circle me-2"></i>Add Category');
        $('#categoryId').val('');
        $('#categoryForm')[0].reset();
        $('#categoryStatus').val('active');
        $('#zohoAccountId').val('');
        $('#zohoErrorText').hide();
        clearErrors();
        
        // Load Zoho accounts
        loadZohoAccounts();
        
        new bootstrap.Modal('#categoryModal').show();
    }

    // =====================================================
    // EDIT CATEGORY
    // =====================================================
    function editCategory(id) {
        axios.get(`${API_BASE}/${id}`)
            .then(response => {
                if (response.data.success) {
                    const category = response.data.data;
                    
                    $('#modalTitle').html('<i class="bi bi-pencil me-2"></i>Edit Category');
                    $('#categoryId').val(category.id);
                    $('#categoryName').val(category.name);
                    $('#categoryCode').val(category.code || '');
                    $('#hsnSacCode').val(category.hsn_sac_code || '');
                    $('#categoryDescription').val(category.description || '');
                    $('#categoryStatus').val(category.status);
                    $('#zohoErrorText').hide();
                    
                    // Load Zoho accounts with pre-selected value
                    loadZohoAccounts(category.zoho_account_id);
                    
                    clearErrors();
                    new bootstrap.Modal('#categoryModal').show();
                }
            })
            .catch(error => {
                console.error('Failed to load category:', error);
                showAlert('danger', 'Failed to load category details');
            });
    }

    // =====================================================
    // HANDLE FORM SUBMIT
    // =====================================================
    function handleFormSubmit(e) {
        e.preventDefault();
        clearErrors();

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
        };

        const btn = $('#saveBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        const request = id 
            ? axios.put(`${API_BASE}/${id}`, data)
            : axios.post(API_BASE, data);

        request
            .then(response => {
                if (response.data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                    showAlert('success', response.data.message);
                    loadCategories();
                    loadStatistics();
                }
            })
            .catch(error => {
                if (error.response?.data?.errors) {
                    const errors = error.response.data.errors;
                    Object.keys(errors).forEach(field => {
                        $(`#error_${field}`).text(errors[field][0]);
                        $(`#category${capitalize(field)}, #${field.replace('_', '')}`).addClass('is-invalid');
                    });
                } else {
                    showAlert('danger', error.response?.data?.message || 'Failed to save category');
                }
            })
            .finally(() => {
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Save');
            });
    }

    // =====================================================
    // TOGGLE STATUS
    // =====================================================
    function toggleStatus(id) {
        axios.post(`${API_BASE}/${id}/toggle-status`)
            .then(response => {
                if (response.data.success) {
                    showAlert('success', 'Status updated');
                    loadCategories();
                    loadStatistics();
                }
            })
            .catch(error => {
                console.error('Failed to toggle status:', error);
                showAlert('danger', 'Failed to update status');
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
            .then(response => {
                if (response.data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    showAlert('success', 'Category deleted');
                    loadCategories();
                    loadStatistics();
                }
            })
            .catch(error => {
                showAlert('danger', error.response?.data?.message || 'Failed to delete category');
            })
            .finally(() => {
                btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i> Delete');
                deleteId = null;
            });
    }

    // =====================================================
    // RENDER PAGINATION
    // =====================================================
    function renderPagination(data) {
        const { current_page, last_page, from, to, total } = data;

        $('#paginationInfo').text(`Showing ${from || 0} to ${to || 0} of ${total || 0}`);

        if (last_page <= 1) {
            $('#paginationContainer').html('');
            return;
        }

        let html = '';

        html += `
            <li class="page-item ${current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current_page - 1})">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;

        for (let i = 1; i <= last_page; i++) {
            if (i === 1 || i === last_page || (i >= current_page - 1 && i <= current_page + 1)) {
                html += `
                    <li class="page-item ${i === current_page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
                    </li>
                `;
            } else if (i === current_page - 2 || i === current_page + 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += `
            <li class="page-item ${current_page === last_page ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${current_page + 1})">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;

        $('#paginationContainer').html(html);
    }

    function goToPage(page) {
        currentPage = page;
        loadCategories();
    }

    // =====================================================
    // HELPER FUNCTIONS
    // =====================================================
    function getStatusBadge(status) {
        if (status === 'active') {
            return '<span class="badge bg-success">Active</span>';
        }
        return '<span class="badge bg-secondary">Inactive</span>';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function clearErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function showAlert(type, message) {
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        $('#alertContainer').html(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="bi bi-${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>
@endpush
