(function() {
    'use strict';

    // =====================================================
    // VARIABLES
    // =====================================================
    
    const apiBaseUrl = '/api/vendor/approval';
    let vendorData = null;
    let originalData = {};

    // =====================================================
    // INITIALIZATION
    // =====================================================
    
    function init() {
        loadVendorDetails();
    }

    // =====================================================
    // API CALLS - LOAD DATA
    // =====================================================
    
    async function loadVendorDetails() {
        try {
            const response = await axios.get(`${apiBaseUrl}/${vendorId}/details`);

            if (response.data.success) {
                vendorData = response.data.data;
                populateAllSections();
                updateActionButtons();
            } else {
                showAlert('danger', 'Failed to load vendor details.');
            }
        } catch (error) {
            console.error('Error loading vendor details:', error);
            showAlert('danger', 'Error loading vendor details. Please try again.');
        }
    }

    // =====================================================
    // POPULATE SECTIONS
    // =====================================================
    
    function populateAllSections() {
        populateVendorHeader();
        populateCompanySection();
        populateContactSection();
        populateStatutorySection();
        populateBankSection();
        populateTaxSection();
        populateBusinessSection();
        populateDocumentsSection();
        populateHistorySection();
    }

    function populateVendorHeader() {
        document.getElementById('vendorName').textContent = vendorData.vendor_name || 'N/A';
        document.getElementById('vendorEmail').textContent = vendorData.vendor_email || 'N/A';
        document.getElementById('submittedDate').textContent = formatDate(vendorData.registration_completed_at);
        
        const statusBadge = document.getElementById('vendorStatusBadge');
        const statusInfo = getStatusInfo(vendorData.approval_status);
        statusBadge.className = `badge ${statusInfo.class} px-3 py-2`;
        statusBadge.innerHTML = `<i class="bi ${statusInfo.icon} me-1"></i>${statusInfo.label}`;
    }

    function populateCompanySection() {
        const data = vendorData.company_info || {};
        originalData.company = { ...data };

        document.getElementById('view_legal_entity_name').textContent = data.legal_entity_name || '-';
        document.getElementById('view_business_type').textContent = data.business_type || '-';
        document.getElementById('view_incorporation_date').textContent = formatDate(data.incorporation_date);
        document.getElementById('view_website').textContent = data.website || '-';
        document.getElementById('view_registered_address').textContent = data.registered_address || '-';
        document.getElementById('view_corporate_address').textContent = data.corporate_address || '-';
        document.getElementById('view_parent_company').textContent = data.parent_company || '-';

        document.getElementById('edit_legal_entity_name').value = data.legal_entity_name || '';
        document.getElementById('edit_business_type').value = data.business_type || '';
        document.getElementById('edit_incorporation_date').value = data.incorporation_date || '';
        document.getElementById('edit_website').value = data.website || '';
        document.getElementById('edit_registered_address').value = data.registered_address || '';
        document.getElementById('edit_corporate_address').value = data.corporate_address || '';
        document.getElementById('edit_parent_company').value = data.parent_company || '';
    }

    function populateContactSection() {
        const data = vendorData.contact || {};
        originalData.contact = { ...data };

        document.getElementById('view_contact_person').textContent = data.contact_person || '-';
        document.getElementById('view_designation').textContent = data.designation || '-';
        document.getElementById('view_mobile').textContent = data.mobile ? `+91 ${data.mobile}` : '-';
        document.getElementById('view_contact_email').textContent = data.email || '-';
        document.getElementById('view_alternate_mobile').textContent = data.alternate_mobile || '-';
        document.getElementById('view_landline').textContent = data.landline || '-';

        document.getElementById('edit_contact_person').value = data.contact_person || '';
        document.getElementById('edit_designation').value = data.designation || '';
        document.getElementById('edit_mobile').value = data.mobile || '';
        document.getElementById('edit_contact_email').value = data.email || '';
        document.getElementById('edit_alternate_mobile').value = data.alternate_mobile || '';
        document.getElementById('edit_landline').value = data.landline || '';
    }

    function populateStatutorySection() {
        const data = vendorData.statutory_info || {};
        originalData.statutory = { ...data };

        document.getElementById('view_pan_number').textContent = data.pan_number || '-';
        document.getElementById('view_tan_number').textContent = data.tan_number || '-';
        document.getElementById('view_gstin').textContent = data.gstin || '-';
        document.getElementById('view_cin').textContent = data.cin || '-';
        document.getElementById('view_msme_registered').textContent = data.msme_registered || '-';

        document.getElementById('edit_pan_number').value = data.pan_number || '';
        document.getElementById('edit_tan_number').value = data.tan_number || '';
        document.getElementById('edit_gstin').value = data.gstin || '';
        document.getElementById('edit_cin').value = data.cin || '';
        document.getElementById('edit_msme_registered').value = data.msme_registered || '';
    }

    function populateBankSection() {
        const data = vendorData.bank_details || {};
        originalData.bank = { ...data };

        document.getElementById('view_bank_name').textContent = data.bank_name || '-';
        document.getElementById('view_branch_address').textContent = data.branch_address || '-';
        document.getElementById('view_account_holder_name').textContent = data.account_holder_name || '-';
        document.getElementById('view_account_number').textContent = data.account_number || '-';
        document.getElementById('view_ifsc_code').textContent = data.ifsc_code || '-';
        document.getElementById('view_account_type').textContent = data.account_type || '-';

        document.getElementById('edit_bank_name').value = data.bank_name || '';
        document.getElementById('edit_branch_address').value = data.branch_address || '';
        document.getElementById('edit_account_holder_name').value = data.account_holder_name || '';
        document.getElementById('edit_account_number').value = data.account_number || '';
        document.getElementById('edit_ifsc_code').value = data.ifsc_code || '';
        document.getElementById('edit_account_type').value = data.account_type || '';
    }

    function populateTaxSection() {
        const data = vendorData.tax_info || {};
        originalData.tax = { ...data };

        document.getElementById('view_tax_residency').textContent = data.tax_residency || '-';
        document.getElementById('view_gst_reverse_charge').textContent = data.gst_reverse_charge || '-';
        document.getElementById('view_sez_status').textContent = data.sez_status || '-';

        document.getElementById('edit_tax_residency').value = data.tax_residency || '';
        document.getElementById('edit_gst_reverse_charge').value = data.gst_reverse_charge || '';
        document.getElementById('edit_sez_status').value = data.sez_status || '';
    }

    function populateBusinessSection() {
        const data = vendorData.business_profile || {};
        originalData.business = { ...data };

        document.getElementById('view_core_activities').textContent = data.core_activities || '-';
        document.getElementById('view_employee_count').textContent = data.employee_count || '-';
        document.getElementById('view_credit_period').textContent = data.credit_period || '-';
        document.getElementById('view_turnover_fy1').textContent = data.turnover_fy1 || '-';
        document.getElementById('view_turnover_fy2').textContent = data.turnover_fy2 || '-';
        document.getElementById('view_turnover_fy3').textContent = data.turnover_fy3 || '-';

        document.getElementById('edit_core_activities').value = data.core_activities || '';
        document.getElementById('edit_employee_count').value = data.employee_count || '';
        document.getElementById('edit_credit_period').value = data.credit_period || '';
        document.getElementById('edit_turnover_fy1').value = data.turnover_fy1 || '';
        document.getElementById('edit_turnover_fy2').value = data.turnover_fy2 || '';
        document.getElementById('edit_turnover_fy3').value = data.turnover_fy3 || '';
    }

    function populateDocumentsSection() {
        const documents = vendorData.documents || [];
        const container = document.getElementById('documentsContainer');

        if (documents.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-folder-x fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No documents uploaded</p>
                </div>
            `;
            return;
        }

        let html = '<div class="row g-3">';
        
        documents.forEach(doc => {
            const fileIcon = getFileIcon(doc.original_name || doc.document_path);
            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bi ${fileIcon} fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">${escapeHtml(doc.document_type)}</h6>
                                    <small class="text-muted">${escapeHtml(doc.original_name || 'Document')}</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button class="btn btn-sm btn-outline-primary w-100" onclick="viewDocument('${doc.document_path}', '${escapeHtml(doc.document_type)}')">
                                <i class="bi bi-eye me-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    function populateHistorySection() {
        const history = vendorData.approval_history || [];
        const container = document.getElementById('historyContainer');

        if (history.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-clock-history fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No history available</p>
                </div>
            `;
            return;
        }

        let html = '<div class="timeline">';
        
        history.forEach((item, index) => {
            const actionInfo = getActionInfo(item.action);
            html += `
                <div class="timeline-item mb-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-2 ${actionInfo.bgClass}">
                                <i class="bi ${actionInfo.icon} ${actionInfo.textClass}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${actionInfo.label}</h6>
                                    <small class="text-muted">
                                        <i class="bi bi-person me-1"></i>${escapeHtml(item.action_by_name || 'System')}
                                    </small>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>${formatDateTime(item.created_at)}
                                </small>
                            </div>
                            ${item.notes ? `<p class="mt-2 mb-0 text-muted small">${escapeHtml(item.notes)}</p>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    }

    // =====================================================
    // EDIT MODE FUNCTIONS
    // =====================================================
    
    window.toggleEdit = function(section) {
        const viewMode = document.getElementById(`${section}ViewMode`);
        const editMode = document.getElementById(`${section}EditMode`);
        const saveBtn = document.getElementById(`${section}SaveBtn`);
        const cancelBtn = document.getElementById(`${section}CancelBtn`);
        const editBtnText = document.getElementById(`${section}EditBtnText`);

        if (viewMode.classList.contains('d-none')) {
            viewMode.classList.remove('d-none');
            editMode.classList.add('d-none');
            saveBtn.classList.add('d-none');
            cancelBtn.classList.add('d-none');
            editBtnText.textContent = 'Edit';
        } else {
            viewMode.classList.add('d-none');
            editMode.classList.remove('d-none');
            saveBtn.classList.remove('d-none');
            cancelBtn.classList.remove('d-none');
            editBtnText.textContent = 'Cancel';
        }
    };

    window.cancelEdit = function(section) {
        resetSectionData(section);
        toggleEdit(section);
    };

    function resetSectionData(section) {
        switch(section) {
            case 'company': populateCompanySection(); break;
            case 'contact': populateContactSection(); break;
            case 'statutory': populateStatutorySection(); break;
            case 'bank': populateBankSection(); break;
            case 'tax': populateTaxSection(); break;
            case 'business': populateBusinessSection(); break;
        }
    }

    // =====================================================
    // SAVE SECTION FUNCTIONS
    // =====================================================
    
    window.saveSection = async function(section) {
        const data = getSectionData(section);
        const endpoint = getSectionEndpoint(section);

        try {
            const response = await axios.put(`${apiBaseUrl}/${vendorId}/${endpoint}`, data);

            if (response.data.success) {
                showAlert('success', response.data.message || 'Data saved successfully!');
                await loadVendorDetails();
                
                const viewMode = document.getElementById(`${section}ViewMode`);
                const editMode = document.getElementById(`${section}EditMode`);
                const saveBtn = document.getElementById(`${section}SaveBtn`);
                const cancelBtn = document.getElementById(`${section}CancelBtn`);
                const editBtnText = document.getElementById(`${section}EditBtnText`);

                viewMode.classList.remove('d-none');
                editMode.classList.add('d-none');
                saveBtn.classList.add('d-none');
                cancelBtn.classList.add('d-none');
                editBtnText.textContent = 'Edit';
            } else {
                showAlert('danger', response.data.message || 'Failed to save data.');
            }
        } catch (error) {
            console.error('Error saving section:', error);
            const message = error.response?.data?.message || 'Error saving data. Please try again.';
            showAlert('danger', message);
        }
    };

    function getSectionData(section) {
        switch(section) {
            case 'company':
                return {
                    legal_entity_name: document.getElementById('edit_legal_entity_name').value,
                    business_type: document.getElementById('edit_business_type').value,
                    incorporation_date: document.getElementById('edit_incorporation_date').value,
                    website: document.getElementById('edit_website').value,
                    registered_address: document.getElementById('edit_registered_address').value,
                    corporate_address: document.getElementById('edit_corporate_address').value,
                    parent_company: document.getElementById('edit_parent_company').value
                };
            case 'contact':
                return {
                    contact_person: document.getElementById('edit_contact_person').value,
                    designation: document.getElementById('edit_designation').value,
                    mobile: document.getElementById('edit_mobile').value,
                    email: document.getElementById('edit_contact_email').value,
                    alternate_mobile: document.getElementById('edit_alternate_mobile').value,
                    landline: document.getElementById('edit_landline').value
                };
            case 'statutory':
                return {
                    pan_number: document.getElementById('edit_pan_number').value,
                    tan_number: document.getElementById('edit_tan_number').value,
                    gstin: document.getElementById('edit_gstin').value,
                    cin: document.getElementById('edit_cin').value,
                    msme_registered: document.getElementById('edit_msme_registered').value
                };
            case 'bank':
                return {
                    bank_name: document.getElementById('edit_bank_name').value,
                    branch_address: document.getElementById('edit_branch_address').value,
                    account_holder_name: document.getElementById('edit_account_holder_name').value,
                    account_number: document.getElementById('edit_account_number').value,
                    ifsc_code: document.getElementById('edit_ifsc_code').value,
                    account_type: document.getElementById('edit_account_type').value
                };
            case 'tax':
                return {
                    tax_residency: document.getElementById('edit_tax_residency').value,
                    gst_reverse_charge: document.getElementById('edit_gst_reverse_charge').value,
                    sez_status: document.getElementById('edit_sez_status').value
                };
            case 'business':
                return {
                    core_activities: document.getElementById('edit_core_activities').value,
                    employee_count: document.getElementById('edit_employee_count').value,
                    credit_period: document.getElementById('edit_credit_period').value,
                    turnover_fy1: document.getElementById('edit_turnover_fy1').value,
                    turnover_fy2: document.getElementById('edit_turnover_fy2').value,
                    turnover_fy3: document.getElementById('edit_turnover_fy3').value
                };
            default:
                return {};
        }
    }

    function getSectionEndpoint(section) {
        const endpoints = {
            'company': 'company-info',
            'contact': 'contact',
            'statutory': 'statutory-info',
            'bank': 'bank-details',
            'tax': 'tax-info',
            'business': 'business-profile'
        };
        return endpoints[section] || section;
    }

    // =====================================================
    // APPROVAL ACTIONS
    // =====================================================
    
    window.approveVendor = function() {
        const modal = new bootstrap.Modal(document.getElementById('approveModal'));
        modal.show();
    };

    window.confirmApproval = async function() {
        const notes = document.getElementById('approvalNotes').value;

        try {
            const response = await axios.post(`${apiBaseUrl}/${vendorId}/approve`, { notes });

            if (response.data.success) {
                showAlert('success', 'Vendor approved successfully!');
                bootstrap.Modal.getInstance(document.getElementById('approveModal')).hide();
                await loadVendorDetails();
            } else {
                showAlert('danger', response.data.message || 'Failed to approve vendor.');
            }
        } catch (error) {
            console.error('Error approving vendor:', error);
            showAlert('danger', 'Error approving vendor. Please try again.');
        }
    };

    // =====================================================
    // 🔥 CONFIRM REJECT FUNCTION (FIXED)
    // =====================================================
    
    window.confirmReject = async function() {
        const reason = document.getElementById('rejectReason').value.trim();
        const reasonInput = document.getElementById('rejectReason');

        // Validation
        if (!reason) {
            reasonInput.classList.add('is-invalid');
            return;
        }
        reasonInput.classList.remove('is-invalid');

        try {
            const response = await axios.post(`${apiBaseUrl}/${vendorId}/reject`, { rejection_reason: reason });

            if (response.data.success) {
                // Show success with email status
                const emailMsg = response.data.email_sent 
                    ? 'Vendor rejected successfully! Email sent to vendor.' 
                    : 'Vendor rejected successfully!';
                showAlert('success', emailMsg);
                
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                
                // Clear the textarea
                reasonInput.value = '';
                
                // Reload vendor data
                await loadVendorDetails();
            } else {
                showAlert('danger', response.data.message || 'Failed to reject vendor.');
            }
        } catch (error) {
            console.error('Error rejecting vendor:', error);
            showAlert('danger', 'Error rejecting vendor. Please try again.');
        }
    };

    window.requestRevision = async function() {
        const notes = document.getElementById('revisionNotes').value.trim();
        const notesInput = document.getElementById('revisionNotes');

        if (!notes) {
            notesInput.classList.add('is-invalid');
            return;
        }
        notesInput.classList.remove('is-invalid');

        try {
            const response = await axios.post(`${apiBaseUrl}/${vendorId}/request-revision`, { revision_notes: notes });

            if (response.data.success) {
                showAlert('success', 'Revision request sent successfully!');
                bootstrap.Modal.getInstance(document.getElementById('revisionModal')).hide();
                await loadVendorDetails();
            } else {
                showAlert('danger', response.data.message || 'Failed to request revision.');
            }
        } catch (error) {
            console.error('Error requesting revision:', error);
            showAlert('danger', 'Error requesting revision. Please try again.');
        }
    };

    // =====================================================
    // DOCUMENT PREVIEW
    // =====================================================
    
    window.viewDocument = function(path, title) {
        const modal = new bootstrap.Modal(document.getElementById('documentModal'));
        const container = document.getElementById('documentPreviewContainer');
        const downloadBtn = document.getElementById('downloadDocBtn');
        const modalTitle = document.getElementById('documentModalLabel');

        modalTitle.innerHTML = `<i class="bi bi-file-earmark me-2"></i>${escapeHtml(title)}`;
        
        const fileUrl = `/storage/${path}`;
        downloadBtn.href = fileUrl;

        const ext = path.split('.').pop().toLowerCase();

        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
            container.innerHTML = `<img src="${fileUrl}" class="img-fluid rounded" alt="${title}">`;
        } else if (ext === 'pdf') {
            container.innerHTML = `<iframe src="${fileUrl}" width="100%" height="500px" frameborder="0"></iframe>`;
        } else {
            container.innerHTML = `
                <div class="py-5">
                    <i class="bi bi-file-earmark fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">Preview not available. Click download to view the file.</p>
                </div>
            `;
        }

        modal.show();
    };

    // =====================================================
    // UI HELPERS
    // =====================================================
    
    function updateActionButtons() {
        const actionCard = document.getElementById('actionButtonsCard');

        if (vendorData.approval_status === 'approved') {
            actionCard.innerHTML = `
                <div class="card-body text-center">
                    <div class="text-success">
                        <i class="bi bi-check-circle fs-1"></i>
                        <h5 class="mt-2">Vendor Approved</h5>
                        <p class="text-muted mb-0">This vendor has been approved on ${formatDateTime(vendorData.approved_at)}</p>
                    </div>
                </div>
            `;
        } else if (vendorData.approval_status === 'rejected') {
            actionCard.innerHTML = `
                <div class="card-body text-center">
                    <div class="text-danger">
                        <i class="bi bi-x-circle fs-1"></i>
                        <h5 class="mt-2">Vendor Rejected</h5>
                        <p class="text-muted mb-0">This vendor was rejected on ${formatDateTime(vendorData.rejected_at)}</p>
                        ${vendorData.rejection_reason ? `<p class="mt-2"><strong>Reason:</strong> ${escapeHtml(vendorData.rejection_reason)}</p>` : ''}
                    </div>
                </div>
            `;
        }
    }

    function showAlert(type, message) {
        const container = document.getElementById('alertContainer');
        const alertId = 'alert-' + Date.now();

        let bgColor, borderColor, textColor, icon;
        
        if (type === 'success') {
            bgColor = '#d1fae5';
            borderColor = '#10b981';
            textColor = '#065f46';
            icon = 'bi-check-circle-fill';
        } else if (type === 'danger' || type === 'error') {
            bgColor = '#fce7f3';
            borderColor = '#fb7185';
            textColor = '#9f1239';
            icon = 'bi-exclamation-circle-fill';
        } else if (type === 'warning') {
            bgColor = '#fef3c7';
            borderColor = '#f59e0b';
            textColor = '#92400e';
            icon = 'bi-exclamation-triangle-fill';
        } else {
            bgColor = '#dbeafe';
            borderColor = '#3b82f6';
            textColor = '#1e40af';
            icon = 'bi-info-circle-fill';
        }

        const alertHtml = `
            <div class="alert alert-dismissible fade show" 
                 role="alert" 
                 id="${alertId}"
                 style="background-color: ${bgColor}; 
                        border-left: 4px solid ${borderColor}; 
                        color: ${textColor};
                        border-radius: 8px;
                        padding: 16px 20px;
                        margin-bottom: 20px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <i class="bi ${icon} me-2" style="color: ${borderColor};"></i>
                <strong>${escapeHtml(message)}</strong>
                <button type="button" 
                        class="btn-close" 
                        data-bs-dismiss="alert" 
                        aria-label="Close"
                        style="filter: brightness(0.8);"></button>
            </div>
        `;

        container.innerHTML = alertHtml;

        setTimeout(() => {
            const alertElement = document.getElementById(alertId);
            if (alertElement) {
                const bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            }
        }, 5000);
    }

    // =====================================================
    // UTILITY FUNCTIONS
    // =====================================================
    
    function getStatusInfo(status) {
        const statuses = {
            'draft': { label: 'Draft', icon: 'bi-pencil', class: 'bg-secondary' },
            'pending_approval': { label: 'Pending Approval', icon: 'bi-clock', class: 'bg-warning text-dark' },
            'approved': { label: 'Approved', icon: 'bi-check-circle', class: 'bg-success' },
            'rejected': { label: 'Rejected', icon: 'bi-x-circle', class: 'bg-danger' },
            'revision_requested': { label: 'Revision Requested', icon: 'bi-arrow-repeat', class: 'bg-info' }
        };
        return statuses[status] || { label: 'Unknown', icon: 'bi-question-circle', class: 'bg-secondary' };
    }

    function getActionInfo(action) {
        const actions = {
            'submitted': { label: 'Submitted', icon: 'bi-upload', bgClass: 'bg-primary bg-opacity-10', textClass: 'text-primary' },
            'pending_approval': { label: 'Pending Approval', icon: 'bi-clock', bgClass: 'bg-warning bg-opacity-10', textClass: 'text-warning' },
            'approved': { label: 'Approved', icon: 'bi-check-circle', bgClass: 'bg-success bg-opacity-10', textClass: 'text-success' },
            'rejected': { label: 'Rejected', icon: 'bi-x-circle', bgClass: 'bg-danger bg-opacity-10', textClass: 'text-danger' },
            'revision_requested': { label: 'Revision Requested', icon: 'bi-arrow-repeat', bgClass: 'bg-info bg-opacity-10', textClass: 'text-info' },
            'resubmitted': { label: 'Resubmitted', icon: 'bi-arrow-up-circle', bgClass: 'bg-primary bg-opacity-10', textClass: 'text-primary' },
            'data_updated': { label: 'Data Updated', icon: 'bi-pencil', bgClass: 'bg-secondary bg-opacity-10', textClass: 'text-secondary' }
        };
        return actions[action] || { label: action, icon: 'bi-circle', bgClass: 'bg-secondary bg-opacity-10', textClass: 'text-secondary' };
    }

    function getFileIcon(filename) {
        if (!filename) return 'bi-file-earmark';
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            'pdf': 'bi-file-earmark-pdf',
            'doc': 'bi-file-earmark-word',
            'docx': 'bi-file-earmark-word',
            'jpg': 'bi-file-earmark-image',
            'jpeg': 'bi-file-earmark-image',
            'png': 'bi-file-earmark-image'
        };
        return icons[ext] || 'bi-file-earmark';
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatDateTime(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // =====================================================
    // INITIALIZE
    // =====================================================
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

