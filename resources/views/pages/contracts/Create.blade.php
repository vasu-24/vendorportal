@extends('layouts.app')

@section('content')
<style>
    /* Tag dropdown - looks EXACTLY like normal select */
    .tag-dropdown {
        position: relative;
        width: 100%;
    }
    
    .tag-dropdown-btn {
        width: 100%;
        text-align: left;
        background: #fff url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") no-repeat right 0.5rem center/12px 12px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 0.25rem 1.75rem 0.25rem 0.5rem;
        font-size: 0.875rem;
        color: #212529;
        cursor: pointer;
        height: 31px;
        line-height: 1.5;
    }
    
    .tag-dropdown-btn:hover,
    .tag-dropdown-btn:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .tag-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 9999;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-height: 200px;
        overflow-y: auto;
        display: none;
        margin-top: 2px;
    }
    
    .tag-dropdown-menu.show {
        display: block;
    }
    
    .tag-dropdown-item {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        font-size: 13px;
        transition: background 0.15s;
    }
    
    .tag-dropdown-item:hover {
        background: #f0f7ff;
    }
    
    .tag-dropdown-item input[type="checkbox"] {
        margin-right: 10px;
        cursor: pointer;
        width: 16px;
        height: 16px;
    }
    
    .tag-dropdown-item label {
        cursor: pointer;
        margin: 0;
        flex: 1;
        user-select: none;
    }
    
    .tag-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #6c757d;
    }
    
    .tag-text.has-value {
        color: #212529;
    }
    
    /* Fix table overflow so dropdown is visible */
    #configSection .table-responsive {
        overflow: visible !important;
    }
    
    #configTable {
        overflow: visible;
    }
    
    #configTable td {
        overflow: visible;
        position: relative;
    }
    
    #configTable tbody {
        overflow: visible;
    }
