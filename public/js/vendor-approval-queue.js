/**
 * Vendor Approval Queue Page JavaScript
 * Path: public/js/pages/vendor-approval-queue.js
 */

(function() {
    'use strict';

    // =====================================================
    // VARIABLES
    // =====================================================
    
    let currentStatus = 'pending_approval';
    const apiBaseUrl = '/api/vendor/approval';

    // DOM Elements
    const elements = {
        refreshBtn: document.getElementById('refreshBtn'),
        statusTabs: document.getElementById('statusTabs'),
        loadingSpinner: document.getElementById('loadingSpinner'),
        vendorsTableContainer: document.getElementById('vendorsTableContainer'),
        vendorsTableBody: document.getElementById('vendorsTableBody'),
        emptyState: document.getElementById('emptyState'),
        emptyMessage: document.getElementById('emptyMessage'),
        
        // Statistics
        statPending: document.getElementById('statPending'),
        statApproved: document.getElementById('statApproved'),
        statRejected: document.getElementById('statRejected'),
        statRevision: document.getElementById('statRevision'),
        
        // Badges
        badgePending: document.getElementById('badgePending'),
        badgeApproved: document.getElementById('badgeApproved'),
        badgeRejected: document.getElementById('badgeRejected'),
        badgeRevision: document.getElementById('badgeRevision'),
    };

    // =====================================================
    // INITIALIZATION
    // =====================================================
    
    function init() {
        loadStatistics();
        loadVendors(currentStatus);
        setupEventListeners();
    }

    // =====================================================
    // EVENT LISTENERS
    // =====================================================
    
    function setupEventListeners() {
        // Refresh button
        elements.refreshBtn.addEventListener('click', function() {
            loadStatistics();
            loadVendors(currentStatus);
        });

        // Status tabs
        elements.statusTabs.querySelectorAll('.nav-link').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active tab
                elements.statusTabs.querySelectorAll('.nav-link').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Load vendors for selected status
                currentStatus = this.getAttribute('data-status');
                loadVendors(currentStatus);
            });
        });
    }

    // =====================================================
    // API CALLS
    // =====================================================
    
    async function loadStatistics() {
        try {
            const response = await axios.get(`${apiBaseUrl}/statistics`);
            
            if (response.data.success) {
                const stats = response.data.data;
                updateStatistics(stats);
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    }

    async function loadVendors(status) {
        showLoading();
        
        try {
            const response = await axios.get(`${apiBaseUrl}/status/${status}`);
            
            if (response.data.success) {
                renderVendors(response.data.data);
            } else {
                showEmpty('Failed to load vendors.');
            }
        } catch (error) {
            console.error('Error loading vendors:', error);
            showEmpty('Error loading vendors. Please try again.');
        }
    }

    // =====================================================
    // UI UPDATES
    // =====================================================
    
    function updateStatistics(stats) {
        elements.statPending.textContent = stats.pending_approval || 0;
        elements.statApproved.textContent = stats.approved || 0;
        elements.statRejected.textContent = stats.rejected || 0;
        elements.statRevision.textContent = stats.revision_requested || 0;
        
        // Update badges
        elements.badgePending.textContent = stats.pending_approval || 0;
        elements.badgeApproved.textContent = stats.approved || 0;
        elements.badgeRejected.textContent = stats.rejected || 0;
        elements.badgeRevision.textContent = stats.revision_requested || 0;
    }

    function renderVendors(vendors) {
        hideLoading();
        
        if (vendors.length === 0) {
            showEmpty(getEmptyMessage(currentStatus));
            return;
        }
        
        elements.emptyState.style.display = 'none';
        elements.vendorsTableContainer.style.display = 'block';
        
        elements.vendorsTableBody.innerHTML = vendors.map((vendor, index) => `
            <tr>
                <td class="ps-4 text-muted">${index + 1}</td>
                <td>
                    <div class="fw-medium">${escapeHtml(vendor.vendor_name)}</div>
                    <small class="text-muted">${escapeHtml(vendor.vendor_email)}</small>
                </td>
                <td>
                    <span class="text-muted">${vendor.company_info ? escapeHtml(vendor.company_info.legal_entity_name || '-') : '-'}</span>
                </td>
                <td>
                    <small class="text-muted">${formatDate(vendor.registration_completed_at)}</small>
                </td>
                <td>
                    ${getStatusBadge(vendor.approval_status)}
                </td>
                <td class="pe-4 text-end">
                    <a href="/vendors/approval/review/${vendor.id}" class="btn btn-sm btn-outline-primary">
                        Review
                    </a>
                </td>
            </tr>
        `).join('');
    }

    function showLoading() {
        elements.loadingSpinner.style.display = 'block';
        elements.vendorsTableContainer.style.display = 'none';
        elements.emptyState.style.display = 'none';
    }

    function hideLoading() {
        elements.loadingSpinner.style.display = 'none';
    }

    function showEmpty(message) {
        hideLoading();
        elements.vendorsTableContainer.style.display = 'none';
        elements.emptyState.style.display = 'block';
        elements.emptyMessage.textContent = message;
    }

    // =====================================================
    // HELPER FUNCTIONS
    // =====================================================
    
    function getEmptyMessage(status) {
        const messages = {
            'pending_approval': 'No pending vendors.',
            'approved': 'No approved vendors.',
            'rejected': 'No rejected vendors.',
            'revision_requested': 'No revision requests.'
        };
        return messages[status] || 'No vendors found.';
    }

    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge bg-light text-secondary">Draft</span>',
            'pending_approval': '<span class="badge bg-light text-dark">Pending</span>',
            'approved': '<span class="badge bg-light text-success">Approved</span>',
            'rejected': '<span class="badge bg-light text-danger">Rejected</span>',
            'revision_requested': '<span class="badge bg-light text-primary">Revision</span>'
        };
        return badges[status] || '<span class="badge bg-light text-secondary">Unknown</span>';
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // =====================================================
    // INITIALIZE ON DOM READY
    // =====================================================
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

