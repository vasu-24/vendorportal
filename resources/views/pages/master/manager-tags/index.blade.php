@extends('layouts.app')

@section('title', 'Manager Master')

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

    /* Tag Badge */
    .badge-tag {
        background: #e7f3ff;
        color: #0d6efd;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
        margin: 2px;
        display: inline-block;
    }
</style>

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon">
                <i class="bi bi-person-badge"></i>
            </div>
            <div>
                <h2 class="page-title">Manager Master</h2>
                <p class="page-subtitle">Assign tags to managers for approval routing</p>
            </div>
        </div>
        <button class="btn btn-primary btn-sm" onclick="openModal()">
            <i class="bi bi-plus-lg me-1"></i>Assign Tags
        </button>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        
        {{-- Search --}}
        <div class="card-header bg-white py-2">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" 
                               placeholder="Search managers...">
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <small class="text-muted">Total: <strong id="totalCount">0</strong> managers</small>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 index-table">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Manager Name</th>
                        <th>Email</th>
                        <th>Assigned Tags</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="managerTableBody">
                    <tr>
                        <td colspan="5" class="text-center py-4">
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

{{-- Assign Tags Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold">Assign Tags to Manager</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignForm">
                    {{-- Manager Select --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Manager <span class="text-danger">*</span></label>
                        <select class="form-select" id="managerSelect" required>
                            <option value="">-- Select Manager --</option>
                        </select>
                    </div>

                    {{-- Tags Checkboxes --}}
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Assign Tags <span class="text-danger">*</span></label>
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;" id="tagsContainer">
                            <div class="text-center text-muted">
                                <div class="spinner-border spinner-border-sm"></div>
                                <span class="ms-2">Loading tags...</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveTags()">
                    <i class="bi bi-check-lg me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const API_BASE = '/api/admin/manager-tags';
let managers = [];
let allManagers = []; // For filtering
let zohoTags = [];
let assignModal;
let currentPage = 1;

$(document).ready(function() {
    assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
    loadManagers();
    loadZohoTags();
    loadManagersDropdown();

    // Search debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            filterAndRenderTable();
        }, 300);
    });
});

// =====================================================
// LOAD MANAGERS LIST
// =====================================================
function loadManagers() {
    const tbody = $('#managerTableBody');
    tbody.html(`
        <tr><td colspan="5" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <span class="ms-2 text-muted">Loading...</span>
        </td></tr>
    `);

    axios.get(API_BASE)
        .then(response => {
            allManagers = response.data.data;
            managers = [...allManagers];
            filterAndRenderTable();
        })
        .catch(error => {
            tbody.html(`<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load managers</td></tr>`);
            Toast.error('Failed to load managers');
        });
}

