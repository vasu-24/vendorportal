@extends('layouts.app')

@section('content')
<div class="container-fluid py-2">

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <div class="row g-3">
        {{-- LEFT: PDF Preview --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Template Preview</span>
                    <span class="badge bg-secondary" id="previewFileName">{{ $defaultFile ?? 'No file' }}</span>
                </div>
                <div class="card-body p-0">
                    <iframe id="agreementPreview"
                            src="{{ isset($defaultFile) && $defaultFile ? route('contracts.preview', ['file' => $defaultFile]) : '' }}"
                            style="width:100%; height:calc(100vh - 120px); border:0;">
                    </iframe>
                </div>
            </div>
        </div>

        {{-- RIGHT: Contract Form --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2">
                    <span class="fw-semibold">Create New Contract</span>
                </div>

                <div class="card-body py-3" style="overflow-y: auto; max-height: calc(100vh - 120px);">
                    <form id="contractForm">

                        {{-- CONTRACT TYPE TOGGLE --}}
                        <div class="mb-3">
                            <label class="form-label mb-2"><strong>Contract Type <span class="text-danger">*</span></strong></label>
                            <div class="d-flex gap-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="contract_type" 
                                           id="contractTypeNormal" value="normal" checked>
                                    <label class="form-check-label" for="contractTypeNormal">
                                        <i class="bi bi-file-earmark-text me-1"></i>Normal Contract
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="contract_type" 
                                           id="contractTypeAdhoc" value="adhoc">
                                    <label class="form-check-label" for="contractTypeAdhoc">
                                        <i class="bi bi-lightning me-1"></i>ADHOC Contract
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- NORMAL CONTRACT SECTION --}}
                        <div id="normalContractSection">

                        {{-- Template --}}
                        <div class="mb-3">
                            <label class="form-label mb-1"><strong>Template <span class="text-danger">*</span></strong></label>
<select id="agreementTemplate" name="template_file" class="form-select form-select-sm" required>
    <option value="">-- Select Template --</option>
    @foreach(config('contracts.templates') as $key => $template)
        <option value="{{ $template['file'] }}" 
                data-type="{{ $template['type'] }}"
                data-requires-config="{{ $template['requires_config'] ? 'true' : 'false' }}"
                data-allows-invoice="{{ $template['allows_invoice'] ? 'true' : 'false' }}"
                {{ ($template['file'] ?? '') === ($defaultFile ?? '') ? 'selected' : '' }}>
            {{ $template['label'] }}
        </option>
    @endforeach
</select>                            <small class="text-muted" id="templateDescription"></small>
                        </div>

                        {{-- Template Type Badge --}}
                        <div class="mb-3" id="templateTypeBadge" style="display: none;">
                            <span id="typeBadge" class="badge"></span>
                        </div>

                        {{-- COMMON FIELDS FOR ALL TEMPLATES --}}
                        <div id="commonFields">
                            {{-- Company & Vendor --}}
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label mb-1"><strong>Company</strong></label>
                                    <select id="company_id" name="company_id" class="form-select form-select-sm">
                                        <option value="">– Select Company –</option>
                                        @foreach($organisations as $org)
                                            <option value="{{ $org->id }}"
                                                    data-name="{{ $org->company_name }}"
                                                    data-cin="{{ $org->cin }}"
                                                    data-address="{{ $org->address }}">
                                                {{ $org->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="company_name" name="company_name">
                                    <input type="hidden" id="company_cin" name="company_cin">
                                    <input type="hidden" id="company_address" name="company_address">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label mb-1"><strong>Vendor <span class="text-danger">*</span></strong></label>
                                    <select id="vendor_id" name="vendor_id" class="form-select form-select-sm" required>
                                        <option value="">– Select Vendor –</option>
                                        @foreach($vendors as $vendor)
                                            @php
                                                $companyInfo = $vendor->companyInfo;
                                                $statutoryInfo = $vendor->statutoryInfo;
                                            @endphp
                                            <option value="{{ $vendor->id }}"
                                                    data-name="{{ $companyInfo->legal_entity_name ?? $vendor->vendor_name }}"
                                                    data-cin="{{ $statutoryInfo->cin ?? '' }}"
                                                    data-address="{{ $companyInfo->registered_address ?? '' }}">
                                                {{ $companyInfo->legal_entity_name ?? $vendor->vendor_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="vendor_name" name="vendor_name">
                                </div>
                            </div>

                            {{-- Vendor CIN & Address --}}
                            <div class="row g-2 mb-2">
                                <div class="col-md-5">
                                    <label class="form-label mb-1"><strong>Vendor CIN</strong></label>
                                    <input type="text" id="vendor_cin" name="vendor_cin"
                                           class="form-control form-control-sm bg-light" readonly>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label mb-1"><strong>Vendor Address</strong></label>
                                    <input type="text" id="vendor_address" name="vendor_address"
                                           class="form-control form-control-sm bg-light" readonly>
                                </div>
            </div>

                            {{-- Contract Dates --}}
                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <label class="form-label mb-1"><strong>Effective Date</strong></label>
                                    <input type="date" id="effective_date" name="effective_date" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1"><strong>Start Date</strong></label>
                                    <input type="date" id="start_date" name="start_date" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1"><strong>End Date</strong></label>
                                    <input type="date" id="end_date" name="end_date" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        {{-- DYNAMIC TEMPLATE-SPECIFIC FIELDS --}}
                        <div id="dynamicFieldsContainer"></div>

                        {{-- CONTRACT CONFIGURATIONS - ONLY FOR PAID TEMPLATES --}}
                        <div id="contractConfigSection">
                            <hr class="my-3">

                            {{-- CONFIG TOGGLE --}}
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="toggleConfig()">
                                <i class="bi bi-gear me-1"></i>Contract Configurations
                                <i class="bi bi-chevron-down ms-1" id="configChevron"></i>
                            </button>

                            {{-- CONFIG SECTION --}}
                            <div class="d-none" id="configSection">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Line items with Reporting Tags</small>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="addRow()">
                                        <i class="bi bi-plus"></i> Add
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0" id="configTable">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 25%;">Category</th>
                                                <th style="width: 12%;">Qty</th>
                                                <th style="width: 15%;">Unit</th>
                                                <th style="width: 15%;">Rate</th>
                                                <th style="width: 25%;">Tag</th>
                                                <th style="width: 8%;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="configTableBody"></tbody>
                                    </table>
                                </div>

                                <div class="mt-3 p-2 bg-light rounded">
                                    <label class="form-label mb-1"><strong>Contract Value <span class="text-danger">*</span></strong></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" id="contractValue" name="contract_value" 
                                               class="form-control form-control-sm" min="0" step="0.01" 
                                               placeholder="Enter contract value">
                                    </div>
                                </div>
                            </div>
                        </div>

                       <hr class="my-3">

                        {{-- Buttons --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm px-4" id="saveBtn">
                                <i class="bi bi-save me-1"></i> Save Contract
                            </button>
                        </div>

                        </div>
                        {{-- END NORMAL CONTRACT SECTION --}}

                        
                    {{-- ADHOC CONTRACT SECTION --}}
                    <div id="adhocContractSection" style="display: none;">

                        {{-- Vendor --}}
                        <div class="mb-3">
                            <label class="form-label mb-1"><strong>Vendor <span class="text-danger">*</span></strong></label>
                         <select id="adhoc_vendor_id" class="form-select form-select-sm">
                                <option value="">– Select Vendor –</option>
                                @foreach($vendors as $vendor)
                                    @php
                                        $companyInfo = $vendor->companyInfo;
                                    @endphp
                                    <option value="{{ $vendor->id }}">
                                        {{ $companyInfo->legal_entity_name ?? $vendor->vendor_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dates --}}
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label mb-1"><strong>Start Date <span class="text-danger">*</span></strong></label>
                               <input type="date" id="adhoc_start_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1"><strong>End Date <span class="text-danger">*</span></strong></label>
                            <input type="date" id="adhoc_end_date" class="form-control form-control-sm">
                            </div>
                        </div>

                        {{-- ADHOC Config Table --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0"><strong>Configuration <span class="text-danger">*</span></strong></label>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="addAdhocRow()">
                                    <i class="bi bi-plus"></i> Add
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="adhocConfigTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 45%;">Category</th>
                                            <th style="width: 45%;">Tag</th>
                                            <th style="width: 10%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="adhocConfigTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- SOW Value --}}
                        <div class="mb-3 p-2 bg-light rounded">
                            <label class="form-label mb-1"><strong>SOW Value <span class="text-danger">*</span></strong></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">₹</span>
                               <input type="number" id="adhoc_sow_value" class="form-control form-control-sm" 
       min="0" step="0.01" placeholder="Enter SOW value">
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- Buttons --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                            <button type="button" class="btn btn-primary btn-sm px-4" id="saveAdhocBtn" onclick="handleAdhocSubmit()">
                                <i class="bi bi-save me-1"></i> Save ADHOC Contract
                            </button>
                        </div>

                    </div>
                    {{-- END ADHOC CONTRACT SECTION --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hidden iframe for download --}}
<iframe id="downloadIframe" name="downloadIframe" style="display:none;"></iframe>
@endsection

@push('scripts')
<script>
const API_BASE = '/api/admin/contracts';
let categories = @json($categories);
let reportingTags = @json($reportingTags);
let rowCounter = 0;
let configOpen = false;

// =====================================================
// TEMPLATE CONFIGURATION FROM CONFIG FILE
// =====================================================
const TEMPLATE_CONFIG = @json(config('contracts.templates'));

// Get paid/non-paid template lists
const PAID_TEMPLATES = @json(config('contracts.helpers.paid_templates'));
const NON_PAID_TEMPLATES = @json(config('contracts.helpers.non_paid_templates'));

const UNITS = [
    { value: 'hrs', label: 'Hours' },
    { value: 'days', label: 'Days' },
    { value: 'months', label: 'Months' },
    { value: 'nos', label: 'Nos' },
    { value: 'lot', label: 'Lot' },
];

// =====================================================
// ADHOC CONTRACT VARIABLES & FUNCTIONS
// =====================================================
let adhocRowCounter = 0;

// Toggle between Normal and ADHOC
$(document).ready(function() {
    // Add this inside existing document.ready OR separately
    $('input[name="contract_type"]').change(function() {
        handleContractTypeChange(this.value);
    });
});

function handleContractTypeChange(type) {
    if (type === 'adhoc') {
        // Hide Normal, Show ADHOC
        $('#normalContractSection').hide();
        $('#adhocContractSection').show();
        
        // Hide left PDF preview
        $('.col-lg-6').first().hide();
        $('.col-lg-6').last().removeClass('col-lg-6').addClass('col-lg-12');
        
        // Add first row if empty
        if ($('#adhocConfigTableBody tr').length === 0) {
            addAdhocRow();
        }
    } else {
        // Show Normal, Hide ADHOC
        $('#normalContractSection').show();
        $('#adhocContractSection').hide();
        
        // Show left PDF preview
        $('.col-lg-12').last().removeClass('col-lg-12').addClass('col-lg-6');
        $('.col-lg-6').first().show();
    }
}

// Add ADHOC config row
function addAdhocRow() {
    adhocRowCounter++;
    
    // Category options
    let categoryOptions = '<option value="">-- Select Category --</option>';
    if (categories && categories.length > 0) {
        categories.forEach(c => {
            categoryOptions += `<option value="${c.id}">${c.name}</option>`;
        });
    }
    
    // Tag options
    let tagOptions = '<option value="">-- Select Tag --</option>';
    if (reportingTags && reportingTags.length > 0) {
        reportingTags.forEach(t => {
            tagOptions += `<option value="${t.tag_id}">${t.tag_name}</option>`;
        });
    }
    
    const row = `
        <tr id="adhoc_row_${adhocRowCounter}">
            <td>
                <select class="form-select form-select-sm adhoc-cat-select" required>
                    ${categoryOptions}
                </select>
            </td>
            <td>
                <select class="form-select form-select-sm adhoc-tag-select" required>
                    ${tagOptions}
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger py-0" onclick="removeAdhocRow(${adhocRowCounter})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#adhocConfigTableBody').append(row);
}

// Remove ADHOC config row
function removeAdhocRow(id) {
    $(`#adhoc_row_${id}`).remove();
}

// Handle ADHOC form submit
function handleAdhocSubmit() {
    // Validate
    const vendorId = $('#adhoc_vendor_id').val();
    if (!vendorId) {
        showAlert('danger', 'Please select a vendor.');
        return;
    }

    const startDate = $('#adhoc_start_date').val();
    const endDate = $('#adhoc_end_date').val();
    if (!startDate || !endDate) {
        showAlert('danger', 'Please enter start and end dates.');
        return;
    }

    const sowValue = $('#adhoc_sow_value').val();
    if (!sowValue || parseFloat(sowValue) <= 0) {
        showAlert('danger', 'Please enter SOW value.');
        return;
    }

    // Collect config items
    const items = [];
    let hasError = false;
    
    $('#adhocConfigTableBody tr').each(function() {
        const categoryId = $(this).find('.adhoc-cat-select').val();
        const tagId = $(this).find('.adhoc-tag-select').val();
        
        if (!categoryId || !tagId) {
            hasError = true;
            return false;
        }
        
        items.push({
            category_id: categoryId,
            tag_id: tagId
        });
    });

    if (items.length === 0) {
        showAlert('danger', 'Please add at least one configuration item.');
        return;
    }
    
    if (hasError) {
        showAlert('danger', 'Please fill all fields in configuration items.');
        return;
    }

    // Prepare API data
    const apiData = {
        contract_type: 'adhoc',
        vendor_id: vendorId,
        start_date: startDate,
        end_date: endDate,
        sow_value: parseFloat(sowValue),
        items: items
    };

    console.log('Submitting ADHOC:', apiData);

    $('#saveAdhocBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

    axios.post(API_BASE, apiData)
        .then(res => {
            const contractNumber = res.data.data.contract_number;
            showAlert('success', 'ADHOC Contract saved! Contract #' + contractNumber);
            
            setTimeout(() => {
                window.location.href = '{{ route("contracts.index") }}';
            }, 2000);
        })
        .catch(err => {
            let errorMsg = 'Failed to save contract.';
            if (err.response?.data?.errors) {
                errorMsg = Object.values(err.response.data.errors).flat().join('<br>');
            } else if (err.response?.data?.message) {
                errorMsg = err.response.data.message;
            }
            
            showAlert('danger', errorMsg);
            $('#saveAdhocBtn').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Save ADHOC Contract');
        });
}

// =====================================================
// TEMPLATE ADDITIONAL FIELDS
// =====================================================
const TEMPLATE_ADDITIONAL_FIELDS = {
    
    // ========== FIDE MOU AGREEMENT ==========
    'FIDE_Agreement_Bold.docx': [
        { name: 'effective_date', label: 'Effective Date', type: 'date', required: true },
        { name: 'mou_validity_years', label: 'MOU Validity (Years)', type: 'number', required: false, min: 1 },
        { name: 'termination_notice_days', label: 'Termination Notice (Days)', type: 'number', required: false, min: 0 },
        { name: 'second_party_description', label: 'Second Party Description', type: 'textarea', required: false, rows: 3 },
        { name: 'mou_purpose', label: 'Purpose of MOU', type: 'textarea', required: false, rows: 3 },
        { name: 'mou_objectives', label: 'MOU Objectives', type: 'textarea', required: false, rows: 2 },
        { name: 'vendor_contact_name', label: 'Vendor Contact Name', type: 'text', required: false },
        { name: 'vendor_contact_designation', label: 'Vendor Contact Designation', type: 'text', required: false },
        { name: 'vendor_contact_email', label: 'Vendor Contact Email', type: 'email', required: false },
        { name: 'vendor_contact_address', label: 'Vendor Contact Address', type: 'textarea', required: false, rows: 2 },
        { name: 'vendor_signatory_name', label: 'Vendor Signatory Name', type: 'text', required: false },
        { name: 'vendor_signatory_designation', label: 'Vendor Signatory Designation', type: 'text', required: false },
        { name: 'vendor_signatory_place', label: 'Vendor Signatory Place', type: 'text', required: false }
    ],

    // ========== NDA AGREEMENT ==========
    'NDA_Bold.docx': [
        { name: 'effective_date', label: 'Effective Date', type: 'date', required: true },
        { name: 'nda_term_years', label: 'NDA Term (Years)', type: 'number', required: true, min: 1 },
        { name: 'confidentiality_survival_years', label: 'Confidentiality Survival (Years)', type: 'number', required: true, min: 1 },
        { name: 'disclosing_party_name', label: 'Disclosing Party Name', type: 'text', required: true },
        { name: 'disclosing_party_short_name', label: 'Disclosing Party Short Name', type: 'text', required: true },
        { name: 'company_incorporation_type', label: 'Company Incorporation Type', type: 'text', required: true },
        { name: 'disclosing_party_address', label: 'Disclosing Party Address', type: 'textarea', required: true, rows: 3 },
        { name: 'client_legal_name', label: 'Client Legal Name', type: 'text', required: true },
        { name: 'client_address', label: 'Client Address', type: 'textarea', required: true, rows: 3 },
        { name: 'confidentiality_purpose', label: 'Purpose of Confidentiality', type: 'textarea', required: true, rows: 4 },
        { name: 'disclosing_party_signatory', label: 'Disclosing Party Signatory', type: 'text', required: true },
        { name: 'client_signatory', label: 'Client Signatory', type: 'text', required: true },
        { name: 'signing_date', label: 'Signing Date', type: 'date', required: false }
    ],

    // ========== CONSULTING AGREEMENT ==========
    'Consulting_Agreement_Bold.docx': [
        { name: 'agreement_date', label: 'Agreement Date', type: 'date', required: true },
        { name: 'consultant_name', label: 'Consultant Name', type: 'text', required: true },
        { name: 'consultant_pan', label: 'Consultant PAN', type: 'text', required: true },
        { name: 'consultant_address', label: 'Consultant Address', type: 'textarea', required: true, rows: 3 },
        { name: 'client_legal_name', label: 'Client Legal Name', type: 'text', required: true },
        { name: 'client_cin', label: 'Client CIN', type: 'text', required: true },
        { name: 'client_registered_address', label: 'Client Registered Address', type: 'textarea', required: true, rows: 3 },
        { name: 'services_description', label: 'Services Description', type: 'textarea', required: true, rows: 4 },
        { name: 'initial_term_months', label: 'Initial Term (Months)', type: 'number', required: true, min: 1 },
        { name: 'termination_notice_days', label: 'Termination Notice (Days)', type: 'number', required: false, min: 0 },
        { name: 'sow_start_date', label: 'SOW Start Date', type: 'date', required: false },
        { name: 'sow_end_date', label: 'SOW End Date', type: 'date', required: false },
        { name: 'consultant_signatory_name', label: 'Consultant Signatory Name', type: 'text', required: true },
        { name: 'client_signatory_name', label: 'Client Signatory Name', type: 'text', required: true },
        { name: 'signing_place', label: 'Signing Place', type: 'text', required: true },
        { name: 'signing_date', label: 'Signing Date', type: 'date', required: false }
    ],

    // ========== MSA AGREEMENT ==========
    'MSA_Template_Bold.docx': [
        { name: 'msa_execution_date', label: 'MSA Execution Date', type: 'date', required: true },
        { name: 'service_provider_name', label: 'Service Provider Name', type: 'text', required: true },
        { name: 'service_provider_address', label: 'Service Provider Address', type: 'textarea', required: true, rows: 3 },
        { name: 'client_legal_name', label: 'Client Legal Name', type: 'text', required: true },
        { name: 'client_cin', label: 'Client CIN', type: 'text', required: true },
        { name: 'client_registered_address', label: 'Client Registered Address', type: 'textarea', required: true, rows: 3 },
        { name: 'client_business_description', label: 'Client Business Description', type: 'textarea', required: false, rows: 3 },
        { name: 'services_description', label: 'Services Description', type: 'textarea', required: true, rows: 4 },
        { name: 'sow_reference', label: 'SOW Reference', type: 'text', required: false },
        { name: 'service_fees', label: 'Service Fees', type: 'number', required: true, min: 0, step: 0.01 },
        { name: 'payment_terms', label: 'Payment Terms', type: 'textarea', required: true, rows: 2 },
        { name: 'service_provider_signatory', label: 'Service Provider Signatory', type: 'text', required: true },
        { name: 'client_signatory', label: 'Client Signatory', type: 'text', required: true },
        { name: 'signing_date', label: 'Signing Date', type: 'date', required: false }
    ]
};

// =====================================================
// HELPER FUNCTIONS
// =====================================================
function isPaidTemplate(templateFile) {
    return PAID_TEMPLATES.includes(templateFile);
}

function isNonPaidTemplate(templateFile) {
    return NON_PAID_TEMPLATES.includes(templateFile);
}

function getTemplateType(templateFile) {
    if (isPaidTemplate(templateFile)) return 'paid';
    if (isNonPaidTemplate(templateFile)) return 'non_paid';
    return 'unknown';
}

function getTemplateInfo(templateFile) {
    for (const key in TEMPLATE_CONFIG) {
        if (TEMPLATE_CONFIG[key].file === templateFile) {
            return TEMPLATE_CONFIG[key];
        }
    }
    return null;
}

// =====================================================
// INITIALIZATION
// =====================================================
$(document).ready(function() {
    console.log('✓ Form initialized');
    console.log('✓ Paid Templates:', PAID_TEMPLATES);
    console.log('✓ Non-Paid Templates:', NON_PAID_TEMPLATES);
    
    // Template change
    $('#agreementTemplate').change(function() {
        const file = this.value;
        if (file) {
            $('#agreementPreview').attr('src', "{{ route('contracts.preview') }}?file=" + encodeURIComponent(file));
            $('#previewFileName').text(file);
            loadAdditionalFields(file);
            handleTemplateTypeChange(file);
        } else {
            $('#dynamicFieldsContainer').html('');
            $('#contractConfigSection').hide();
            $('#templateTypeBadge').hide();
            $('#templateDescription').text('');
        }
    });

    // Company change
    $('#company_id').change(function() {
        const opt = this.selectedOptions[0];
        $('#company_name').val(opt?.dataset.name || '');
        $('#company_cin').val(opt?.dataset.cin || '');
        $('#company_address').val(opt?.dataset.address || '');
    });

    // Vendor change
    $('#vendor_id').change(function() {
        const opt = this.selectedOptions[0];
        $('#vendor_name').val(opt?.dataset.name || '');
        $('#vendor_cin').val(opt?.dataset.cin || '');
        $('#vendor_address').val(opt?.dataset.address || '');
    });

    $('#contractForm').submit(handleSubmit);
    
    // Initialize if default template is selected
    @if(isset($defaultFile) && $defaultFile)
        loadAdditionalFields('{{ $defaultFile }}');
        handleTemplateTypeChange('{{ $defaultFile }}');
    @endif
});

// =====================================================
// HANDLE TEMPLATE TYPE CHANGE (SHOW/HIDE CONFIG)
// =====================================================
function handleTemplateTypeChange(templateFile) {
    const templateInfo = getTemplateInfo(templateFile);
    const isPaid = isPaidTemplate(templateFile);
    
    // Show/Hide Configuration Section
    if (isPaid) {
        $('#contractConfigSection').show();
        console.log('✓ PAID template - showing config section');
    } else {
        $('#contractConfigSection').hide();
        // Close config if it was open
        if (configOpen) {
            configOpen = false;
            $('#configSection').addClass('d-none');
            $('#configChevron').removeClass('bi-chevron-up').addClass('bi-chevron-down');
        }
        // Clear config data
        $('#configTableBody').empty();
        $('#contractValue').val('');
        console.log('✓ NON-PAID template - hiding config section');
    }
    
    // Show Template Type Badge
    if (templateInfo) {
        $('#templateTypeBadge').show();
        if (isPaid) {
            $('#typeBadge')
                .removeClass('bg-secondary bg-info')
                .addClass('bg-success')
                .html('<i class="bi bi-currency-rupee me-1"></i>Paid Contract - Invoice Allowed');
        } else {
            $('#typeBadge')
                .removeClass('bg-success bg-info')
                .addClass('bg-secondary')
                .html('<i class="bi bi-file-text me-1"></i>Non-Paid Contract - No Invoice');
        }
        
        // Show description
        $('#templateDescription').text(templateInfo.description || '');
    } else {
        $('#templateTypeBadge').hide();
        $('#templateDescription').text('');
    }
}

// =====================================================
// LOAD ADDITIONAL FIELDS
// =====================================================
function loadAdditionalFields(templateFile) {
    const fields = TEMPLATE_ADDITIONAL_FIELDS[templateFile];
    
    if (!fields) {
        $('#dynamicFieldsContainer').html('');
        return;
    }
    
    let html = '<hr class="my-3">';
    
    fields.forEach(field => {
        const required = field.required ? '<span class="text-danger">*</span>' : '';
        const requiredAttr = field.required ? 'required' : '';
        
        if (field.type === 'textarea') {
            html += `
                <div class="mb-2">
                    <label class="form-label mb-1"><strong>${field.label} ${required}</strong></label>
                    <textarea id="${field.name}" name="${field.name}" 
                              class="form-control form-control-sm" 
                              rows="${field.rows || 3}" 
                              ${requiredAttr}></textarea>
                </div>
            `;
        } else if (field.type === 'date') {
            html += `
                <div class="mb-2">
                    <label class="form-label mb-1"><strong>${field.label} ${required}</strong></label>
                    <input type="date" id="${field.name}" name="${field.name}" 
                           class="form-control form-control-sm" 
                           ${requiredAttr}>
                </div>
            `;
        } else if (field.type === 'number') {
            html += `
                <div class="mb-2">
                    <label class="form-label mb-1"><strong>${field.label} ${required}</strong></label>
                    <input type="number" id="${field.name}" name="${field.name}" 
                           class="form-control form-control-sm" 
                           min="${field.min || 0}" 
                           step="${field.step || 1}"
                           ${requiredAttr}>
                </div>
            `;
        } else if (field.type === 'email') {
            html += `
                <div class="mb-2">
                    <label class="form-label mb-1"><strong>${field.label} ${required}</strong></label>
                    <input type="email" id="${field.name}" name="${field.name}" 
                           class="form-control form-control-sm" 
                           ${requiredAttr}>
                </div>
            `;
        } else {
            html += `
                <div class="mb-2">
                    <label class="form-label mb-1"><strong>${field.label} ${required}</strong></label>
                    <input type="text" id="${field.name}" name="${field.name}" 
                           class="form-control form-control-sm" 
                           ${requiredAttr}>
                </div>
            `;
        }
    });
    
    $('#dynamicFieldsContainer').html(html);
}

// =====================================================
// TOGGLE CONFIG
// =====================================================
function toggleConfig() {
    configOpen = !configOpen;
    $('#configSection').toggleClass('d-none', !configOpen);
    $('#configChevron').toggleClass('bi-chevron-down bi-chevron-up');
    
    if (configOpen && $('#configTableBody tr').length === 0) {
        addRow();
    }
}

// =====================================================
// ADD ROW - SINGLE TAG SELECT
// =====================================================
function addRow() {
    rowCounter++;
    
    // Category options
    let categoryOptions = '<option value="">-- Select --</option>';
    if (categories && categories.length > 0) {
        categories.forEach(c => {
            categoryOptions += `<option value="${c.id}">${c.name}</option>`;
        });
    }
    
    // Unit options
    let unitOptions = '';
    UNITS.forEach(u => {
        unitOptions += `<option value="${u.value}">${u.label}</option>`;
    });
    
    // Tag options - SINGLE SELECT
    let tagOptions = '<option value="">-- Select Tag --</option>';
    if (reportingTags && reportingTags.length > 0) {
        reportingTags.forEach(t => {
            tagOptions += `<option value="${t.tag_id}">${t.tag_name}</option>`;
        });
    }
    
    const row = `
        <tr id="row_${rowCounter}">
            <td>
                <select class="form-select form-select-sm cat-select" required>
                    ${categoryOptions}
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm qty-input" 
                       min="0" step="0.01" placeholder="0" required>
            </td>
            <td>
                <select class="form-select form-select-sm unit-select" required>
                    ${unitOptions}
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm rate-input" 
                       min="0" step="0.01" placeholder="0" required>
            </td>
            <td>
                <select class="form-select form-select-sm tag-select" required>
                    ${tagOptions}
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger py-0" onclick="removeRow(${rowCounter})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#configTableBody').append(row);
}

// =====================================================
// REMOVE ROW
// =====================================================
function removeRow(id) {
    $(`#row_${id}`).remove();
}

// =====================================================
// SHOW ALERT
// =====================================================
function showAlert(type, message) {
    const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
    $('#alertContainer').html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// =====================================================
// DOWNLOAD WORD FILE
// =====================================================
function downloadWordFile(contractId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const template = $('#agreementTemplate').val();
    
    const formData = { '_token': csrfToken, 'template_file': template };
    
    $('#contractForm').find('input, textarea, select').each(function() {
        const field = $(this);
        const name = field.attr('name');
        if (name && name !== 'template_file' && !name.startsWith('_')) {
            formData[name] = field.val() || '';
        }
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/contracts/${contractId}/download-word`;
    form.style.display = 'none';

    for (const [name, value] of Object.entries(formData)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
    setTimeout(() => document.body.removeChild(form), 1000);
}

// =====================================================
// HANDLE SUBMIT
// =====================================================
function handleSubmit(e) {
    e.preventDefault();

    const template = $('#agreementTemplate').val();
    if (!template) {
        showAlert('danger', 'Please select a template.');
        return;
    }

    if (!$('#vendor_id').val()) {
        showAlert('danger', 'Please select a vendor.');
        return;
    }

    const isPaid = isPaidTemplate(template);

    let apiData = {
        template_file: template,
        template_type: getTemplateType(template), // Send type to backend
        company_id: $('#company_id').val() || null,
        vendor_id: $('#vendor_id').val(),
        effective_date: $('#effective_date').val() || null,
        start_date: $('#start_date').val() || null,
        end_date: $('#end_date').val() || null
    };
    
    const additionalFields = TEMPLATE_ADDITIONAL_FIELDS[template];
    if (additionalFields) {
        additionalFields.forEach(field => {
            apiData[field.name] = $(`#${field.name}`).val() || null;
        });
    }

    // =====================================================
    // ONLY VALIDATE CONFIG FOR PAID TEMPLATES
    // =====================================================
    if (isPaid) {
        // Collect items with SINGLE tag
        const items = [];
        let hasError = false;
        
        $('#configTableBody tr').each(function() {
            const categoryId = $(this).find('.cat-select').val();
            const qty = $(this).find('.qty-input').val();
            const unit = $(this).find('.unit-select').val();
            const rate = $(this).find('.rate-input').val();
            const tagId = $(this).find('.tag-select').val();
            
            if (!categoryId || !qty || !unit || !rate || !tagId) {
                hasError = true;
                return false;
            }
            
            items.push({
                category_id: categoryId,
                quantity: parseFloat(qty) || 0,
                unit: unit,
                rate: parseFloat(rate) || 0,
                tags: [tagId]
            });
        });

        if (items.length === 0) {
            showAlert('danger', 'Please add at least one configuration item.');
            if (!configOpen) toggleConfig();
            return;
        }
        
        if (hasError) {
            showAlert('danger', 'Please fill all fields in configuration items (including Tag).');
            return;
        }

        const contractValue = $('#contractValue').val();
        if (!contractValue || parseFloat(contractValue) <= 0) {
            showAlert('danger', 'Please enter contract value.');
            if (!configOpen) toggleConfig();
            return;
        }

        apiData.contract_value = parseFloat(contractValue);
        apiData.items = items;
    } else {
        // NON-PAID: Set defaults
        apiData.contract_value = 0;
        apiData.items = [];
    }

    console.log('Submitting:', apiData);

    $('#saveBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

    axios.post(API_BASE, apiData)
        .then(res => {
            const contractId = res.data.data.id;
            const contractNumber = res.data.data.contract_number;
            
            showAlert('success', 'Contract saved! Contract #' + contractNumber + '. Downloading Word file...');
            downloadWordFile(contractId);
            
            setTimeout(() => {
                window.location.href = '{{ route("contracts.index") }}';
            }, 5000);
        })
        .catch(err => {
            let errorMsg = 'Failed to save contract.';
            if (err.response?.data?.errors) {
                errorMsg = Object.values(err.response.data.errors).flat().join('<br>');
            } else if (err.response?.data?.message) {
                errorMsg = err.response.data.message;
            }
            
            showAlert('danger', errorMsg);
            $('#saveBtn').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Save Contract');
        });
}
</script>
@endpush