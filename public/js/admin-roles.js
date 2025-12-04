/**
 * Admin Roles Management
 */

let allPermissions = {};
let allRoles = [];

// Initialize on DOM ready
$(document).ready(function() {
    loadPermissions();
    loadRoles();
});

// Load all permissions
function loadPermissions() {
    axios.get('/api/admin/roles/permissions')
        .then(response => {
            allPermissions = response.data;
            renderPermissionsInModal();
            updateStats();
        })
        .catch(error => {
            console.error('Error loading permissions:', error);
        });
}

// Load all roles
function loadRoles() {
    axios.get('/api/admin/roles')
        .then(response => {
            allRoles = response.data;
            renderRolesGrid();
            updateStats();
        })
        .catch(error => {
            console.error('Error loading roles:', error);
        });
}

// Update stats
function updateStats() {
    $('#totalRoles').text(allRoles.length);
    
    let totalPermissions = 0;
    Object.keys(allPermissions).forEach(group => {
        totalPermissions += allPermissions[group].length;
    });
    $('#totalPermissions').text(totalPermissions);
    
    let totalUsers = 0;
    allRoles.forEach(role => {
        totalUsers += role.users_count || 0;
    });
    $('#totalUsers').text(totalUsers);
}

// Render roles grid
function renderRolesGrid() {
    const container = $('#rolesGrid');
    container.empty();

    if (allRoles.length === 0) {
        container.html(`
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="empty-state">
                        <i class="bi bi-shield-x"></i>
                        <h5>No Roles Found</h5>
                        <p class="text-muted">Create your first role to get started</p>
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="bi bi-plus-lg me-1"></i> Create Role
                        </button>
                    </div>
                </div>
            </div>
        `);
        return;
    }

    allRoles.forEach(role => {
        const permissionBadges = getPermissionBadges(role);
        const isDefault = role.is_default ? `<span class="default-badge"><i class="bi bi-lock me-1"></i>System</span>` : '';
        
        const cardHtml = `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card role-card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center">
                                <div class="role-icon me-3">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">${role.name}</h5>
                                    <span class="users-count">
                                        <i class="bi bi-people me-1"></i>${role.users_count || 0} Users
                                    </span>
                                </div>
                            </div>
                            ${isDefault}
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">${role.description || 'No description'}</p>
                        <div class="mb-3">
                            ${permissionBadges}
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-key me-1"></i>${countPermissions(role)} permissions
                            </small>
                            <div class="role-actions">
                                <button class="btn btn-info" onclick="viewRole(${role.id})" title="View">
                                    <i class="bi bi-eye text-white"></i>
                                </button>
                                <button class="btn btn-warning" onclick="editRole(${role.id})" title="Edit">
                                    <i class="bi bi-pencil text-white"></i>
                                </button>
                                ${!role.is_default ? `
                                    <button class="btn btn-danger" onclick="deleteRole(${role.id})" title="Delete">
                                        <i class="bi bi-trash text-white"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(cardHtml);
    });
}

// Get permission badges HTML
function getPermissionBadges(role) {
    if (!role.permissions || role.permissions.length === 0) {
        return '<span class="text-muted small">No permissions assigned</span>';
    }

    // Show max 5 permissions
    const maxShow = 5;
    let badges = '';
    
    role.permissions.slice(0, maxShow).forEach(perm => {
        const type = getPermissionType(perm.slug);
        badges += `<span class="permission-badge permission-${type}"><i class="bi bi-check-circle-fill"></i>${perm.name}</span>`;
    });

    if (role.permissions.length > maxShow) {
        badges += `<span class="permission-badge"><i class="bi bi-plus-circle"></i>+${role.permissions.length - maxShow} more</span>`;
    }

    return badges;
}

// Get permission type from slug
function getPermissionType(slug) {
    if (slug.includes('view')) return 'view';
    if (slug.includes('create')) return 'create';
    if (slug.includes('edit')) return 'edit';
    if (slug.includes('delete')) return 'delete';
    return 'view';
}

// Count permissions
function countPermissions(role) {
    return role.permissions ? role.permissions.length : 0;
}

// Render permissions in modal
function renderPermissionsInModal() {
    const container = $('#permissionsContainer');
    container.empty();

    const groupIcons = {
        'vendors': 'bi-building',
        'users': 'bi-people',
        'roles': 'bi-shield-check',
        'settings': 'bi-gear'
    };

    Object.keys(allPermissions).forEach(group => {
        const icon = groupIcons[group] || 'bi-key';
        
        let permissionsHtml = '';
        allPermissions[group].forEach(perm => {
            const type = getPermissionType(perm.slug);
            permissionsHtml += `
                <div class="permission-checkbox">
                    <input type="checkbox" class="permission-check" id="perm_${perm.id}" value="${perm.id}">
                    <label for="perm_${perm.id}">${perm.name}</label>
                    <span class="permission-type ${type}">${type}</span>
                </div>
            `;
        });

        const groupHtml = `
            <div class="permission-group">
                <div class="permission-group-title">
                    <i class="bi ${icon}"></i>${group}
                    <span class="ms-auto">
                        <small class="text-white-50">${allPermissions[group].length} permissions</small>
                    </span>
                </div>
                <div class="row">
                    <div class="col-12">
                        ${permissionsHtml}
                    </div>
                </div>
            </div>
        `;
        container.append(groupHtml);
    });
}

