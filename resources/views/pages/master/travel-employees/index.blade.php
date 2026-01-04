@extends('layouts.app')

@section('title', 'Travel Employee Master')

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
    .badge-project {
        background: #e7f3ff;
        color: #0d6efd;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }
</style>

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <h2 class="page-title">Travel Employee Master</h2>
                <p class="page-subtitle">Manage employees for travel invoices</p>
            </div>
        </div>
        <button class="btn btn-primary btn-sm" onclick="showAddModal()">
            <i class="bi bi-plus-lg me-1"></i>Add Employee
        </button>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm">
        
        {{-- Search Only --}}
        <div class="card-header bg-white py-2">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="searchInput" 
                               placeholder="Search employees...">
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <small class="text-muted">Total: <strong id="totalCount">0</strong> employees</small>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 index-table">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Employee Name</th>
                        <th>Code</th>
                        <th>Project</th>
                        <th>Manager</th>
                        <th>Designation</th>
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
<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold" id="modalTitle">Add Employee</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="employeeId">
                
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Employee Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="employeeName" placeholder="Enter employee name">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Employee Code</label>
                    <input type="text" class="form-control" id="employeeCode" placeholder="Enter code (optional)">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Project <span class="text-danger">*</span></label>
                    <select class="form-select" id="projectSelect">
                        <option value="">Select Project</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Designation</label>
                        <input type="text" class="form-control" id="designation" placeholder="Designation">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Department</label>
                        <input type="text" class="form-control" id="department" placeholder="Department">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Email">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input type="text" class="form-control" id="phone" placeholder="Phone">
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveBtn" onclick="saveEmployee()">
                    <i class="bi bi-check-lg me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const API_BASE = '/api/admin/travel-employees';
let employees = [];
let projects = [];
let currentPage = 1;

$(document).ready(function() {
    loadProjects();
    loadEmployees();

    // Search debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadEmployees();
        }, 300);
    });
});

// =====================================================
// LOAD PROJECTS (for dropdown)
// =====================================================
function loadProjects() {
    axios.get(`${API_BASE}/projects`)
        .then(res => {
            if (res.data.success) {
                projects = res.data.data;
                const selectHtml = '<option value="">Select Project</option>' + 
                    projects.map(p => `<option value="${p.tag_id}" data-name="${p.tag_name}">${p.tag_name} (${p.manager_name})</option>`).join('');
                $('#projectSelect').html(selectHtml);
            }
        })
        .catch(err => console.error('Failed to load projects'));
}

// =====================================================
// LOAD EMPLOYEES
// =====================================================
function loadEmployees() {
    const tbody = $('#tableBody');
    tbody.html(`
        <tr><td colspan="8" class="text-center py-4">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <span class="ms-2 text-muted">Loading...</span>
        </td></tr>
    `);

    const params = new URLSearchParams({
        page: currentPage,
        per_page: 10,
        search: $('#searchInput').val()
    });

    axios.get(`${API_BASE}?${params}`)
        .then(res => {
            if (res.data.success) {
                employees = res.data.data.data;
                renderTable(res.data.data);
                renderPagination(res.data.data);
                $('#totalCount').text(res.data.data.total || 0);
            }
        })
        .catch(err => {
            tbody.html(`<tr><td colspan="8" class="text-center py-4 text-danger">Failed to load data</td></tr>`);
            Toast.error('Failed to load employees');
        });
}

