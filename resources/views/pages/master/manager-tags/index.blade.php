@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Manager Master</h4>
            <p class="text-muted mb-0 small">Assign tags to managers for approval routing</p>
        </div>
        <button class="btn btn-primary btn-sm" onclick="openModal()">
            <i class="bi bi-plus-lg me-1"></i> Assign Tags
        </button>
    </div>

    <!-- Manager List -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Manager Name</th>
                            <th>Email</th>
                            <th>Assigned Tags</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="managerTableBody">
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary"></div>
                                <span class="ms-2">Loading...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Assign Tags Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Tags to Manager</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="assignForm">
                    <!-- Manager Select -->
                    <div class="mb-3">
                        <label class="form-label">Manager <span class="text-danger">*</span></label>
                        <select class="form-select" id="managerSelect" required>
                            <option value="">-- Select Manager --</option>
                        </select>
                    </div>

                    <!-- Tags Checkboxes -->
                    <div class="mb-3">
                        <label class="form-label">Assign Tags <span class="text-danger">*</span></label>
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;" id="tagsContainer">
                            <div class="text-center text-muted">
                                <div class="spinner-border spinner-border-sm"></div>
                                <span class="ms-2">Loading tags...</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveTags()">
                    <i class="bi bi-check-lg me-1"></i> Save
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
let zohoTags = [];
let assignModal;

$(document).ready(function() {
    assignModal = new bootstrap.Modal(document.getElementById('assignModal'));
    loadManagers();
    loadZohoTags();
    loadManagersDropdown();
});

// =====================================================
// LOAD MANAGERS LIST
// =====================================================
function loadManagers() {
    axios.get(API_BASE)
        .then(response => {
            managers = response.data.data;
            renderTable();
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.error('Failed to load managers');
        });
}

// =====================================================
// RENDER TABLE
// =====================================================
function renderTable() {
    const tbody = $('#managerTableBody');
    
    if (managers.length === 0) {
        tbody.html(`
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                    No managers found. Click "Assign Tags" to add.
                </td>
            </tr>
        `);
        return;
    }

    let html = '';
    managers.forEach((manager, index) => {
        const tags = manager.tags.map(t => 
            `<span class="badge bg-primary me-1 mb-1">${t.tag_name}</span>`
        ).join('');

        html += `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${manager.name}</strong></td>
                <td>${manager.email}</td>
                <td>${tags || '<span class="text-muted">No tags assigned</span>'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editManager(${manager.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteManager(${manager.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.html(html);
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
    const manager = managers.find(m => m.id === userId);
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
        Toast.error('Please select a manager');
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
        Toast.error('Please select at least one tag');
        return;
    }

    axios.post(API_BASE, { user_id: userId, tags: tags })
        .then(response => {
            Toast.success('Tags assigned successfully');
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
            Toast.success('Tags removed successfully');
            loadManagers();
        })
        .catch(error => {
            Toast.error('Failed to remove tags');
        });
}
</script>
@endpush