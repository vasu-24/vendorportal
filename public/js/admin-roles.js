/**
 * Admin Roles Management
 * Clean Minimal Design
 */

let allRoles = [];
let allPermissions = {};
let currentPage = 1;
let perPage = 10;
let activeDropdown = null;

// Initialize
$(document).ready(function() {
    loadPermissions();
    loadRoles();
    initSearch();
    initClickOutside();
});

// Load permissions
function loadPermissions() {
    axios.get('/api/admin/roles/permissions')
        .then(response => {
            allPermissions = response.data;
            renderPermissionsInModal();
        })
        .catch(error => {
            console.error('Error loading permissions:', error);
        });
}

// Load roles
function loadRoles() {
    axios.get('/api/admin/roles')
        .then(response => {
            allRoles = response.data;
            $('#totalCount').text(allRoles.length);
            renderTable();
        })
        .catch(error => {
            console.error('Error loading roles:', error);
        });
}

// Render table
function renderTable() {
    let roles = [...allRoles];
    
    // Apply search
    const searchTerm = $('#searchInput').val().toLowerCase();
    if (searchTerm) {
        roles = roles.filter(role => 
            role.name.toLowerCase().includes(searchTerm) ||
            (role.description && role.description.toLowerCase().includes(searchTerm))
        );
    }

    // Pagination
    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const paginatedRoles = roles.slice(start, end);

    let html = '';
    
    if (paginatedRoles.length === 0) {
        html = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">No roles found</td>
            </tr>
        `;
    } else {
        paginatedRoles.forEach(role => {
            const permBadges = getPermissionBadges(role.permissions);
            const typeClass = role.is_default ? 'system' : 'custom';
            const typeText = role.is_default ? 'System' : 'Custom';

            html += `
                <tr>
                    <td>
                        <div class="role-info">
                            <div class="role-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="role-details">
                                <p class="role-name">${role.name}</p>
                                <p class="role-desc">${role.description || 'No description'}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="permission-badges">
                            ${permBadges}
                        </div>
                    </td>
                    <td>
                        <span class="users-count">
                            <i class="bi bi-people"></i> ${role.users_count || 0}
                        </span>
                    </td>
                    <td>
                        <span class="type-badge ${typeClass}">
                            <i class="bi bi-${role.is_default ? 'lock' : 'unlock'}"></i> ${typeText}
                        </span>
                    </td>
                    <td>
                        <div class="action-menu">
                            <button class="action-menu-btn" onclick="toggleActionDropdown(event, ${role.id})">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="action-dropdown" id="action-dropdown-${role.id}">
                                <button type="button" class="action-dropdown-item view" onclick="viewRole(${role.id})">
                                    <i class="bi bi-eye"></i> View details
                                </button>
                                <button type="button" class="action-dropdown-item edit" onclick="editRole(${role.id})">
                                    <i class="bi bi-pencil"></i> Edit role
                                </button>
                                ${!role.is_default ? `
                                    <div class="action-dropdown-divider"></div>
                                    <button type="button" class="action-dropdown-item delete" onclick="deleteRole(${role.id})">
                                        <i class="bi bi-trash"></i> Delete role
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        });
    }

    $('#rolesTableBody').html(html);
    renderPagination(roles.length);
}

// Get permission badges
function getPermissionBadges(permissions) {
    if (!permissions || permissions.length === 0) {
        return '<span class="text-muted" style="font-size: 12px;">No permissions</span>';
    }

    const maxShow = 3;
    let html = '';

    permissions.slice(0, maxShow).forEach(perm => {
        const type = getPermType(perm.slug);
        html += `<span class="perm-badge ${type}">${perm.name}</span>`;
    });

    if (permissions.length > maxShow) {
        html += `<span class="perm-badge more">+${permissions.length - maxShow} more</span>`;
    }

    return html;
}

// Get permission type
function getPermType(slug) {
    if (slug.includes('view')) return 'view';
    if (slug.includes('create')) return 'create';
    if (slug.includes('edit')) return 'edit';
    if (slug.includes('delete')) return 'delete';
    if (slug.includes('approve')) return 'approve';
    if (slug.includes('reject')) return 'reject';
    if (slug.includes('manage')) return 'manage';
    return 'view';
}

// Render pagination
function renderPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / perPage);
    let html = '';

    if (totalPages <= 1) {
        $('#pagination').html('');
        return;
    }

    html += `<button class="pagination-btn ${currentPage === 1 ? 'disabled' : ''}" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
        <i class="bi bi-chevron-left"></i>
    </button>`;

    for (let i = 1; i <= totalPages; i++) {
        if (i <= 5 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        } else if (i === 6 && totalPages > 6) {
            html += `<span class="pagination-btn disabled">...</span>`;
        }
    }

    html += `<button class="pagination-btn ${currentPage === totalPages ? 'disabled' : ''}" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
        <i class="bi bi-chevron-right"></i>
    </button>`;

    $('#pagination').html(html);
}

// Go to page
function goToPage(page) {
    const totalPages = Math.ceil(allRoles.length / perPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable();
}

// Toggle action dropdown
function toggleActionDropdown(event, roleId) {
    event.stopPropagation();
    
    $('.action-dropdown').removeClass('show');
    
    const dropdown = $(`#action-dropdown-${roleId}`);
    const isOpen = dropdown.hasClass('show');
    
    if (!isOpen) {
        dropdown.addClass('show');
        activeDropdown = roleId;
    } else {
        activeDropdown = null;
    }
}

// Close dropdown on click outside
function initClickOutside() {
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.action-menu').length) {
            closeAllDropdowns();
        }
    });
}