</style>

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
                        <div class="mb-2">
                            <label class="form-label mb-1"><strong>Template</strong></label>
                            <select id="agreementTemplate" name="template_file" class="form-select form-select-sm">
                                @foreach($agreementFiles as $file)
                                    <option value="{{ $file }}" {{ $file === $defaultFile ? 'selected' : '' }}>
                                        {{ $file }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

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

                        {{-- MOU Validity & Termination Notice --}}
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label mb-1"><strong>MOU Validity (Years)</strong></label>
                                <input type="number" id="mou_validity_years" name="mou_validity_years"
                                       class="form-control form-control-sm" min="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1"><strong>Termination Notice (Days)</strong></label>
                                <input type="number" id="termination_notice_days" name="termination_notice_days"
                                       class="form-control form-control-sm" value="30">
                            </div>
                        </div>

                        <hr class="my-3">
                        <h6 class="text-muted mb-2">Section 4: Background & Purpose</h6>

                        {{-- Second Party Description & MOU Purpose --}}
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label mb-1"><strong>Second Party Description</strong></label>
                                <textarea id="second_party_description" name="second_party_description"
                                          class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1"><strong>Purpose of MOU</strong></label>
                                <textarea id="mou_purpose" name="mou_purpose"
                                          class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                        </div>

                        {{-- MOU Objectives --}}
                        <div class="mb-2">
                            <label class="form-label mb-1"><strong>MOU Objectives</strong></label>
                            <textarea id="mou_objectives" name="mou_objectives"
                                      class="form-control form-control-sm" rows="2"></textarea>
                        </div>

                        <hr class="my-3">
                        <h6 class="text-muted mb-2">Section 8: Vendor Contact Details</h6>

                        {{-- Contact Name & Email --}}
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label mb-1"><strong>Contact Name</strong></label>
                                <input type="text" id="vendor_contact_name" name="vendor_contact_name"
                                       class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-1"><strong>Contact Email</strong></label>
                                <input type="email" id="vendor_contact_email" name="vendor_contact_email"
                                       class="form-control form-control-sm">
                            </div>
                        </div>

                        {{-- Contact Address --}}
                        <div class="mb-2">
                            <label class="form-label mb-1"><strong>Contact Address</strong></label>
                            <textarea id="vendor_contact_address" name="vendor_contact_address"
                                      class="form-control form-control-sm" rows="2"></textarea>
                        </div>

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

                            {{-- Warning for 3+ tags --}}
                            <div class="alert alert-warning py-1 px-2 mb-2 d-none" id="ceoWarning" style="font-size: 12px;">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <strong>Note:</strong> 3+ tags selected - Invoice will go directly to CEO for approval
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="configTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 22%;">Category</th>
                                            <th style="width: 10%;">Qty</th>
                                            <th style="width: 13%;">Unit</th>
                                            <th style="width: 13%;">Rate</th>
                                            <th style="width: 36%;">Tags</th>
                                            <th style="width: 6%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="configTableBody"></tbody>
                                </table>
                            </div>

                            {{-- Spacer for dropdown visibility --}}
                            <div style="min-height: 120px;"></div>

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
// INITIALIZATION
// =====================================================
$(document).ready(function() {
    console.log('Categories:', categories);
    console.log('Reporting Tags:', reportingTags);
    
    // Template change - update preview
    $('#agreementTemplate').change(function() {
        const file = this.value;
        if (file) {
            $('#agreementPreview').attr('src', "{{ route('contracts.preview') }}?file=" + encodeURIComponent(file));
            $('#previewFileName').text(file);
        }
    });

    // Company change - fill hidden fields
    $('#company_id').change(function() {
        const opt = this.selectedOptions[0];
        $('#company_name').val(opt?.dataset.name || '');
        $('#company_cin').val(opt?.dataset.cin || '');
        $('#company_address').val(opt?.dataset.address || '');
    });

    // Vendor change - fill hidden fields
    $('#vendor_id').change(function() {
        const opt = this.selectedOptions[0];
        $('#vendor_name').val(opt?.dataset.name || '');
        $('#vendor_cin').val(opt?.dataset.cin || '');
        $('#vendor_address').val(opt?.dataset.address || '');
    });

    // Form submit
    $('#contractForm').submit(handleSubmit);
    
    // Close tag dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.tag-dropdown').length) {
            $('.tag-dropdown-menu').removeClass('show');
        }
    });
});

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
// ADD ROW WITH TAG CHECKBOX DROPDOWN
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
    
    // Tag checkboxes
    let tagCheckboxes = '';
    if (reportingTags && reportingTags.length > 0) {
        reportingTags.forEach(t => {
            tagCheckboxes += `
                <div class="tag-dropdown-item">
                    <input type="checkbox" class="form-check-input tag-checkbox" 
                           value="${t.tag_id}" 
                           data-name="${t.tag_name}"
                           data-row="${rowCounter}"
                           id="tag_${rowCounter}_${t.tag_id}"
                           onclick="event.stopPropagation(); updateTagDisplay(${rowCounter})">
                    <label for="tag_${rowCounter}_${t.tag_id}" onclick="event.stopPropagation();">${t.tag_name}</label>
                </div>
            `;
        });
    } else {
        tagCheckboxes = '<div class="p-2 text-muted small">No tags available</div>';
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
                <div class="tag-dropdown" id="tagDropdown_${rowCounter}">
                    <div class="tag-dropdown-btn" onclick="toggleTagDropdown(${rowCounter})">
                        <span class="tag-text" id="tagText_${rowCounter}">-- Select Tags --</span>
                    </div>
                    <div class="tag-dropdown-menu" id="tagMenu_${rowCounter}">
                        ${tagCheckboxes}
                    </div>
                </div>
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
// TOGGLE TAG DROPDOWN
// =====================================================
function toggleTagDropdown(rowId) {
    const menu = $(`#tagMenu_${rowId}`);
    
    // Close all other dropdowns
    $('.tag-dropdown-menu').not(menu).removeClass('show');
    
    // Toggle current
    menu.toggleClass('show');
}

// =====================================================
// UPDATE TAG DISPLAY
// =====================================================
function updateTagDisplay(rowId) {
    const selectedTags = [];
    
    $(`#row_${rowId} .tag-checkbox:checked`).each(function() {
        selectedTags.push($(this).data('name'));
    });
    
    const textEl = $(`#tagText_${rowId}`);
    
    if (selectedTags.length > 0) {
        textEl.text(selectedTags.join(', '));
        textEl.addClass('has-value');
    } else {
        textEl.text('-- Select Tags --');
        textEl.removeClass('has-value');
    }
    
    checkTagCount();
}

// =====================================================
// CHECK TAG COUNT - CEO WARNING
// =====================================================
function checkTagCount() {
    const uniqueTags = new Set();
    
    $('.tag-checkbox:checked').each(function() {
        uniqueTags.add($(this).val());
    });
    
    if (uniqueTags.size >= 3) {
        $('#ceoWarning').removeClass('d-none');
    } else {
        $('#ceoWarning').addClass('d-none');
    }
}

// =====================================================
// REMOVE ROW
// =====================================================
function removeRow(id) {
    $(`#row_${id}`).remove();
    checkTagCount();
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
    
    const formData = {
        '_token': csrfToken,
        'effective_date': $('#effective_date').val() || '',
        'mou_validity_years': $('#mou_validity_years').val() || '',
        'second_party_description': $('#second_party_description').val() || '',
        'mou_purpose': $('#mou_purpose').val() || '',
        'mou_objectives': $('#mou_objectives').val() || '',
        'termination_notice_days': $('#termination_notice_days').val() || '30',
        'vendor_contact_name': $('#vendor_contact_name').val() || '',
        'vendor_contact_email': $('#vendor_contact_email').val() || '',
        'vendor_contact_address': $('#vendor_contact_address').val() || ''
    };

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
    
    setTimeout(() => {
        document.body.removeChild(form);
    }, 1000);
}

// =====================================================
// HANDLE SUBMIT
// =====================================================
function handleSubmit(e) {
    e.preventDefault();

    // Validate vendor
    if (!$('#vendor_id').val()) {
        showAlert('danger', 'Please select a vendor.');
        return;
    }

    // Collect items with tags
    const items = [];
    let hasError = false;
    
    $('#configTableBody tr').each(function() {
        const categoryId = $(this).find('.cat-select').val();
        const qty = $(this).find('.qty-input').val();
        const unit = $(this).find('.unit-select').val();
        const rate = $(this).find('.rate-input').val();
        
        // Get checked tags
        const tags = [];
        $(this).find('.tag-checkbox:checked').each(function() {
            tags.push($(this).val());
        });
        
        if (!categoryId || !qty || !unit || !rate) {
            hasError = true;
            return false;
        }
        
        items.push({
            category_id: categoryId,
            quantity: parseFloat(qty) || 0,
            unit: unit,
            rate: parseFloat(rate) || 0,
            tags: tags
        });
    });

    if (items.length === 0) {
        showAlert('danger', 'Please add at least one configuration item.');
        if (!configOpen) toggleConfig();
        return;
    }
    
    if (hasError) {
        showAlert('danger', 'Please fill all fields in configuration items.');
        return;
    }

    const contractValue = $('#contractValue').val();
    if (!contractValue || parseFloat(contractValue) <= 0) {
        showAlert('danger', 'Please enter contract value.');
        if (!configOpen) toggleConfig();
        return;
    }

    // Prepare API data
    const apiData = {
        template_file: $('#agreementTemplate').val(),
        company_id: $('#company_id').val() || null,
        vendor_id: $('#vendor_id').val(),
        start_date: $('#start_date').val() || null,
        end_date: $('#end_date').val() || null,
        contract_value: parseFloat(contractValue),
        items: items
    };

    console.log('Submitting:', apiData);

    // Disable button
    $('#saveBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

    // Submit to API
    axios.post(API_BASE, apiData)
        .then(res => {
            console.log('API Response:', res.data);
            
            const contractId = res.data.data.id;
            const contractNumber = res.data.data.contract_number;
            
            showAlert('success', 'Contract saved! Contract #' + contractNumber + '. Downloading Word file...');
            
            // Download Word file
            downloadWordFile(contractId);
            
            // Redirect after 5 seconds
            setTimeout(() => {
                window.location.href = '{{ route("contracts.index") }}';
            }, 5000);
        })
        .catch(err => {
            console.error('API Error:', err);
            
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