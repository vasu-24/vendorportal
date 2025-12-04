// =====================================================
// WIZARD JAVASCRIPT - WITH PRE-FILL SUPPORT
// =====================================================

// CSRF Token Setup
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Wizard Variables
let currentStep = 1;
const totalSteps = 4;
const vendorToken = window.vendorToken || document.querySelector('input[name="vendor_token"]')?.value;

// API Endpoints
const apiEndpoints = {
    1: `/api/vendor/registration/step1/${vendorToken}`,
    2: `/api/vendor/registration/step2/${vendorToken}`,
    3: `/api/vendor/registration/step3/${vendorToken}`,
    4: `/api/vendor/registration/step4/${vendorToken}`
};

// DOM Elements
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const submitBtn = document.getElementById('submitBtn');
const alertContainer = document.getElementById('alertContainer');

// =====================================================
// ALERT FUNCTIONS
// =====================================================
function showAlert(message, type = 'danger') {
    alertContainer.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// =====================================================
// 🔥 PRE-FILL FORM DATA (FOR REJECTED VENDORS)
// =====================================================
function prefillFormData() {
    if (typeof window.existingData === 'undefined' || !window.existingData) {
        console.log('No existing data to prefill');
        return;
    }

    const data = window.existingData;
    console.log('Pre-filling form with existing data:', data);

    // ========== STEP 1: Company Info ==========
    if (data.companyInfo) {
        setFieldValue('legal_entity_name', data.companyInfo.legal_entity_name);
        setFieldValue('business_type', data.companyInfo.business_type);
        setFieldValue('incorporation_date', data.companyInfo.incorporation_date);
        setFieldValue('registered_address', data.companyInfo.registered_address);
        setFieldValue('corporate_address', data.companyInfo.corporate_address);
        setFieldValue('website', data.companyInfo.website);
        setFieldValue('parent_company', data.companyInfo.parent_company);
    }

    // ========== STEP 1: Contact Info ==========
    if (data.contact) {
        setFieldValue('contact_person', data.contact.contact_person);
        setFieldValue('designation', data.contact.designation);
        setFieldValue('mobile', data.contact.mobile);
        setFieldValue('contact_email', data.contact.email); // Note: field name is contact_email
    }

    // ========== STEP 2: Statutory Info ==========
    if (data.statutoryInfo) {
        setFieldValue('pan_number', data.statutoryInfo.pan_number);
        setFieldValue('tan_number', data.statutoryInfo.tan_number);
        setFieldValue('gstin', data.statutoryInfo.gstin);
        setFieldValue('cin', data.statutoryInfo.cin);
        setFieldValue('msme_registered', data.statutoryInfo.msme_registered);
        
        // Show Udyam upload if MSME is Yes
        if (data.statutoryInfo.msme_registered === 'Yes') {
            const udyamBox = document.getElementById('udyamUploadBox');
            if (udyamBox) udyamBox.style.display = 'block';
        }
    }

    // ========== STEP 2: Bank Details ==========
    if (data.bankDetails) {
        setFieldValue('bank_name', data.bankDetails.bank_name);
        setFieldValue('branch_address', data.bankDetails.branch_address);
        setFieldValue('account_holder_name', data.bankDetails.account_holder_name);
        setFieldValue('account_number', data.bankDetails.account_number);
        setFieldValue('ifsc_code', data.bankDetails.ifsc_code);
        setFieldValue('account_type', data.bankDetails.account_type);
    }

    // ========== STEP 3: Tax Info ==========
    if (data.taxInfo) {
        setFieldValue('tax_residency', data.taxInfo.tax_residency);
        setFieldValue('gst_reverse_charge', data.taxInfo.gst_reverse_charge);
        setFieldValue('sez_status', data.taxInfo.sez_status);
    }

    // ========== STEP 3: Business Profile ==========
    if (data.businessProfile) {
        setFieldValue('core_activities', data.businessProfile.core_activities);
        setFieldValue('employee_count', data.businessProfile.employee_count);
        setFieldValue('credit_period', data.businessProfile.credit_period);
        setFieldValue('turnover_fy1', data.businessProfile.turnover_fy1);
        setFieldValue('turnover_fy2', data.businessProfile.turnover_fy2);
        setFieldValue('turnover_fy3', data.businessProfile.turnover_fy3);
    }

    console.log('Form pre-filled successfully!');
}

// =====================================================
// 🔥 HELPER: SET FIELD VALUE
// =====================================================
function setFieldValue(fieldName, value) {
    if (!value) return;

    const field = document.querySelector(`[name="${fieldName}"]`);
    if (!field) {
        console.log(`Field not found: ${fieldName}`);
        return;
    }

    if (field.tagName === 'SELECT') {
        field.value = value;
    } else if (field.type === 'checkbox') {
        field.checked = value === '1' || value === 1 || value === true;
    } else if (field.type === 'radio') {
        const radio = document.querySelector(`[name="${fieldName}"][value="${value}"]`);
        if (radio) radio.checked = true;
    } else {
        field.value = value;
    }
}

// =====================================================
// UPDATE STEP INDICATOR
// =====================================================
function updateStepIndicator() {
    document.querySelectorAll('.wizard-step').forEach((step, index) => {
        const stepNum = index + 1;
        step.classList.remove('active', 'completed');
        
        if (stepNum < currentStep) {
            step.classList.add('completed');
        } else if (stepNum === currentStep) {
            step.classList.add('active');
        }
    });
}

// =====================================================
// SHOW STEP CONTENT
// =====================================================
function showStep(step) {
    document.querySelectorAll('.step-content').forEach(content => {
        content.classList.remove('active');
    });
    document.querySelector(`.step-content[data-step="${step}"]`).classList.add('active');
    
    prevBtn.style.display = step === 1 ? 'none' : 'inline-flex';
    nextBtn.style.display = step === totalSteps ? 'none' : 'inline-flex';
    submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';
    
    updateStepIndicator();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// =====================================================
// GET FORM DATA FROM CURRENT STEP
// =====================================================
function getStepFormData(step) {
    const formData = new FormData();
    const stepContent = document.querySelector(`.step-content[data-step="${step}"]`);
    
    stepContent.querySelectorAll('input, select, textarea').forEach(input => {
        if (input.type === 'file') {
            if (input.files.length > 0) {
                formData.append(input.name, input.files[0]);
            }
        } else if (input.type === 'checkbox') {
            formData.append(input.name, input.checked ? '1' : '0');
        } else if (input.type === 'radio') {
            if (input.checked) {
                formData.append(input.name, input.value);
            }
        } else {
            formData.append(input.name, input.value);
        }
    });
    
    return formData;
}

// =====================================================
// SAVE STEP DATA VIA API
// =====================================================
async function saveStepData(step) {
    try {
        const formData = getStepFormData(step);
        
        const response = await axios.post(apiEndpoints[step], formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        if (response.data.success) {
            return true;
        } else {
            showAlert(response.data.message || 'Something went wrong.');
            return false;
        }
        
    } catch (error) {
        if (error.response && error.response.data) {
            if (error.response.data.errors) {
                const errors = Object.values(error.response.data.errors).flat();
                showAlert(errors.join('<br>'));
            } else {
                showAlert(error.response.data.message || 'Something went wrong.');
            }
        } else {
            showAlert('Network error. Please try again.');
        }
        
        return false;
    }
}

// =====================================================
// BUTTON EVENT LISTENERS
// =====================================================

// Next Button
nextBtn.addEventListener('click', async function() {
    const saved = await saveStepData(currentStep);
    
    if (saved) {
        currentStep++;
        showStep(currentStep);
        showAlert('Step saved successfully!', 'success');
    }
});

// Previous Button
prevBtn.addEventListener('click', function() {
    currentStep--;
    showStep(currentStep);
});

// Submit Button
submitBtn.addEventListener('click', async function() {
    const saved = await saveStepData(currentStep);
    
    if (saved) {
        window.location.href = `/vendor/registration/success/${vendorToken}`;
    }
});

// =====================================================
// FILE UPLOAD PREVIEW
// =====================================================
document.querySelectorAll('.file-upload-input').forEach(input => {
    input.addEventListener('change', function() {
        const box = this.closest('.file-upload-box');
        const label = box?.querySelector('.file-upload-label');
        
        if (this.files.length > 0 && box && label) {
            box.classList.add('has-file');
            label.innerHTML = `<i class="bi bi-check-circle text-success me-1"></i>${this.files[0].name}`;
        }
    });
});

// =====================================================
// REMOVE INVALID CLASS ON INPUT
// =====================================================
document.querySelectorAll('input, select, textarea').forEach(input => {
    input.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
});

// =====================================================
// 🔥 INITIALIZE
// =====================================================
document.addEventListener('DOMContentLoaded', function() {
    showStep(1);
    
    // 🔥 PRE-FILL FORM IF EXISTING DATA EXISTS
    prefillFormData();
});