// =====================================================
// RENDER TABLE
// =====================================================
function renderTable(data) {
    const tbody = $('#tableBody');
    
    if (!employees || employees.length === 0) {
        tbody.html(`
            <tr><td colspan="8" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>No employees found
            </td></tr>
        `);
        return;
    }

    let html = '';
    employees.forEach((emp, idx) => {
        const rowNum = ((data.current_page - 1) * data.per_page) + idx + 1;
        
        html += `
            <tr>
                <td class="ps-3">${rowNum}</td>
                <td>
                    <div class="fw-medium">${emp.employee_name}</div>
                    ${emp.email ? `<small class="text-muted">${emp.email}</small>` : ''}
                </td>
                <td>${emp.employee_code || '-'}</td>
                <td><span class="badge-project">${emp.tag_name || '-'}</span></td>
                <td>${emp.manager_name || '-'}</td>
                <td>${emp.designation || '-'}</td>
                <td>
                    <span class="badge-${emp.is_active ? 'active' : 'inactive'}">
                        ${emp.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="editEmployee(${emp.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-${emp.is_active ? 'warning' : 'success'}" 
                                onclick="toggleStatus(${emp.id})" 
                                title="${emp.is_active ? 'Deactivate' : 'Activate'}">
                            <i class="bi bi-${emp.is_active ? 'pause-circle' : 'check-circle'}"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteEmployee(${emp.id})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.html(html);
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
    loadEmployees();
}

// =====================================================
// MODAL FUNCTIONS
// =====================================================
function showAddModal() {
    $('#modalTitle').text('Add Employee');
    $('#employeeId').val('');
    $('#employeeName').val('');
    $('#employeeCode').val('');
    $('#projectSelect').val('');
    $('#designation').val('');
    $('#department').val('');
    $('#email').val('');
    $('#phone').val('');
    
    new bootstrap.Modal('#employeeModal').show();
}

function editEmployee(id) {
    const emp = employees.find(e => e.id === id);
    if (!emp) return;

    $('#modalTitle').text('Edit Employee');
    $('#employeeId').val(emp.id);
    $('#employeeName').val(emp.employee_name);
    $('#employeeCode').val(emp.employee_code || '');
    $('#projectSelect').val(emp.tag_id || '');
    $('#designation').val(emp.designation || '');
    $('#department').val(emp.department || '');
    $('#email').val(emp.email || '');
    $('#phone').val(emp.phone || '');

    new bootstrap.Modal('#employeeModal').show();
}

function saveEmployee() {
    const id = $('#employeeId').val();
    const name = $('#employeeName').val().trim();
    const projectSelect = document.getElementById('projectSelect');
    const tagId = projectSelect.value;
    const tagName = projectSelect.options[projectSelect.selectedIndex]?.dataset?.name || '';

    if (!name) {
        Toast.warning('Employee name is required');
        return;
    }
    if (!tagId) {
        Toast.warning('Please select a project');
        return;
    }

    const data = {
        employee_name: name,
        employee_code: $('#employeeCode').val().trim(),
        tag_id: tagId,
        tag_name: tagName,
        designation: $('#designation').val().trim(),
        department: $('#department').val().trim(),
        email: $('#email').val().trim(),
        phone: $('#phone').val().trim()
    };

    const btn = $('#saveBtn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

    const request = id 
        ? axios.put(`${API_BASE}/${id}`, data)
        : axios.post(API_BASE, data);

    request
        .then(res => {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('employeeModal')).hide();
                Toast.success(id ? 'Employee updated!' : 'Employee created!');
                loadEmployees();
            }
        })
        .catch(err => {
            Toast.error(err.response?.data?.message || 'Failed to save');
        })
        .finally(() => {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Save');
        });
}

// =====================================================
// OTHER ACTIONS
// =====================================================
function toggleStatus(id) {
    const emp = employees.find(e => e.id === id);
    if (!emp) return;

    const action = emp.is_active ? 'deactivate' : 'activate';
    if (!confirm(`Are you sure you want to ${action} this employee?`)) return;

    axios.post(`${API_BASE}/${id}/toggle-status`)
        .then(res => {
            if (res.data.success) {
                Toast.success(res.data.message);
                loadEmployees();
            }
        })
        .catch(err => Toast.error('Failed to update status'));
}

function deleteEmployee(id) {
    if (!confirm('Are you sure you want to delete this employee?')) return;

    axios.delete(`${API_BASE}/${id}`)
        .then(res => {
            if (res.data.success) {
                Toast.success('Employee deleted!');
                loadEmployees();
            }
        })
        .catch(err => Toast.error(err.response?.data?.message || 'Failed to delete'));
}
</script>
@endpush