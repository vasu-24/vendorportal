@extends('layouts.app')

@section('content')
<div class="container-fluid py-2">

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <div class="row g-3">
        {{-- LEFT: PDF Preview --}}
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Template Preview</span>
                    <span class="badge bg-secondary" id="previewFileName">{{ $contract->template_file ?? 'No file' }}</span>
                </div>
                <div class="card-body p-0">
                    <iframe id="agreementPreview"
                            src="{{ $contract->template_file ? route('contracts.preview', ['file' => $contract->template_file]) : '' }}"
                            style="width:100%; height:calc(100vh - 120px); border:0;">
                    </iframe>
                </div>
            </div>
        </div>

        {{-- RIGHT: Contract Form --}}
        <div class="col-12 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header py-2">
                    <span class="fw-semibold">Edit Contract: {{ $contract->contract_number }}</span>
                </div>

                <div class="card-body py-3">
                    <form id="contractForm">
                        <input type="hidden" id="contractId" value="{{ $contract->id }}">
                        
                        {{-- Template --}}
                        <div class="mb-2">
                            <label class="form-label mb-1"><strong>Template</strong></label>
                            <select id="agreementTemplate" name="template_file" class="form-select form-select-sm">
                                @foreach($agreementFiles as $file)
                                    <option value="{{ $file }}" {{ $contract->template_file === $file ? 'selected' : '' }}>
                                        {{ $file }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Company --}}
                        <div class="mb-2">
                            <label class="form-label mb-1"><strong>Company</strong></label>
                            <select id="company_id" name="company_id" class="form-select form-select-sm">
                                <option value="">– Select Company –</option>
                                @foreach($organisations as $org)
                                    <option value="{{ $org->id }}"
                                            data-cin="{{ $org->cin }}"
                                            data-address="{{ $org->address }}"
                                            {{ $contract->company_id == $org->id ? 'selected' : '' }}>
                                        {{ $org->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" id="company_cin" name="company_cin" value="{{ $contract->company_cin }}">
                        <input type="hidden" id="company_address" name="company_address" value="{{ $contract->company_address }}">

                        {{-- Vendor --}}
                        <div class="mb-2">
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
                                            data-address="{{ $companyInfo->registered_address ?? '' }}"
                                            {{ $contract->vendor_id == $vendor->id ? 'selected' : '' }}>
                                        {{ $companyInfo->legal_entity_name ?? $vendor->vendor_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" id="vendor_name" name="vendor_name" value="{{ $contract->vendor_name }}">

                        {{-- Vendor CIN & Address --}}
                        <div class="row g-2 mb-2">
                            <div class="col-5">
                                <label class="form-label mb-1"><strong>Vendor CIN</strong></label>
                                <input type="text" id="vendor_cin" name="vendor_cin" 
                                       class="form-control form-control-sm bg-light" 
                                       value="{{ $contract->vendor_cin }}" readonly>
                            </div>
                            <div class="col-7">
                                <label class="form-label mb-1"><strong>Vendor Address</strong></label>
                                <input type="text" id="vendor_address" name="vendor_address" 
                                       class="form-control form-control-sm bg-light" 
                                       value="{{ $contract->vendor_address }}" readonly>
                            </div>
                        </div>

                        {{-- Contract Dates --}}
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label mb-1"><strong>Start Date</strong></label>
                                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm"
                                       value="{{ $contract->start_date ? $contract->start_date->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-1"><strong>End Date</strong></label>
                                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm"
                                       value="{{ $contract->end_date ? $contract->end_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- CONFIG TOGGLE --}}
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" onclick="toggleConfig()">
                            <i class="bi bi-gear me-1"></i>Contract Configurations
                            <i class="bi bi-chevron-up ms-1" id="configChevron"></i>
                        </button>

                        {{-- CONFIG SECTION (Open by default in edit) --}}
                        <div id="configSection">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Line items (for reference)</small>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="addRow()">
                                    <i class="bi bi-plus"></i> Add
                                </button>
                            </div>

                            {{-- Config Table --}}
                            <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                                <table class="table table-bordered table-sm mb-0" id="configTable">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th style="font-size: 11px;">Category</th>
                                            <th style="font-size: 11px; width: 70px;">Qty</th>
                                            <th style="font-size: 11px; width: 80px;">Unit</th>
                                            <th style="font-size: 11px; width: 80px;">Rate</th>
                                            <th style="width: 30px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="configTableBody"></tbody>
                                </table>
                            </div>

                            {{-- Contract Value --}}
                            <div class="mt-3 p-2 bg-light rounded">
                                <label class="form-label mb-1"><strong>Contract Value <span class="text-danger">*</span></strong></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="contractValue" name="contract_value" 
                                           value="{{ $contract->contract_value }}"
                                           placeholder="Enter total contract value" min="0" step="0.01" required>
                                </div>
                                <small class="text-muted">Enter manually - NOT auto-calculated</small>
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- Buttons --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-sm" id="saveBtn">
                                <i class="bi bi-check-lg me-1"></i>Update Contract
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const API_BASE = '/api/admin/contracts';
    const contractId = '{{ $contract->id }}';
    let categories = @json($categories);
    let existingItems = @json($contract->items);
    let rowCounter = 0;
    let configOpen = true;

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
        // Load existing items
        if (existingItems && existingItems.length > 0) {
            existingItems.forEach(item => addRow(item));
        } else {
            addRow();
        }

        // Template change → Preview
        $('#agreementTemplate').on('change', function() {
            const file = this.value;
            if (file) {
                $('#agreementPreview').attr('src', "{{ route('contracts.preview') }}?file=" + encodeURIComponent(file));
                $('#previewFileName').text(file);
            }
        });

        // Company change → Auto-fill
        $('#company_id').on('change', function() {
            const opt = this.selectedOptions[0];
            $('#company_cin').val(opt?.getAttribute('data-cin') || '');
            $('#company_address').val(opt?.getAttribute('data-address') || '');
        });

        // Vendor change → Auto-fill
        $('#vendor_id').on('change', function() {
            const opt = this.selectedOptions[0];
            $('#vendor_name').val(opt?.getAttribute('data-name') || '');
            $('#vendor_cin').val(opt?.getAttribute('data-cin') || '');
            $('#vendor_address').val(opt?.getAttribute('data-address') || '');
        });

        // Form submit
        $('#contractForm').on('submit', handleSubmit);
    });

    // =====================================================
    // TOGGLE CONFIG
    // =====================================================
    function toggleConfig() {
        configOpen = !configOpen;
        $('#configSection').toggleClass('d-none', !configOpen);
        $('#configChevron').toggleClass('bi-chevron-down', !configOpen).toggleClass('bi-chevron-up', configOpen);
    }

    // =====================================================
    // ADD ROW
    // =====================================================
    function addRow(existingItem = null) {
        rowCounter++;
        const rowId = `row_${rowCounter}`;

        let catOptions = '<option value="">Select</option>';
        categories.forEach(c => {
            const selected = existingItem && existingItem.category_id == c.id ? 'selected' : '';
            catOptions += `<option value="${c.id}" ${selected}>${c.name}</option>`;
        });

        let unitOptions = '';
        UNITS.forEach(u => {
            const selected = existingItem && existingItem.unit == u.value ? 'selected' : '';
            unitOptions += `<option value="${u.value}" ${selected}>${u.label}</option>`;
        });

        const qty = existingItem ? existingItem.quantity : '';
        const rate = existingItem ? existingItem.rate : '';

        const row = `
            <tr id="${rowId}">
                <td>
                    <select class="form-select form-select-sm cat-select" style="font-size:11px;" required>
                        ${catOptions}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm qty-input" style="font-size:11px;" 
                           value="${qty}" placeholder="0" min="0" step="0.01" required>
                </td>
                <td>
                    <select class="form-select form-select-sm unit-select" style="font-size:11px;" required>
                        ${unitOptions}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm rate-input" style="font-size:11px;" 
                           value="${rate}" placeholder="0" min="0" step="0.01" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeRow('${rowId}')">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#configTableBody').append(row);
    }

    // =====================================================
    // REMOVE ROW
    // =====================================================
    function removeRow(rowId) {
        if ($('#configTableBody tr').length <= 1) {
            showAlert('warning', 'At least one item required');
            return;
        }
        $(`#${rowId}`).remove();
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

        // Check config rows
        const rows = $('#configTableBody tr').length;
        if (rows === 0) {
            showAlert('danger', 'Please add at least one configuration item.');
            return;
        }

        // Validate contract value
        const contractValue = $('#contractValue').val();
        if (!contractValue || parseFloat(contractValue) <= 0) {
            showAlert('danger', 'Please enter the contract value.');
            return;
        }

        // Collect items
        const items = [];
        let hasError = false;

        $('#configTableBody tr').each(function() {
            const categoryId = $(this).find('.cat-select').val();
            const quantity = $(this).find('.qty-input').val();
            const unit = $(this).find('.unit-select').val();
            const rate = $(this).find('.rate-input').val();

            if (!categoryId || !quantity || !unit || !rate) {
                hasError = true;
                return false;
            }

            items.push({
                category_id: categoryId,
                quantity: parseFloat(quantity),
                unit: unit,
                rate: parseFloat(rate)
            });
        });

        if (hasError) {
            showAlert('danger', 'Please fill all fields in configuration items.');
            return;
        }

        // Prepare data
        const data = {
            template_file: $('#agreementTemplate').val(),
            company_id: $('#company_id').val() || null,
            company_cin: $('#company_cin').val(),
            company_address: $('#company_address').val(),
            vendor_id: $('#vendor_id').val(),
            vendor_name: $('#vendor_name').val(),
            vendor_cin: $('#vendor_cin').val(),
            vendor_address: $('#vendor_address').val(),
            start_date: $('#start_date').val() || null,
            end_date: $('#end_date').val() || null,
            contract_value: parseFloat(contractValue),
            items: items
        };

        // Disable button
        const btn = $('#saveBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');

        // Update contract
        axios.put(`${API_BASE}/${contractId}`, data)
            .then(response => {
                if (response.data.success) {
                    showAlert('success', 'Contract updated successfully!');
                    setTimeout(() => {
                        window.location.href = '{{ route("contracts.index") }}';
                    }, 1500);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.response?.data?.errors) {
                    const errors = Object.values(error.response.data.errors).flat();
                    showAlert('danger', errors.join('<br>'));
                } else {
                    showAlert('danger', error.response?.data?.message || 'Failed to update contract');
                }
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Update Contract');
            });
    }

    // =====================================================
    // SHOW ALERT
    // =====================================================
    function showAlert(type, message) {
        const icon = type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle');
        $('#alertContainer').html(`
            <div class="alert alert-${type} alert-dismissible fade show py-2" role="alert">
                <i class="bi bi-${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
    }
</script>
@endpush