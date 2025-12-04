/**
 * Admin Users Management
 * Clean Minimal Design
 */

let allUsers = [];
let roles = [];
let currentPage = 1;
let perPage = 10;
let activeDropdown = null;
let activeFilter = 'all';

// Initialize
$(document).ready(function() {
    loadRoles();
    loadUsers();
    initSearch();
    initFilter();
    initClickOutside();
});

// Load roles for dropdown
function loadRoles() {
    axios.get('/api/admin/users/roles')
        .then(response => {
            roles = response.data;
            let options = '<option value="">Select Role</option>';
            roles.forEach(role => {
                options += `<option value="${role.id}">${role.name}</option>`;
            });
            $('#userRole').html(options);
        })
        .catch(error => {
            console.error('Error loading roles:', error);
        });
}

// Load users
function loadUsers() {
    axios.get('/api/admin/users')
        .then(response => {
            allUsers = response.data;
            $('#totalCount').text(allUsers.length);
            renderTable();
        })
        .catch(error => {
            console.error('Error loading users:', error);
        });
}

// Render table
function renderTable() {
    let users = [...allUsers];
    
    // Apply filter
    if (activeFilter === 'active') {
        users = users.filter(u => u.status === 'active');
    } else if (activeFilter === 'inactive') {
        users = users.filter(u => u.status === 'inactive');
    }
    
    // Apply search
    const searchTerm = $('#searchInput').val().toLowerCase();
    if (searchTerm) {
        users = users.filter(user => 
            user.name.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm) ||
            (user.role && user.role.name.toLowerCase().includes(searchTerm))
        );
    }

    // Pagination
    const start = (currentPage - 1) * perPage;
    const end = start + perPage;
    const paginatedUsers = users.slice(start, end);

    let html = '';
    
    if (paginatedUsers.length === 0) {
        html = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">No users found</td>
            </tr>
        `;
    } else {
        paginatedUsers.forEach(user => {
            const initials = getInitials(user.name);
            const roleClass = getRoleClass(user.role?.name);
            const statusClass = user.status === 'active' ? 'active' : 'inactive';
            const lastActive = formatDate(user.updated_at);
            const dateAdded = formatDate(user.created_at);

            html += `
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">${initials}</div>
                            <div class="user-details">
                                <p class="user-name">${user.name}</p>
                                <p class="user-email">${user.email}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="role-badges">
                            ${user.role ? `<span class="role-badge ${roleClass}">${user.role.name}</span>` : '<span class="role-badge default">No Role</span>'}
                        </div>
                    </td>
                    <td>
                        <span class="status-badge ${statusClass}">${user.status === 'active' ? 'Active' : 'Inactive'}</span>
                    </td>
                    <td>
                        <span class="date-text">${lastActive}</span>
                    </td>
                    <td>
                        <span class="date-text">${dateAdded}</span>
                    </td>
                    <td>
                        <div class="action-menu">
                            <button class="action-menu-btn" onclick="toggleActionDropdown(event, ${user.id})">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="action-dropdown" id="action-dropdown-${user.id}">
                                <button type="button" class="action-dropdown-item view" onclick="viewUser(${user.id})">
                                    <i class="bi bi-eye"></i> View details
                                </button>
                                <button type="button" class="action-dropdown-item edit" onclick="editUser(${user.id})">
                                    <i class="bi bi-pencil"></i> Edit user
                                </button>
                                <div class="action-dropdown-divider"></div>
                                <button type="button" class="action-dropdown-item ${user.status === 'active' ? 'deactivate' : 'status'}" onclick="toggleStatus(${user.id}, '${user.status}')">
                                    <i class="bi bi-${user.status === 'active' ? 'x-circle' : 'check-circle'}"></i> ${user.status === 'active' ? 'Deactivate' : 'Activate'}
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        });
    }

    $('#usersTableBody').html(html);
    renderPagination(users.length);
    
    // Update count based on filter
    if (activeFilter === 'all') {
        $('#totalCount').text(allUsers.length);
    } else {
        $('#totalCount').text(users.length);
    }
}

// Render pagination
function renderPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / perPage);
    let html = '';

    if (totalPages <= 1) {
        $('#pagination').html('');
        return;
    }

    // Previous button
    html += `<button class="pagination-btn ${currentPage === 1 ? 'disabled' : ''}" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
        <i class="bi bi-chevron-left"></i>
    </button>`;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i <= 5 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
        } else if (i === 6 && totalPages > 6) {
            html += `<span class="pagination-btn disabled">...</span>`;
        }
    }

    // Next button
    html += `<button class="pagination-btn ${currentPage === totalPages ? 'disabled' : ''}" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
        <i class="bi bi-chevron-right"></i>
    </button>`;

    $('#pagination').html(html);
}

// Go to page
function goToPage(page) {
    const totalPages = Math.ceil(allUsers.length / perPage);
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    renderTable();
}

// Toggle action dropdown (3-dot menu)
function toggleActionDropdown(event, userId) {
    event.stopPropagation();
    
    // Close filter dropdown if open
    $('#filterDropdown').removeClass('show');
    $('#filterBtn').removeClass('active');
    
    // Close any open action dropdown
    $('.action-dropdown').removeClass('show');
    
    const dropdown = $(`#action-dropdown-${userId}`);
    const isOpen = dropdown.hasClass('show');
    
    if (!isOpen) {
        dropdown.addClass('show');
        activeDropdown = userId;
    } else {
        activeDropdown = null;
    }
}