// Close all dropdowns
function closeAllDropdowns() {
    $('.action-dropdown').removeClass('show');
    activeDropdown = null;
}

// Initialize search
function initSearch() {
    $('#searchInput').on('input', function() {
        currentPage = 1;
        renderTable();
    });
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
        const perms = allPermissions[group];

        let permsHtml = '';
        perms.forEach(perm => {
            const type = getPermType(perm.slug);
            permsHtml += `
                <div class="permission-item" onclick="togglePermissionCheck(${perm.id})">
                    <input type="checkbox" class="permission-check" id="perm_${perm.id}" value="${perm.id}">
                    <label for="perm_${perm.id}">${perm.name}</label>
                    <span class="perm-type ${type}">${type}</span>
                </div>
            `;
        });

        const groupHtml = `
            <div class="permission-group">
                <div class="permission-group-header">
                    <i class="bi ${icon}"></i>
                    <span>${group}</span>
                    <span class="perm-count">${perms.length} permissions</span>
                </div>
                <div class="permission-group-body">
                    ${permsHtml}
                </div>
            </div>
        `;
        container.append(groupHtml);
    });
}

// Toggle permission checkbox
function togglePermissionCheck(permId) {
    const checkbox = $(`#perm_${permId}`);
    checkbox.prop('checked', !checkbox.prop('checked'));
    updateSelectAllCheckbox();
}

// Toggle all permissions
function toggleAllPermissions(checked) {
    $('.permission-check').prop('checked', checked);
}

// Update select all checkbox
function updateSelectAllCheckbox() {
    const total = $('.permission-check').length;
    const checked = $('.permission-check:checked').length;
    $('#selectAllPermissions').prop('checked', total === checked);
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
    closeAllDropdowns();
    
    axios.get(`/api/admin/roles/${id}`)
        .then(response => {
            const role = response.data;
            
            $('#roleModalTitle').text('Edit Role');
            $('#saveButtonText').text('Update Role');
            $('#roleId').val(role.id);
            $('#roleName').val(role.name);
            $('#roleDescription').val(role.description);
            
            $('.permission-check').prop('checked', false);
            
            if (role.permissions) {
                role.permissions.forEach(perm => {
                    $(`#perm_${perm.id}`).prop('checked', true);
                });
            }
            
            updateSelectAllCheckbox();
            
            new bootstrap.Modal(document.getElementById('roleModal')).show();
        })
        .catch(error => {
            showToast('Error loading role details', false);
        });
}

// Save role
function saveRole() {
    const id = $('#roleId').val();
    const name = $('#roleName').val().trim();
    const description = $('#roleDescription').val().trim();
    
    const permissions = [];
    $('.permission-check:checked').each(function() {
        permissions.push($(this).val());
    });

    if (!name) {
        showToast('Please enter role name', false);
        return;
    }
    
    if (permissions.length === 0) {
        showToast('Please select at least one permission', false);
        return;
    }

    const data = { name, description, permissions };
    const url = id ? `/api/admin/roles/${id}` : '/api/admin/roles';
    const method = id ? 'put' : 'post';

    axios[method](url, data)
        .then(response => {
            bootstrap.Modal.getInstance(document.getElementById('roleModal')).hide();
            loadRoles();
            showToast(id ? 'Role updated successfully' : 'Role created successfully');
        })
        .catch(error => {
            const message = error.response?.data?.message || 'Error saving role';
            showToast(message, false);
        });
}

// View role
function viewRole(id) {
    closeAllDropdowns();
    
    axios.get(`/api/admin/roles/${id}`)
        .then(response => {
            const role = response.data;
            
            $('#viewRoleName').text(role.name);
            $('#viewRoleDesc').text(role.description || 'No description');
            $('#viewUsersCount').text(role.users_count || 0);
            $('#viewPermsCount').text(role.permissions ? role.permissions.length : 0);
            
            let permsHtml = '';
            if (role.permissions && role.permissions.length > 0) {
                role.permissions.forEach(perm => {
                    const type = getPermType(perm.slug);
                    permsHtml += `<span class="view-perm-badge ${type}"><i class="bi bi-check-circle-fill"></i> ${perm.name}</span>`;
                });
            } else {
                permsHtml = '<p class="text-muted">No permissions assigned</p>';
            }
            $('#viewPermissions').html(permsHtml);
            
            new bootstrap.Modal(document.getElementById('viewRoleModal')).show();
        })
        .catch(error => {
            showToast('Error loading role details', false);
        });
}

// Delete role
function deleteRole(id) {
    closeAllDropdowns();
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
            showToast('Role deleted successfully');
        })
        .catch(error => {
            const message = error.response?.data?.message || 'Error deleting role';
            showToast(message, false);
        });
}

// Show toast
function showToast(message, success = true) {
    const toast = $('#toastNotification');
    $('#toastMessage').text(message);
    
    const icon = toast.find('.toast-icon');
    icon.removeClass('bi-check-circle-fill bi-x-circle-fill success error');
    
    if (success) {
        icon.addClass('bi-check-circle-fill success');
    } else {
        icon.addClass('bi-x-circle-fill error');
    }
    
    toast.addClass('show');
    
    setTimeout(() => {
        hideToast();
    }, 4000);
}

// Hide toast
function hideToast() {
    $('#toastNotification').removeClass('show');
}

// Listen for permission checkbox changes
$(document).on('change', '.permission-check', function() {
    updateSelectAllCheckbox();
});
