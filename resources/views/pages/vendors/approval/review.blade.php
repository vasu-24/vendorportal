@extends('layouts.app')
@section('title', 'Vendor Review')





@section('content')
<div class="container-fluid">

    <!-- Loading Overlay -->
    <div class="position-fixed top-0 start-0 w-100 h-100 d-none" id="pageLoader" style="background: rgba(255,255,255,0.8); z-index: 9999;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vendors.approval.queue') }}">Approval Queue</a></li>
                    <li class="breadcrumb-item active">Review</li>
                </ol>
            </nav>
            <h2 class="fw-bold" style="color: var(--primary-blue);">
                <i class="bi bi-file-earmark-text me-2"></i>Vendor Review
            </h2>
            <p class="text-muted mb-0">Review vendor details and take action</p>
        </div>
        <div class="col-auto d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark px-3 py-2" id="vendorStatusBadge">
                <i class="bi bi-clock me-1"></i>Loading...
            </span>
            <a href="{{ route('vendors.approval.queue') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <!-- =====================================================
         VENDOR INFO CARD
         ===================================================== -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-1" id="vendorName">Loading...</h4>
                    <p class="text-muted mb-0" id="vendorEmail">Loading...</p>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>Submitted: <span id="submittedDate">-</span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================
         ALERT CONTAINER
         ===================================================== -->
    <div id="alertContainer"></div>

    <!-- =====================================================
         COLLAPSIBLE SECTIONS
         ===================================================== -->
    <div class="accordion" id="vendorAccordion">

        <!-- ===========================================
             SECTION 1: COMPANY INFORMATION
             =========================================== -->
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sectionCompany">
                    <i class="bi bi-building me-2"></i>
                    <strong>Company Information</strong>
                </button>
            </h2>
            <div id="sectionCompany" class="accordion-collapse collapse show" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    
                    <!-- Section Actions -->
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="toggleEdit('company')">
                            <i class="bi bi-pencil me-1"></i><span id="companyEditBtnText">Edit</span>
                        </button>
                        <button class="btn btn-sm btn-success d-none" id="companySaveBtn" onclick="saveSection('company')">
                            <i class="bi bi-check-lg me-1"></i>Save
                        </button>
                        <button class="btn btn-sm btn-secondary d-none ms-2" id="companyCancelBtn" onclick="cancelEdit('company')">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </button>
                    </div>

                    <!-- View Mode -->
                    <div id="companyViewMode">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Legal Entity Name</label>
                                <p class="fw-medium mb-0" id="view_legal_entity_name">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Business Type</label>
                                <p class="fw-medium mb-0" id="view_business_type">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Date of Incorporation</label>
                                <p class="fw-medium mb-0" id="view_incorporation_date">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Website</label>
                                <p class="fw-medium mb-0" id="view_website">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Registered Address</label>
                                <p class="fw-medium mb-0" id="view_registered_address">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Corporate Address</label>
                                <p class="fw-medium mb-0" id="view_corporate_address">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Parent Company</label>
                                <p class="fw-medium mb-0" id="view_parent_company">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="companyEditMode" class="d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Legal Entity Name</label>
                                <input type="text" class="form-control" id="edit_legal_entity_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Type</label>
                                <select class="form-select" id="edit_business_type">
                                    <option value="">-- Select --</option>
                                    <option value="Proprietorship">Proprietorship</option>
                                    <option value="Partnership">Partnership</option>
                                    <option value="LLP">LLP</option>
                                    <option value="Pvt Ltd">Private Limited</option>
                                    <option value="Public Ltd">Public Limited</option>
                                    <option value="Others">Others</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Incorporation</label>
                                <input type="date" class="form-control" id="edit_incorporation_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="url" class="form-control" id="edit_website">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registered Address</label>
                                <textarea class="form-control" id="edit_registered_address" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Corporate Address</label>
                                <textarea class="form-control" id="edit_corporate_address" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Parent Company</label>
                                <input type="text" class="form-control" id="edit_parent_company">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ===========================================
             SECTION 2: CONTACT DETAILS
             =========================================== -->
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sectionContact">
                    <i class="bi bi-person-lines-fill me-2"></i>
                    <strong>Contact Details</strong>
                </button>
            </h2>
            <div id="sectionContact" class="accordion-collapse collapse" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    
                    <!-- Section Actions -->
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="toggleEdit('contact')">
                            <i class="bi bi-pencil me-1"></i><span id="contactEditBtnText">Edit</span>
                        </button>
                        <button class="btn btn-sm btn-success d-none" id="contactSaveBtn" onclick="saveSection('contact')">
                            <i class="bi bi-check-lg me-1"></i>Save
                        </button>
                        <button class="btn btn-sm btn-secondary d-none ms-2" id="contactCancelBtn" onclick="cancelEdit('contact')">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </button>
                    </div>

                    <!-- View Mode -->
                    <div id="contactViewMode">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Contact Person</label>
                                <p class="fw-medium mb-0" id="view_contact_person">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Designation</label>
                                <p class="fw-medium mb-0" id="view_designation">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Mobile</label>
                                <p class="fw-medium mb-0" id="view_mobile">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Email</label>
                                <p class="fw-medium mb-0" id="view_contact_email">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Alternate Mobile</label>
                                <p class="fw-medium mb-0" id="view_alternate_mobile">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Landline</label>
                                <p class="fw-medium mb-0" id="view_landline">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="contactEditMode" class="d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="edit_contact_person">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Designation</label>
                                <input type="text" class="form-control" id="edit_designation">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile</label>
                                <input type="text" class="form-control" id="edit_mobile" maxlength="10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_contact_email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alternate Mobile</label>
                                <input type="text" class="form-control" id="edit_alternate_mobile" maxlength="10">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Landline</label>
                                <input type="text" class="form-control" id="edit_landline">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ===========================================
             SECTION 3: STATUTORY INFORMATION
             =========================================== -->
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sectionStatutory">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <strong>Statutory Information</strong>
                </button>
            </h2>
            <div id="sectionStatutory" class="accordion-collapse collapse" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    
                    <!-- Section Actions -->
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="toggleEdit('statutory')">
                            <i class="bi bi-pencil me-1"></i><span id="statutoryEditBtnText">Edit</span>
                        </button>
                        <button class="btn btn-sm btn-success d-none" id="statutorySaveBtn" onclick="saveSection('statutory')">
                            <i class="bi bi-check-lg me-1"></i>Save
                        </button>
                        <button class="btn btn-sm btn-secondary d-none ms-2" id="statutoryCancelBtn" onclick="cancelEdit('statutory')">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </button>
                    </div>

                    <!-- View Mode -->
                    <div id="statutoryViewMode">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">PAN Number</label>
                                <p class="fw-medium mb-0" id="view_pan_number">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">TAN Number</label>
                                <p class="fw-medium mb-0" id="view_tan_number">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">GSTIN</label>
                                <p class="fw-medium mb-0" id="view_gstin">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">CIN</label>
                                <p class="fw-medium mb-0" id="view_cin">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">MSME Registered</label>
                                <p class="fw-medium mb-0" id="view_msme_registered">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="statutoryEditMode" class="d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">PAN Number</label>
                                <input type="text" class="form-control" id="edit_pan_number" maxlength="10" style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">TAN Number</label>
                                <input type="text" class="form-control" id="edit_tan_number" maxlength="10" style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GSTIN</label>
                                <input type="text" class="form-control" id="edit_gstin" maxlength="15" style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CIN</label>
                                <input type="text" class="form-control" id="edit_cin" maxlength="21" style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">MSME Registered</label>
                                <select class="form-select" id="edit_msme_registered">
                                    <option value="">-- Select --</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ===========================================
             SECTION 4: BANKING DETAILS
             =========================================== -->
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sectionBank">
                    <i class="bi bi-bank me-2"></i>
                    <strong>Banking Details</strong>
                </button>
            </h2>
            <div id="sectionBank" class="accordion-collapse collapse" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    
                    <!-- Section Actions -->
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="toggleEdit('bank')">
                            <i class="bi bi-pencil me-1"></i><span id="bankEditBtnText">Edit</span>
                        </button>
                        <button class="btn btn-sm btn-success d-none" id="bankSaveBtn" onclick="saveSection('bank')">
                            <i class="bi bi-check-lg me-1"></i>Save
                        </button>
                        <button class="btn btn-sm btn-secondary d-none ms-2" id="bankCancelBtn" onclick="cancelEdit('bank')">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </button>
                    </div>

                    <!-- View Mode -->
                    <div id="bankViewMode">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Bank Name</label>
                                <p class="fw-medium mb-0" id="view_bank_name">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Branch Address</label>
                                <p class="fw-medium mb-0" id="view_branch_address">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Account Holder Name</label>
                                <p class="fw-medium mb-0" id="view_account_holder_name">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Account Number</label>
                                <p class="fw-medium mb-0" id="view_account_number">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">IFSC Code</label>
                                <p class="fw-medium mb-0" id="view_ifsc_code">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Account Type</label>
                                <p class="fw-medium mb-0" id="view_account_type">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="bankEditMode" class="d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Bank Name</label>
                                <input type="text" class="form-control" id="edit_bank_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Branch Address</label>
                                <input type="text" class="form-control" id="edit_branch_address">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Holder Name</label>
                                <input type="text" class="form-control" id="edit_account_holder_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="edit_account_number" maxlength="18">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IFSC Code</label>
                                <input type="text" class="form-control" id="edit_ifsc_code" maxlength="11" style="text-transform: uppercase;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Account Type</label>
                                <select class="form-select" id="edit_account_type">
                                    <option value="">-- Select --</option>
                                    <option value="Current">Current</option>
                                    <option value="Savings">Savings</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ===========================================
             SECTION 5: TAX INFORMATION
             =========================================== -->
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sectionTax">
                    <i class="bi bi-receipt me-2"></i>
                    <strong>Tax Information</strong>
                </button>
            </h2>
            <div id="sectionTax" class="accordion-collapse collapse" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    
                    <!-- Section Actions -->
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="toggleEdit('tax')">
                            <i class="bi bi-pencil me-1"></i><span id="taxEditBtnText">Edit</span>
                        </button>
                        <button class="btn btn-sm btn-success d-none" id="taxSaveBtn" onclick="saveSection('tax')">
                            <i class="bi bi-check-lg me-1"></i>Save
                        </button>
                        <button class="btn btn-sm btn-secondary d-none ms-2" id="taxCancelBtn" onclick="cancelEdit('tax')">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </button>
                    </div>

                    <!-- View Mode -->
                    <div id="taxViewMode">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Tax Residency</label>
                                <p class="fw-medium mb-0" id="view_tax_residency">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">GST Reverse Charge</label>
                                <p class="fw-medium mb-0" id="view_gst_reverse_charge">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">SEZ Status</label>
                                <p class="fw-medium mb-0" id="view_sez_status">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="taxEditMode" class="d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tax Residency</label>
                                <select class="form-select" id="edit_tax_residency">
                                    <option value="">-- Select --</option>
                                    <option value="India">India</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST Reverse Charge</label>
                                <select class="form-select" id="edit_gst_reverse_charge">
                                    <option value="">-- Select --</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SEZ Status</label>
                                <select class="form-select" id="edit_sez_status">
                                    <option value="">-- Select --</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ===========================================
             SECTION 6: BUSINESS PROFILE
             =========================================== -->
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sectionBusiness">
                    <i class="bi bi-briefcase me-2"></i>
                    <strong>Business Profile</strong>
                </button>
            </h2>
            <div id="sectionBusiness" class="accordion-collapse collapse" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    
                    <!-- Section Actions -->
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="toggleEdit('business')">
                            <i class="bi bi-pencil me-1"></i><span id="businessEditBtnText">Edit</span>
                        </button>
                        <button class="btn btn-sm btn-success d-none" id="businessSaveBtn" onclick="saveSection('business')">
                            <i class="bi bi-check-lg me-1"></i>Save
                        </button>
                        <button class="btn btn-sm btn-secondary d-none ms-2" id="businessCancelBtn" onclick="cancelEdit('business')">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </button>
                    </div>

                    <!-- View Mode -->
                    <div id="businessViewMode">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label text-muted small">Core Activities</label>
                                <p class="fw-medium mb-0" id="view_core_activities">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Employee Count</label>
                                <p class="fw-medium mb-0" id="view_employee_count">-</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Credit Period</label>
                                <p class="fw-medium mb-0" id="view_credit_period">-</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Turnover FY 2022-23</label>
                                <p class="fw-medium mb-0" id="view_turnover_fy1">-</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Turnover FY 2023-24</label>
                                <p class="fw-medium mb-0" id="view_turnover_fy2">-</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Turnover FY 2024-25</label>
                                <p class="fw-medium mb-0" id="view_turnover_fy3">-</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="businessEditMode" class="d-none">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Core Activities</label>
                                <textarea class="form-control" id="edit_core_activities" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee Count</label>
                                <select class="form-select" id="edit_employee_count">
                                    <option value="">-- Select --</option>
                                    <option value="1-10">1-10</option>
                                    <option value="11-50">11-50</option>
                                    <option value="51-100">51-100</option>
                                    <option value="101-500">101-500</option>
                                    <option value="501-1000">501-1000</option>
                                    <option value="1000+">1000+</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Credit Period</label>
                                <select class="form-select" id="edit_credit_period">
                                    <option value="">-- Select --</option>
                                    <option value="Immediate">Immediate</option>
                                    <option value="15 Days">15 Days</option>
                                    <option value="30 Days">30 Days</option>
                                    <option value="45 Days">45 Days</option>
                                    <option value="60 Days">60 Days</option>
                                    <option value="90 Days">90 Days</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Turnover FY 2022-23</label>
                                <input type="text" class="form-control" id="edit_turnover_fy1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Turnover FY 2023-24</label>
                                <input type="text" class="form-control" id="edit_turnover_fy2">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Turnover FY 2024-25</label>
                                <input type="text" class="form-control" id="edit_turnover_fy3">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ===========================================
             SECTION 7: DOCUMENTS
             =========================================== -->
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sectionDocuments">
                    <i class="bi bi-folder me-2"></i>
                    <strong>Documents</strong>
                </button>
            </h2>
            <div id="sectionDocuments" class="accordion-collapse collapse" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    <div id="documentsContainer">
                        <p class="text-muted">Loading documents...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===========================================
             SECTION 8: APPROVAL HISTORY
             =========================================== -->
        <div class="accordion-item mb-3 border rounded shadow-sm">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sectionHistory">
                    <i class="bi bi-clock-history me-2"></i>
                    <strong>Approval History</strong>
                </button>
            </h2>
            <div id="sectionHistory" class="accordion-collapse collapse" data-bs-parent="#vendorAccordion">
                <div class="accordion-body">
                    <div id="historyContainer">
                        <p class="text-muted">Loading history...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- =====================================================
         ACTION BUTTONS
         ===================================================== -->
    <div class="card shadow-sm mt-4" id="actionButtonsCard">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <button class="btn btn-success btn-lg px-4" id="btnApprove" onclick="approveVendor()">
                    <i class="bi bi-check-circle me-2"></i>Approve Vendor
                </button>
                <button class="btn btn-danger btn-lg px-4" id="btnReject" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bi bi-x-circle me-2"></i>Reject Vendor
                </button>
                <button class="btn btn-info btn-lg px-4" id="btnRevision" data-bs-toggle="modal" data-bs-target="#revisionModal">
                    <i class="bi bi-arrow-repeat me-2"></i>Request Revision
                </button>
            </div>
        </div>
    </div>

</div>

<!-- =====================================================
     INCLUDE MODALS
     ===================================================== -->

@push('head')
    <link href="{{ asset('css/vendor-review.css') }}" rel="stylesheet">
@endpush

@include('pages.vendors.approval.partials.modals')

@endsection

@push('scripts')
<script>
    const vendorId = {{ $id }};
</script>
<script src="{{ asset('js/vendor-approval-review.js') }}"></script>
@endpush