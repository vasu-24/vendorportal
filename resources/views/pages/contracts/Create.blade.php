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

                        {{-- Template --}}
                        <div class="mb-3">
                            <label class="form-label mb-1"><strong>Template <span class="text-danger">*</span></strong></label>
                            <select id="agreementTemplate" name="template_file" class="form-select form-select-sm" required>
                                <option value="">-- Select Template --</option>
                                @foreach($agreementFiles as $file)
                                    <option value="{{ $file }}" {{ $file === $defaultFile ? 'selected' : '' }}>
                                        {{ $file }}
                                    </option>
                                @endforeach
                            </select>
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

                        {{-- COMMON CONTRACT CONFIGURATIONS FOR ALL TEMPLATES --}}
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

const UNITS = [
    { value: 'hrs', label: 'Hours' },
    { value: 'days', label: 'Days' },
    { value: 'months', label: 'Months' },
    { value: 'nos', label: 'Nos' },
    { value: 'lot', label: 'Lot' },
];

// =====================================================
// TEMPLATE-SPECIFIC ADDITIONAL FIELDS
// =====================================================
const TEMPLATE_ADDITIONAL_FIELDS = {
    'FIDE_Agreement_with_Placeholders2.docx': [
        { name: 'mou_validity_years', label: 'MOU Validity (Years)', type: 'number', required: false, min: 1 },
        { name: 'termination_notice_days', label: 'Termination Notice (Days)', type: 'number', required: false, min: 0 },
        { name: 'second_party_description', label: 'Second Party Description', type: 'textarea', required: false, rows: 3 },
        { name: 'mou_purpose', label: 'Purpose of MOU', type: 'textarea', required: false, rows: 3 },
        { name: 'mou_objectives', label: 'MOU Objectives', type: 'textarea', required: false, rows: 2 },
        { name: 'vendor_contact_name', label: 'Vendor Contact Name', type: 'text', required: false },
        { name: 'vendor_contact_email', label: 'Vendor Contact Email', type: 'email', required: false },
        { name: 'vendor_contact_address', label: 'Vendor Contact Address', type: 'textarea', required: false, rows: 2 }
    ],
    'NDA - FIDE.docx': [
        { name: 'nda_term_years', label: 'NDA Term (Years)', type: 'number', required: true, min: 1 },
        { name: 'disclosing_party_name', label: 'Disclosing Party Name', type: 'text', required: true },
        { name: 'disclosing_party_address', label: 'Disclosing Party Address', type: 'textarea', required: true, rows: 3 },
        { name: 'client_legal_name', label: 'Client Legal Name', type: 'text', required: true },
        { name: 'client_address', label: 'Client Address', type: 'textarea', required: true, rows: 3 },
        { name: 'confidentiality_purpose', label: 'Purpose of Disclosure', type: 'textarea', required: true, rows: 4 },
        { name: 'confidentiality_survival_years', label: 'Survival Period (Years)', type: 'number', required: true, min: 1 },
        { name: 'disclosing_party_signatory', label: 'Disclosing Party Signatory', type: 'text', required: true },
        { name: 'client_signatory', label: 'Client Signatory', type: 'text', required: true },
        { name: 'signing_date', label: 'Date of Signing', type: 'date', required: false }
    ],
    'Consulting Agreement Template - FIDE.docx': [
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
        { name: 'consultant_signatory_name', label: 'Consultant Signatory', type: 'text', required: true },
        { name: 'client_signatory_name', label: 'Client Signatory', type: 'text', required: true },
        { name: 'signing_place', label: 'Signing Place', type: 'text', required: true },
        { name: 'signing_date', label: 'Date of Signing', type: 'date', required: false }
    ],
    'MSA Template - FIDE.docx': [
        { name: 'msa_execution_date', label: 'MSA Execution Date', type: 'date', required: true },
        { name: 'service_provider_name', label: 'Service Provider Name', type: 'text', required: true },
        { name: 'service_provider_address', label: 'Service Provider Address', type: 'textarea', required: true, rows: 3 },
        { name: 'client_legal_name', label: 'Client Legal Name', type: 'text', required: true },
        { name: 'client_cin', label: 'Client CIN', type: 'text', required: true },
        { name: 'services_description', label: 'Services Description', type: 'textarea', required: true, rows: 4 },
        { name: 'sow_reference', label: 'SOW Reference', type: 'text', required: false },
        { name: 'service_fees', label: 'Service Fees', type: 'number', required: true, min: 0, step: 0.01 },
        { name: 'payment_terms', label: 'Payment Terms', type: 'textarea', required: true, rows: 2 },
        { name: 'service_provider_signatory', label: 'Service Provider Signatory', type: 'text', required: true },
        { name: 'client_signatory', label: 'Client Signatory', type: 'text', required: true },
        { name: 'signing_date', label: 'Date of Signing', type: 'date', required: false }
    ]
};

// =====================================================
// INITIALIZATION
// =====================================================
$(document).ready(function() {
    console.log('✓ Form initialized');
    console.log('✓ Reporting Tags:', reportingTags);
    
    // Template change
    $('#agreementTemplate').change(function() {
        const file = this.value;
        if (file) {
            $('#agreementPreview').attr('src', "{{ route('contracts.preview') }}?file=" + encodeURIComponent(file));
            $('#previewFileName').text(file);
            loadAdditionalFields(file);
        } else {
            $('#dynamicFieldsContainer').html('');
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
    
    @if(isset($defaultFile) && $defaultFile)
        loadAdditionalFields('{{ $defaultFile }}');
    @endif
});

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

    let apiData = {
        template_file: template,
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
            tags: [tagId]  // Single tag in array
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