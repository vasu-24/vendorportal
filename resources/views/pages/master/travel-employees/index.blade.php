@extends('layouts.app')

@section('title', 'Travel Employee Master')

@section('content')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .stats-row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    .stat-card {
        flex: 1;
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .stat-card .number {
        font-size: 28px;
        font-weight: 700;
        color: #333;
    }
    .stat-card .label {
        font-size: 13px;
        color: #666;
    }
    .stat-card.active { border-left: 4px solid #28a745; }
    .stat-card.inactive { border-left: 4px solid #dc3545; }
    .stat-card.total { border-left: 4px solid #007bff; }

    .filter-section {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .filter-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }
    .filter-row .form-group {
        flex: 1;
        min-width: 180px;
    }

    .table-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .table {
        margin-bottom: 0;
    }
    .table th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 13px;
        padding: 12px;
        border-bottom: 2px solid #dee2e6;
    }
    .table td {
        padding: 12px;
        vertical-align: middle;
        font-size: 14px;
    }
    .table tr:hover {
        background: #f8f9fa;
    }

    .badge-active {
        background: #28a745;
        color: #fff;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
    }
    .badge-inactive {
        background: #dc3545;
        color: #fff;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
    }
    .badge-project {
        background: #e7f3ff;
        color: #0d6efd;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
    .action-btns {
        display: flex;
        gap: 5px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    .empty-state i {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 15px;
    }

    .pagination-container {
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #dee2e6;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h4 class="mb-1">Travel Employee Master</h4>
            <p class="text-muted mb-0">Manage employees for travel invoices</p>
        </div>
        <button class="btn btn-primary" onclick="showAddModal()">
            <i class="fas fa-plus me-1"></i> Add Employee
        </button>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card total">
            <div class="number" id="statTotal">0</div>
            <div class="label">Total Employees</div>
        </div>
        <div class="stat-card active">
            <div class="number" id="statActive">0</div>
            <div class="label">Active</div>
        </div>
        <div class="stat-card inactive">
            <div class="number" id="statInactive">0</div>
            <div class="label">Inactive</div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-row">
            <div class="form-group">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Name, code, email...">
            </div>
            <div class="form-group">
                <label class="form-label">Project</label>
                <select class="form-select" id="filterProject">
                    <option value="">All Projects</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select class="form-select" id="filterStatus">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="form-group d-flex align-items-end">
                <button class="btn btn-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <table class="table" id="employeesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee Name</th>
                    <th>Code</th>
                    <th>Project</th>
                    <th>Manager</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div class="mt-2">Loading...</div>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="pagination-container">
            <div id="paginationInfo">Showing 0 of 0</div>
            <nav>
                <ul class="pagination mb-0" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="employeeId">
                
                <div class="mb-3">
                    <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="employeeName" placeholder="Enter employee name">
                </div>

                <div class="mb-3">
                    <label class="form-label">Employee Code</label>
                    <input type="text" class="form-control" id="employeeCode" placeholder="Enter code (optional)">
                </div>

                <div class="mb-3">
                    <label class="form-label">Project <span class="text-danger">*</span></label>
                    <select class="form-select" id="projectSelect">
                        <option value="">Select Project</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Designation</label>
                        <input type="text" class="form-control" id="designation" placeholder="Designation">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" id="department" placeholder="Department">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" placeholder="Email">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" placeholder="Phone">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBtn" onclick="saveEmployee()">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="toast" class="toast align-items-center text-white border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script>
const API_BASE = '/api/admin/travel-employees';
let employees = [];
let projects = [];
let currentPage = 1;
let lastPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    loadProjects();
    loadStatistics();
    loadEmployees();

    // Search debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadEmployees(), 500);
    });

    document.getElementById('filterProject').addEventListener('change', () => loadEmployees());
    document.getElementById('filterStatus').addEventListener('change', () => loadEmployees());
});

function loadProjects() {
    axios.get(`${API_BASE}/projects`)
        .then(res => {
            if (res.data.success) {
                projects = res.data.data;
                renderProjectDropdowns();
            }
        })
        .catch(err => console.error('Failed to load projects'));
}

function renderProjectDropdowns() {
    const filterHtml = '<option value="">All Projects</option>' + 
        projects.map(p => `<option value="${p.tag_id}">${p.tag_name}</option>`).join('');
    
    const selectHtml = '<option value="">Select Project</option>' + 
        projects.map(p => `<option value="${p.tag_id}" data-name="${p.tag_name}">${p.tag_name} (${p.manager_name})</option>`).join('');
    
    document.getElementById('filterProject').innerHTML = filterHtml;
    document.getElementById('projectSelect').innerHTML = selectHtml;
}

function loadStatistics() {
    axios.get(`${API_BASE}/statistics`)
        .then(res => {
            if (res.data.success) {
                const stats = res.data.data;
                document.getElementById('statTotal').textContent = stats.total || 0;
                document.getElementById('statActive').textContent = stats.active || 0;
                document.getElementById('statInactive').textContent = stats.inactive || 0;
            }
        })
        .catch(err => console.error('Failed to load statistics'));
}

function loadEmployees(page = 1) {
    currentPage = page;
    
    const params = new URLSearchParams({
        page: page,
        per_page: 15,
        search: document.getElementById('searchInput').value,
        tag_id: document.getElementById('filterProject').value,
        status: document.getElementById('filterStatus').value
    });

    document.getElementById('tableBody').innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
            </td>
        </tr>
    `;

    axios.get(`${API_BASE}?${params}`)
        .then(res => {
            if (res.data.success) {
                employees = res.data.data.data;
                lastPage = res.data.data.last_page;
                renderTable();
                renderPagination(res.data.data);
            }
        })
        .catch(err => {
            console.error('Failed to load employees');
            document.getElementById('tableBody').innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-danger">Failed to load data</td>
                </tr>
            `;
        });
}

