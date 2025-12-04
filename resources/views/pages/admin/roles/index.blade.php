@extends('layouts.app')

@section('title', 'Roles & Permissions - Vendor Portal')

@push('head')
<style>
    /* Role Cards */
    .role-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .role-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .role-card .card-header {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #1a3a5c 100%);
        color: white;
        padding: 20px;
        border: none;
    }
    .role-card .role-icon {
        width: 50px;
        height: 50px;
        background: rgba(255,255,255,0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .role-card .card-body {
        padding: 20px;
    }
    .role-card .users-count {
        background: rgba(255,255,255,0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
    }

    /* Permission Badges */
    .permission-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        background: #f0f7ff;
        border: 1px solid #d0e3ff;
        border-radius: 20px;
        font-size: 12px;
        color: var(--primary-blue);
        margin: 3px;
        transition: all 0.2s;
    }
    .permission-badge i {
        margin-right: 5px;
        font-size: 10px;
    }
    .permission-badge.permission-view { background: #e8f5e9; border-color: #a5d6a7; color: #2e7d32; }
    .permission-badge.permission-create { background: #e3f2fd; border-color: #90caf9; color: #1565c0; }
    .permission-badge.permission-edit { background: #fff3e0; border-color: #ffcc80; color: #ef6c00; }
    .permission-badge.permission-delete { background: #ffebee; border-color: #ef9a9a; color: #c62828; }

    /* Stats Cards */
    .stats-card {
        border-radius: 12px;
        padding: 20px;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .stats-card::after {
        content: '';
        position: absolute;
        top: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .stats-card .stats-icon {
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .stats-card h2 {
        font-size: 28px;
        font-weight: 700;
        margin: 10px 0 5px;
    }
    .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .bg-gradient-info { background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%); }

    /* Action Buttons */
    .role-actions .btn {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-left: 5px;
    }

    /* Permission Groups in Modal */
    .permission-group {
        background: #f8fafc;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .permission-group-title {
        font-weight: 600;
        color: var(--primary-blue);
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--primary-blue);
        display: flex;
        align-items: center;
        text-transform: uppercase;
        font-size: 13px;
    }
    .permission-group-title i {
        margin-right: 8px;
        font-size: 16px;
    }
    .permission-checkbox {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        background: white;
        border-radius: 8px;
        margin-bottom: 8px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
    }
    .permission-checkbox:hover {
        border-color: var(--primary-blue);
        background: #f0f7ff;
    }
    .permission-checkbox input {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        accent-color: var(--primary-blue);
    }
    .permission-checkbox label {
        margin: 0;
        cursor: pointer;
        flex: 1;
    }
    .permission-checkbox .permission-type {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 500;
    }
    .permission-type.view { background: #e8f5e9; color: #2e7d32; }
    .permission-type.create { background: #e3f2fd; color: #1565c0; }
    .permission-type.edit { background: #fff3e0; color: #ef6c00; }
    .permission-type.delete { background: #ffebee; color: #c62828; }
    .permission-type.manage { background: #f3e5f5; color: #7b1fa2; }
    .permission-type.approve { background: #e0f2f1; color: #00695c; }
    .permission-type.reject { background: #fce4ec; color: #c2185b; }

    /* Default Role Badge */
    .default-badge {
        background: rgba(255,255,255,0.2);
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 10px;
        font-weight: 500;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-state i {
        font-size: 60px;
        color: #cbd5e1;
        margin-bottom: 20px;
    }

    /* Select All */
    .select-all-wrapper {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #1a3a5c 100%);
        color: white;
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 20px;
    }
    .select-all-wrapper .form-check-input {
        width: 20px;
        height: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">Roles & Permissions</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Roles</li>
            </ol>
        </nav>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4" id="statsRow">
        <div class="col-md-4 mb-3">
            <div class="stats-card bg-gradient-primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 id="totalRoles">0</h2>
                        <p class="mb-0 opacity-75">Total Roles</p>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card bg-gradient-success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 id="totalPermissions">0</h2>
                        <p class="mb-0 opacity-75">Total Permissions</p>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-key"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card bg-gradient-info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 id="totalUsers">0</h2>
                        <p class="mb-0 opacity-75">Total Users</p>
                    </div>
                    <div class="stats-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0" style="color: var(--primary-blue); font-weight: 600;">
                        <i class="bi bi-shield-lock me-2"></i>Manage Roles
                    </h5>
                </div>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <i class="bi bi-plus-lg me-1"></i> Create New Role
                </button>
            </div>
        </div>
    </div>

    <!-- Roles Grid -->
    <div class="row" id="rolesGrid">
        <!-- Roles loaded via API -->
    </div>

</div>

<!-- Role Modal (Create/Edit) -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-blue) 0%, #1a3a5c 100%);">
                <h5 class="modal-title text-white">
                    <i class="bi bi-shield-plus me-2"></i><span id="roleModalTitle">Create New Role</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm">
                    <input type="hidden" id="roleId" value="">
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-lg" id="roleName" placeholder="e.g. Manager" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Description</label>
                            <input type="text" class="form-control form-control-lg" id="roleDescription" placeholder="Brief description">
                        </div>
                    </div>

                    <!-- Select All -->
                    <div class="select-all-wrapper">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="selectAllPermissions" onchange="toggleAllPermissions(this.checked)">
                            <label class="form-check-label fw-medium" for="selectAllPermissions">
                                Select All Permissions
                            </label>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div id="permissionsContainer">
                        <!-- Permissions loaded via API -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" onclick="saveRole()">
                    <i class="bi bi-check-lg me-1"></i> <span id="saveButtonText">Create Role</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Role Modal -->
<div class="modal fade" id="viewRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, var(--primary-blue) 0%, #1a3a5c 100%);">
                <h5 class="modal-title text-white">
                    <i class="bi bi-shield-check me-2"></i>Role Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-blue) 0%, #1a3a5c 100%); border-radius: 20px;">
                        <i class="bi bi-shield-check text-white" style="font-size: 36px;"></i>
                    </div>
                    <h4 class="mt-3 mb-1" id="viewRoleName"></h4>
                    <p class="text-muted" id="viewRoleDescription"></p>
                    <span class="badge bg-info" id="viewRoleUsers"></span>
                </div>
                
                <h6 class="fw-bold mb-3" style="color: var(--primary-blue);">
                    <i class="bi bi-key me-2"></i>Assigned Permissions
                </h6>
                <div id="viewRolePermissions" class="text-center">
                    <!-- Permissions loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Delete Role</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-trash text-danger" style="font-size: 48px;"></i>
                <p class="mt-3 mb-0">Are you sure you want to delete this role?</p>
                <small class="text-muted">This action cannot be undone.</small>
                <input type="hidden" id="deleteRoleId">
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger px-4" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-roles.js') }}"></script>
@endpush