// Toggle all permissions
function toggleAllPermissions(checked) {
    $('.permission-check').prop('checked', checked);
}

// Open create modal
function openCreateModal() {
    $('#roleModalTitle').text('Create New Role');
    $('#saveButtonText').text('Create Role');
    $('#roleId').val('');
    $('#roleForm')[0].reset();
    $('.permission-check').prop('checked', false);
    $('#selectAllPermissions').prop('checked', false);
    
    new bootstrap.Modal(document.getElementById('roleModal')).show();
}

// Edit role
function editRole(id) {
    axios.get(`/api/admin/roles/${id}`)
        .then(response => {
            const role = response.data;
            
            $('#roleModalTitle').text('Edit Role');
            $('#saveButtonText').text('Update Role');
            $('#roleId').val(role.id);
            $('#roleName').val(role.name);
            $('#roleDescription').val(role.description);
            
            // Reset all checkboxes
            $('.permission-check').prop('checked', false);
            
            // Check assigned permissions
            if (role.permissions) {
                role.permissions.forEach(perm => {
                    $(`#perm_${perm.id}`).prop('checked', true);
                });
            }
            
            // Update select all checkbox
            updateSelectAllCheckbox();
            
            new bootstrap.Modal(document.getElementById('roleModal')).show();
        })
        .catch(error => {
            alert('Error loading role details');
        });
}

// Update select all checkbox state
function updateSelectAllCheckbox() {
    const total = $('.permission-check').length;
    const checked = $('.permission-check:checked').length;
    $('#selectAllPermissions').prop('checked', total === checked);
}

// Save role
function saveRole() {
    const id = $('#roleId').val();
    const name = $('#roleName').val().trim();
    const description = $('#roleDescription').val().trim();
    
    // Get selected permissions
    const permissions = [];
    $('.permission-check:checked').each(function() {
        permissions.push($(this).val());
    });

    // Validation
    if (!name) {
        alert('Please enter role name');
        return;
    }
    
    if (permissions.length === 0) {
        alert('Please select at least one permission');
        return;
    }

    const data = {
        name: name,
        description: description,
        permissions: permissions
    };

    const url = id ? `/api/admin/roles/${id}` : '/api/admin/roles';
    const method = id ? 'put' : 'post';

    axios[method](url, data)
        .then(response => {
            bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
            loadRoles();
            alert(response.data.message);
        })
        .catch(error => {
            if (error.response && error.response.data) {
                alert(error.response.data.message || 'Error saving role');
            } else {
                alert('Error saving role');
            }
        });
}

// View role
function viewRole(id) {
    axios.get(`/api/admin/roles/${id}`)
        .then(response => {
            const role = response.data;
            
            $('#viewRoleName').text(role.name);
            $('#viewRoleDescription').text(role.description || 'No description');
            $('#viewRoleUsers').html(`<i class="bi bi-people me-1"></i>${role.users_count || 0} Users assigned`);
            
            // Show permissions
            let permissionsHtml = '';
            if (role.permissions && role.permissions.length > 0) {
                role.permissions.forEach(perm => {
                    const type = getPermissionType(perm.slug);
                    permissionsHtml += `<span class="permission-badge permission-${type}"><i class="bi bi-check-circle-fill"></i>${perm.name}</span>`;
                });
            } else {
                permissionsHtml = '<p class="text-muted">No permissions assigned</p>';
            }
            $('#viewRolePermissions').html(permissionsHtml);
            
            new bootstrap.Modal(document.getElementById('viewRoleModal')).show();
        })
        .catch(error => {
            alert('Error loading role details');
        });
}

// Delete role
function deleteRole(id) {
    $('#deleteRoleId').val(id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Confirm delete
function confirmDelete() {
    const id = $('#deleteRoleId').val();
    
    axios.delete(`/api/admin/roles/${id}`)
        .then(response => {
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            loadRoles();
            alert(response.data.message);
        })
        .catch(error => {
            if (error.response && error.response.data) {
                alert(error.response.data.message || 'Error deleting role');
            } else {
                alert('Error deleting role');
            }
        });
}

// Listen for permission checkbox changes
$(document).on('change', '.permission-check', function() {
    updateSelectAllCheckbox();
});