function renderTable() {
    if (employees.length === 0) {
        document.getElementById('tableBody').innerHTML = `
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <div>No employees found</div>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    const html = employees.map((emp, idx) => `
        <tr>
            <td>${(currentPage - 1) * 15 + idx + 1}</td>
            <td>
                <strong>${emp.employee_name}</strong>
                ${emp.email ? `<div class="text-muted small">${emp.email}</div>` : ''}
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
            <td>
                <div class="action-btns">
                    <button class="btn btn-sm btn-outline-primary" onclick="editEmployee(${emp.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-${emp.is_active ? 'warning' : 'success'}" 
                            onclick="toggleStatus(${emp.id})" 
                            title="${emp.is_active ? 'Deactivate' : 'Activate'}">
                        <i class="fas fa-${emp.is_active ? 'ban' : 'check'}"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteEmployee(${emp.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

    document.getElementById('tableBody').innerHTML = html;
}

function renderPagination(data) {
    const info = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total || 0}`;
    document.getElementById('paginationInfo').textContent = info;

    let paginationHtml = '';
    
    // Previous
    paginationHtml += `
        <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadEmployees(${data.current_page - 1})">«</a>
        </li>
    `;

    // Pages
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            paginationHtml += `
                <li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadEmployees(${i})">${i}</a>
                </li>
            `;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            paginationHtml += `<li class="page-item disabled"><a class="page-link">...</a></li>`;
        }
    }

    // Next
    paginationHtml += `
        <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadEmployees(${data.current_page + 1})">»</a>
        </li>
    `;

    document.getElementById('pagination').innerHTML = paginationHtml;
}

function showAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Employee';
    document.getElementById('employeeId').value = '';
    document.getElementById('employeeName').value = '';
    document.getElementById('employeeCode').value = '';
    document.getElementById('projectSelect').value = '';
    document.getElementById('designation').value = '';
    document.getElementById('department').value = '';
    document.getElementById('email').value = '';
    document.getElementById('phone').value = '';
    
    new bootstrap.Modal(document.getElementById('employeeModal')).show();
}

function editEmployee(id) {
    const emp = employees.find(e => e.id === id);
    if (!emp) return;

    document.getElementById('modalTitle').textContent = 'Edit Employee';
    document.getElementById('employeeId').value = emp.id;
    document.getElementById('employeeName').value = emp.employee_name;
    document.getElementById('employeeCode').value = emp.employee_code || '';
    document.getElementById('projectSelect').value = emp.tag_id || '';
    document.getElementById('designation').value = emp.designation || '';
    document.getElementById('department').value = emp.department || '';
    document.getElementById('email').value = emp.email || '';
    document.getElementById('phone').value = emp.phone || '';

    new bootstrap.Modal(document.getElementById('employeeModal')).show();
}

function saveEmployee() {
    const id = document.getElementById('employeeId').value;
    const name = document.getElementById('employeeName').value.trim();
    const projectSelect = document.getElementById('projectSelect');
    const tagId = projectSelect.value;
    const tagName = projectSelect.options[projectSelect.selectedIndex]?.dataset?.name || '';

    if (!name) {
        showToast('error', 'Employee name is required');
        return;
    }
    if (!tagId) {
        showToast('error', 'Please select a project');
        return;
    }

    const data = {
        employee_name: name,
        employee_code: document.getElementById('employeeCode').value.trim(),
        tag_id: tagId,
        tag_name: tagName,
        designation: document.getElementById('designation').value.trim(),
        department: document.getElementById('department').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.trim()
    };

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    const request = id 
        ? axios.put(`${API_BASE}/${id}`, data)
        : axios.post(API_BASE, data);

    request
        .then(res => {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('employeeModal')).hide();
                showToast('success', id ? 'Employee updated' : 'Employee created');
                loadStatistics();
                loadEmployees(currentPage);
            }
        })
        .catch(err => {
            showToast('error', err.response?.data?.message || 'Failed to save');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save';
        });
}

function toggleStatus(id) {
    const emp = employees.find(e => e.id === id);
    if (!emp) return;

    const action = emp.is_active ? 'deactivate' : 'activate';
    if (!confirm(`Are you sure you want to ${action} this employee?`)) return;

    axios.post(`${API_BASE}/${id}/toggle-status`)
        .then(res => {
            if (res.data.success) {
                showToast('success', res.data.message);
                loadStatistics();
                loadEmployees(currentPage);
            }
        })
        .catch(err => showToast('error', 'Failed to update status'));
}

function deleteEmployee(id) {
    if (!confirm('Are you sure you want to delete this employee?')) return;

    axios.delete(`${API_BASE}/${id}`)
        .then(res => {
            if (res.data.success) {
                showToast('success', 'Employee deleted');
                loadStatistics();
                loadEmployees(currentPage);
            }
        })
        .catch(err => showToast('error', err.response?.data?.message || 'Failed to delete'));
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterProject').value = '';
    document.getElementById('filterStatus').value = 'all';
    loadEmployees();
}

function showToast(type, message) {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    
    toast.classList.remove('bg-success', 'bg-danger', 'bg-warning');
    toast.classList.add(type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-warning');
    toastMessage.textContent = message;
    
    new bootstrap.Toast(toast).show();
}
</script>
@endsection