// Initialize filter
function initFilter() {
    $('#filterBtn').on('click', function(e) {
        e.stopPropagation();
        
        // Close action dropdowns
        closeAllActionDropdowns();
        
        const dropdown = $('#filterDropdown');
        dropdown.toggleClass('show');
        $(this).toggleClass('active', dropdown.hasClass('show'));
    });

    // Filter item click
    $('.filter-dropdown-item').on('click', function() {
        const filter = $(this).data('filter');
        activeFilter = filter;
        currentPage = 1;
        
        // Update active state
        $('.filter-dropdown-item').removeClass('active');
        $(this).addClass('active');
        
        // Update button text
        let filterText = 'Filters';
        if (filter === 'active') filterText = 'Active';
        else if (filter === 'inactive') filterText = 'Inactive';
        
        if (filter !== 'all') {
            $('#filterBtn').html(`<i class="bi bi-funnel-fill"></i> ${filterText}`).addClass('active');
        } else {
            $('#filterBtn').html(`<i class="bi bi-funnel"></i> Filters`).removeClass('active');
        }
        
        // Close dropdown
        $('#filterDropdown').removeClass('show');
        
        renderTable();
    });
}

// Close dropdown on click outside
function initClickOutside() {
    $(document).on('click', function(e) {
        // Close action dropdowns
        if (!$(e.target).closest('.action-menu').length) {
            closeAllActionDropdowns();
        }
        
        // Close filter dropdown
        if (!$(e.target).closest('.filter-dropdown-container').length) {
            $('#filterDropdown').removeClass('show');
            if (activeFilter === 'all') {
                $('#filterBtn').removeClass('active');
            }
        }
    });
}

// Close all action dropdowns
function closeAllActionDropdowns() {
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

// Get initials
function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
}

// Get role class
function getRoleClass(roleName) {
    if (!roleName) return 'default';
    const name = roleName.toLowerCase();
    if (name.includes('super') || name.includes('admin')) return 'admin';
    if (name.includes('manager')) return 'manager';
    return 'viewer';
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
}

// Open Create Modal
function openCreateModal() {
    $('#userModalTitle').text('Add New User');
    $('#saveButtonText').text('Save User');
    $('#userId').val('');
    $('#userForm')[0].reset();
    $('#passwordRequired').show();
    $('#passwordHint').hide();
    $('#userPassword').prop('required', true);
}

// Edit User
function editUser(id) {
    closeAllActionDropdowns();
    
    axios.get(`/api/admin/users/${id}`)
        .then(response => {
            const user = response.data;
            $('#userModalTitle').text('Edit User');
            $('#saveButtonText').text('Update User');
            $('#userId').val(user.id);
            $('#userName').val(user.name);
            $('#userEmail').val(user.email);
            $('#userPassword').val('');
            $('#userRole').val(user.role_id);
            $('#userStatus').val(user.status);
            $('#passwordRequired').hide();
            $('#passwordHint').show();
            $('#userPassword').prop('required', false);
            
            new bootstrap.Modal(document.getElementById('userModal')).show();
        })
        .catch(error => {
            showToast('Error loading user details', false);
        });
}

// Save User
function saveUser() {
    const id = $('#userId').val();
    const data = {
        name: $('#userName').val(),
        email: $('#userEmail').val(),
        role_id: $('#userRole').val(),
        status: $('#userStatus').val()
    };

    const password = $('#userPassword').val();
    if (password) {
        data.password = password;
    }

    if (!data.name || !data.email || !data.role_id) {
        showToast('Please fill all required fields', false);
        return;
    }

    if (!id && !password) {
        showToast('Password is required for new user', false);
        return;
    }

    const url = id ? `/api/admin/users/${id}` : '/api/admin/users';
    const method = id ? 'put' : 'post';

    axios[method](url, data)
        .then(response => {
            bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
            loadUsers();
            showToast(id ? 'User updated successfully' : 'User created successfully');
        })
        .catch(error => {
            const message = error.response?.data?.message || 'Error saving user';
            showToast(message, false);
        });
}

// View User
function viewUser(id) {
    closeAllActionDropdowns();
    
    axios.get(`/api/admin/users/${id}`)
        .then(response => {
            const user = response.data;
            $('#viewName').text(user.name);
            $('#viewEmail').text(user.email);
            
            const roleClass = getRoleClass(user.role?.name);
            $('#viewRole').html(user.role ? 
                `<span class="role-badge ${roleClass}">${user.role.name}</span>` : 
                '<span class="role-badge default">No Role</span>'
            );
            
            $('#viewStatus').html(`<span class="status-badge ${user.status}">${user.status === 'active' ? 'Active' : 'Inactive'}</span>`);
            $('#viewCreatedAt').text(formatDate(user.created_at));
            
            new bootstrap.Modal(document.getElementById('viewUserModal')).show();
        })
        .catch(error => {
            showToast('Error loading user details', false);
        });
}

// Toggle Status
function toggleStatus(id, currentStatus) {
    closeAllActionDropdowns();
    
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    
    axios.put(`/api/admin/users/${id}`, { status: newStatus })
        .then(response => {
            loadUsers();
            showToast(`User ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully`);
        })
        .catch(error => {
            showToast('Error updating status', false);
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