// =====================================================
// FILTER AND RENDER TABLE
// =====================================================
function filterAndRenderTable() {
    const searchQuery = $('#searchInput').val().toLowerCase().trim();
    
    // Filter managers
    if (searchQuery) {
        managers = allManagers.filter(m => 
            m.name.toLowerCase().includes(searchQuery) ||
            m.email.toLowerCase().includes(searchQuery) ||
            m.tags.some(t => t.tag_name.toLowerCase().includes(searchQuery))
        );
    } else {
        managers = [...allManagers];
    }

    // Paginate
    const perPage = 10;
    const total = managers.length;
    const lastPage = Math.ceil(total / perPage) || 1;
    const from = total > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
    const to = Math.min(currentPage * perPage, total);
    
    const paginatedManagers = managers.slice((currentPage - 1) * perPage, currentPage * perPage);

    renderTable(paginatedManagers, { 
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
function renderTable(managersToRender, paginationData) {
    const tbody = $('#managerTableBody');
    
    if (!managersToRender || managersToRender.length === 0) {
        tbody.html(`
            <tr><td colspan="5" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>No managers found. Click "Assign Tags" to add.
            </td></tr>
        `);
        $('#paginationInfo').text('Showing 0 of 0');
        $('#pagination').html('');
        return;
    }

    let html = '';
    managersToRender.forEach((manager, index) => {
        const rowNum = paginationData.from + index;
        
        const tags = manager.tags.map(t => 
            `<span class="badge-tag">${t.tag_name}</span>`
        ).join('');

        html += `
            <tr>
                <td class="ps-3">${rowNum}</td>
                <td>
                    <div class="fw-medium">${manager.name}</div>
                </td>
                <td>${manager.email}</td>
                <td>${tags || '<span class="text-muted">No tags assigned</span>'}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="editManager(${manager.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteManager(${manager.id})" title="Delete">
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
// LOAD MANAGERS DROPDOWN
// =====================================================
function loadManagersDropdown() {
    axios.get(`${API_BASE}/managers-dropdown`)
        .then(response => {
            const select = $('#managerSelect');
            select.html('<option value="">-- Select Manager --</option>');
            response.data.data.forEach(m => {
                select.append(`<option value="${m.id}">${m.name} (${m.email})</option>`);
            });
        });
}

// =====================================================
// LOAD ZOHO TAGS
// =====================================================
function loadZohoTags() {
    axios.get(`${API_BASE}/tags-dropdown`)
        .then(response => {
            zohoTags = response.data.data;
            renderTagsCheckboxes();
        })
        .catch(error => {
            $('#tagsContainer').html('<p class="text-danger mb-0">Failed to load tags</p>');
        });
}

// =====================================================
// RENDER TAGS CHECKBOXES
// =====================================================
function renderTagsCheckboxes(selectedTags = []) {
    const container = $('#tagsContainer');
    
    if (zohoTags.length === 0) {
        container.html('<p class="text-muted mb-0">No tags available</p>');
        return;
    }

    let html = '';
    zohoTags.forEach(tag => {
        const checked = selectedTags.includes(tag.tag_id) ? 'checked' : '';
        html += `
            <div class="form-check mb-2">
                <input class="form-check-input tag-checkbox" type="checkbox" 
                       value="${tag.tag_id}" 
                       data-name="${tag.tag_name}"
                       id="tag_${tag.tag_id}" ${checked}>
                <label class="form-check-label" for="tag_${tag.tag_id}">
                    ${tag.tag_name}
                </label>
            </div>
        `;
    });

    container.html(html);
}

// =====================================================
// OPEN MODAL (New)
// =====================================================
function openModal() {
    $('#managerSelect').val('');
    renderTagsCheckboxes([]);
    assignModal.show();
}

// =====================================================
// EDIT MANAGER
// =====================================================
function editManager(userId) {
    const manager = allManagers.find(m => m.id === userId);
    if (!manager) return;

    $('#managerSelect').val(userId);
    const selectedTags = manager.tags.map(t => t.tag_id);
    renderTagsCheckboxes(selectedTags);
    assignModal.show();
}

// =====================================================
// SAVE TAGS
// =====================================================
function saveTags() {
    const userId = $('#managerSelect').val();
    
    if (!userId) {
        Toast.warning('Please select a manager');
        return;
    }

    // Collect checked tags
    const tags = [];
    $('.tag-checkbox:checked').each(function() {
        tags.push({
            tag_id: $(this).val(),
            tag_name: $(this).data('name')
        });
    });

    if (tags.length === 0) {
        Toast.warning('Please select at least one tag');
        return;
    }

    axios.post(API_BASE, { user_id: userId, tags: tags })
        .then(response => {
            Toast.success('Tags assigned successfully!');
            assignModal.hide();
            loadManagers();
        })
        .catch(error => {
            Toast.error(error.response?.data?.message || 'Failed to save');
        });
}

// =====================================================
// DELETE MANAGER TAGS
// =====================================================
function deleteManager(userId) {
    if (!confirm('Remove all tags from this manager?')) return;

    axios.delete(`${API_BASE}/${userId}`)
        .then(response => {
            Toast.success('Tags removed successfully!');
            loadManagers();
        })
        .catch(error => {
            Toast.error('Failed to remove tags');
        });
}
</script>
@endpush