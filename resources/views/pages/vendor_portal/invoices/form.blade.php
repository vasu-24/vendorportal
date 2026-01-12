@extends('layouts.Vendor')
@section('title', $mode == 'create' ? 'Create Invoice' : ($mode == 'edit' ? 'Edit Invoice' : 'View Invoice'))

@section('content')
<div class="container-fluid">

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: var(--primary-blue);">
                @if($mode == 'create')
                    <i class="bi bi-plus-circle me-2"></i>Upload Invoice
                    <span id="invoiceTypeBadge" class="badge bg-primary ms-2" style="font-size: 12px;"></span>
                @elseif($mode == 'edit')
                    <i class="bi bi-pencil me-2"></i>Edit Invoice
                @else
                    <i class="bi bi-receipt me-2"></i>Invoice Details
                @endif
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('vendor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vendor.invoices.index') }}">Invoices</a></li>
                    <li class="breadcrumb-item active">
                        {{ $mode == 'create' ? 'Create' : ($mode == 'edit' ? 'Edit' : 'View') }}
                    </li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('vendor.invoices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <!-- Loading Spinner -->
    @if($mode != 'create')
    <div id="loadingSpinner" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Loading invoice details...</p>
    </div>
    @endif

    <!-- Main Content -->
    <div id="mainContent" @if($mode != 'create') style="display: none;" @endif>
        
        <!-- Rejection Notice -->
        <div id="rejectionNotice" class="alert alert-danger align-items-start mb-4 d-none">
            <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
            <div>
                <strong>Invoice Rejected</strong>
                <p class="mb-0 mt-1" id="rejectionReason"></p>
                <small class="text-muted">Please fix the issues and resubmit.</small>
            </div>
        </div>

        <form id="invoiceForm" enctype="multipart/form-data">
            <!-- Hidden field for invoice type -->
            <input type="hidden" name="invoice_type" id="invoiceType" value="normal">
            
            <div class="row">
                <div class="col-lg-8">
                    
                    <!-- Invoice Details Card -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-info-circle me-2"></i>Invoice Details
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label mb-1 small">Contract <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="contract_id" id="contractId" required>
                                        <option value="">-- Select --</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 small">Invoice Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="invoice_number" id="invoiceNumber" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 small">Invoice Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm" name="invoice_date" id="invoiceDate" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1 small">Due Date</label>
                                    <input type="date" class="form-control form-control-sm" name="due_date" id="dueDate">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items Card -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-list-ul me-2"></i>Line Items
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addLineItemBtn" onclick="addNewLineItem()">
                                <i class="bi bi-plus me-1"></i>Add Item
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" id="lineItemsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 35px; font-size: 11px;">#</th>
                                            <th style="width: 150px; font-size: 11px;">Category <span class="text-danger">*</span></th>
                                            <th style="font-size: 11px;">Particulars</th>
                                            <th style="width: 70px; font-size: 11px;">SAC</th>
                                            <th style="width: 70px; font-size: 11px;">Qty <span class="text-danger">*</span></th>
                                            <th style="width: 70px; font-size: 11px;">UOM</th>
                                            <th style="width: 90px; font-size: 11px;">Rate <span class="text-danger">*</span></th>
                                            <th style="width: 100px; font-size: 11px;">Amount</th>
                                            <th style="width: 30px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="lineItemsBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Totals Card -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-calculator me-2"></i>Totals
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3 align-items-end">
                                <!-- Base Total -->
                                <div class="col-md-3">
                                    <label class="form-label mb-1 small">Base Total <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" class="form-control bg-light" name="base_total" id="baseTotal" step="0.01" readonly>
                                    </div>
                                </div>

                                <!-- GST Rate -->
                                <div class="col-md-2">
                                    <label class="form-label mb-1 small">GST Rate <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" name="gst_percent" id="gstPercent" required>
                                        <option value="18" selected>18%</option>
                                        <option value="5">5%</option>
                                        <option value="12">12%</option>
                                        <option value="28">28%</option>
                                        <option value="0">0%</option>
                                    </select>
                                </div>

                                <!-- GST Amount -->
                                <div class="col-md-2">
                                    <label class="form-label mb-1 small">GST Amount</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" class="form-control bg-light" name="gst_total" id="gstTotal" step="0.01" readonly>
                                    </div>
                                </div>

                                <!-- Grand Total -->
                                <div class="col-md-2">
                                    <label class="form-label mb-1 small">Grand Total</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" class="form-control bg-light fw-bold" name="grand_total" id="grandTotal" step="0.01" readonly>
                                    </div>
                                </div>

                                <!-- TDS Rate -->
                                <div class="col-md-1">
                                    <label class="form-label mb-1 small">TDS %</label>
                                    <input type="number" class="form-control form-control-sm" name="tds_percent" id="tdsPercent" step="0.01" value="5">
                                </div>

                                <!-- TDS Amount -->
                                <div class="col-md-2">
                                    <label class="form-label mb-1 small">TDS Amount</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" class="form-control bg-light text-danger" name="tds_amount" id="tdsAmount" step="0.01" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments Card -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-paperclip me-2"></i>Attachments
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label mb-1 small">Invoice Document <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control form-control-sm" name="invoice_attachment" id="invoiceAttachment" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="text-muted">PDF, JPG, PNG (Max: 10MB)</small>
                                    <div id="currentInvoiceAttachment" class="mt-2" style="display: none;"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mb-1 small">Description / Notes</label>
                                    <textarea class="form-control form-control-sm" name="description" id="description" rows="2" placeholder="Any additional notes..."></textarea>
                                </div>
                            </div>

                            <!-- Timesheet Section - ONLY FOR NORMAL INVOICES -->
                            <div id="timesheetSection" class="border-top pt-3 mt-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="includeTimesheet" name="include_timesheet" value="1">
                                    <label class="form-check-label small" for="includeTimesheet">
                                        <i class="bi bi-clock-history me-1"></i>Include Timesheet
                                    </label>
                                </div>

                                <div id="timesheetOptions" class="row g-3 mt-2" style="display: none;">
                                    <div class="col-md-6">
                                        <label class="form-label mb-1 small">Step 1: Download Template</label>
                                        <a href="{{ asset('templates/timesheet_template.xlsx') }}" class="btn btn-outline-success btn-sm w-100" download>
                                            <i class="bi bi-download me-1"></i>Download Excel Template
                                        </a>
                                        <small class="text-muted d-block mt-1">Fill data, don't change columns</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label mb-1 small">Step 2: Upload Filled Timesheet</label>
                                        <input type="file" class="form-control form-control-sm" name="timesheet_attachment" id="timesheetFile" accept=".xlsx,.xls">
                                        <div id="timesheetStatus" class="mt-1 small"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Contract Info Card (for ADHOC) -->
                    <div class="card shadow-sm border-0 mb-3" id="contractInfoCard" style="display: none;">
                        <div class="card-header bg-warning text-dark py-2">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-lightning me-2"></i>ADHOC Contract Info
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted small py-1">SOW Value</td>
                                    <td class="text-end fw-bold py-1" id="contractSowValue">₹0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small py-1">Invoiced Till Date</td>
                                    <td class="text-end py-1" id="contractInvoiced">₹0.00</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-muted small py-1">Remaining</td>
                                    <td class="text-end fw-bold text-success py-1" id="contractRemaining">₹0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header bg-white py-2">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-receipt me-2"></i>Summary
                            </h6>
                        </div>
                        <div class="card-body py-3">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted small py-1">Line Items</td>
                                    <td class="text-end small py-1" id="summaryItems">0</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small py-1">Base Total</td>
                                    <td class="text-end small py-1" id="summaryBase">₹0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small py-1">GST (<span id="summaryGstPercent">18</span>%)</td>
                                    <td class="text-end small py-1" id="summaryGst">₹0.00</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="fw-bold py-2">Grand Total</td>
                                    <td class="text-end fw-bold py-2" id="summaryGrandTotal">₹0.00</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small py-1">TDS (<span id="summaryTdsPercent">5</span>%)</td>
                                    <td class="text-end small text-danger py-1" id="summaryTds">-₹0.00</td>
                                </tr>
                                <tr class="border-top bg-light">
                                    <td class="fw-bold text-success py-2">Net Payable</td>
                                    <td class="text-end fw-bold text-success py-2" id="summaryNetPayable">₹0.00</td>
                                </tr>
                            </table>
                            <input type="hidden" name="net_payable" id="netPayable" value="0">
                        </div>
                    </div>

                    <!-- Submit Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body py-3">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bi bi-send me-2"></i>
                                    {{ $mode == 'edit' ? 'Resubmit' : 'Submit Invoice' }}
                                </button>
                                <a href="{{ route('vendor.invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                                    Cancel
                                </a>
                            </div>
                            <p class="text-center text-muted small mt-2 mb-0">
                                <i class="bi bi-info-circle me-1"></i>Once submitted, cannot be edited.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
    </div>

</div>
@endsection


@push('styles')
<style>
/* SUPER COMPACT EXCEED ROW STYLES */
.compact-exceed-row {
    display: none;
}
.compact-exceed-row.show {
    display: table-row;
}
.compact-exceed-cell {
    padding: 8px !important;
    background: #fffbeb;
    border-left: 3px solid #f59e0b;
    border-bottom: 1px solid #fbbf24;
}
.compact-content {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 10px;
}
.comparison-inline {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.compare-item {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    background: white;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
}
.compare-label {
    font-weight: 600;
    color: #6b7280;
    font-size: 9px;
    text-transform: uppercase;
}
.contract-val {
    color: #059669;
    font-weight: 700;
    font-size: 11px;
}
.arrow {
    color: #9ca3af;
    font-size: 10px;
}
.invoice-val {
    color: #dc2626;
    font-weight: 700;
    font-size: 11px;
}
.diff-val {
    color: #92400e;
    font-weight: 700;
    background: #fef3c7;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 9px;
}
.reason-compact {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 6px;
}
.reason-compact label {
    font-size: 9px;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 3px;
}
.reason-compact i {
    color: #8b5cf6;
    font-size: 11px;
}
.reason-input {
    width: 100%;
    padding: 5px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 10px;
    resize: none;
    height: 32px;
    font-family: inherit;
}
.reason-input:focus {
    outline: none;
    border-color: #8b5cf6;
    box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.1);
}
.reason-input::placeholder {
    color: #9ca3af;
    font-size: 10px;
}
.save-btn-compact {
    padding: 5px 10px;
    background: #10b981;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}
.save-btn-compact:hover {
    background: #059669;
}
.input-exceeded {
    border-color: #f59e0b !important;
    background: #fffbeb !important;
}
.warning-icon-inline {
    color: #f59e0b;
    font-size: 12px;
    margin-left: 4px;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
</style>
@endpush



@push('scripts')
<script>
const API_BASE = '/api/vendor/invoices';
const CONTRACT_API = '/api/vendor/contracts';
const ZOHO_API = '/api/zoho';
const MODE = '{{ $mode }}';
const INVOICE_ID = '{{ $invoiceId ?? "" }}';

const urlParams = new URLSearchParams(window.location.search);
const URL_CONTRACT_ID = urlParams.get('contract_id');
const URL_INVOICE_TYPE = urlParams.get('type') || 'normal'; // 'normal' or 'adhoc'

let contractItems = [];
let allCategories = [];
let rowCounter = 0;
let isAdhocInvoice = URL_INVOICE_TYPE === 'adhoc';
let currentContract = null;
// Exceed notes data structure
let exceedNotesData = {
    items: [],
    contract: null
};

// =====================================================
// INITIALIZATION
// =====================================================
$(document).ready(function() {
    // Set invoice type
    $('#invoiceType').val(URL_INVOICE_TYPE);
    
    // Update UI based on invoice type
    updateUIForInvoiceType();
    
    loadContracts();
    loadZohoTaxes();
    loadAllCategories();

    if ((MODE === 'edit' || MODE === 'show') && INVOICE_ID) {
        loadInvoiceData();
    }

    // Event listeners
    $('#contractId').on('change', handleContractChange);
    $('#gstPercent').on('change', calculateTotals);
    $('#tdsPercent').on('input', calculateTotals);
    $('#invoiceForm').on('submit', handleFormSubmit);

    // Timesheet toggle
    $('#includeTimesheet').on('change', function() {
        $('#timesheetOptions').toggle($(this).is(':checked'));
    });

    // Timesheet file validation
    $('#timesheetFile').on('change', handleTimesheetUpload);
});

// =====================================================
// UPDATE UI FOR INVOICE TYPE
// =====================================================
function updateUIForInvoiceType() {
    if (isAdhocInvoice) {
        // ADHOC Invoice
        $('#invoiceTypeBadge').text('ADHOC').removeClass('bg-primary').addClass('bg-warning text-dark');
        $('#timesheetSection').hide(); // Hide timesheet for ADHOC
        $('#contractInfoCard').show(); // Show SOW info card
    } else {
        // Normal Invoice
        $('#invoiceTypeBadge').text('Normal').removeClass('bg-warning text-dark').addClass('bg-primary');
        $('#timesheetSection').show(); // Show timesheet for Normal
        $('#contractInfoCard').hide(); // Hide SOW info card
    }
}

// =====================================================
// LOAD ALL CATEGORIES (for Add Item dropdown)
// =====================================================
function loadAllCategories() {
    axios.get('/api/vendor/categories')
        .then(response => {
            if (response.data.success) {
                allCategories = response.data.data;
            }
        })
        .catch(error => {
            console.log('Failed to load categories, using contract items only');
        });
}

// =====================================================
// LOAD ZOHO TAXES (GST)
// =====================================================
function loadZohoTaxes() {
    axios.get(`${ZOHO_API}/taxes`)
        .then(response => {
            if (response.data.success && response.data.data) {
                const taxes = response.data.data;
                
                if (taxes.gst && taxes.gst.length > 0) {
                    let html = '';
                    taxes.gst.forEach(tax => {
                        const selected = tax.tax_percentage == 18 ? 'selected' : '';
                        html += `<option value="${tax.tax_percentage}" data-tax-id="${tax.tax_id}" ${selected}>${tax.tax_name || tax.tax_percentage + '%'}</option>`;
                    });
                    html += '<option value="0">No GST (0%)</option>';
                    $('#gstPercent').html(html);
                }
            }
        })
        .catch(error => {
            console.log('Using default GST options');
        });
}

// =====================================================
// LOAD CONTRACTS (filtered by type)
// =====================================================
function loadContracts() {
    axios.get(`${CONTRACT_API}/dropdown`)
        .then(response => {
            if (response.data.success) {
                let options = '<option value="">-- Select Contract --</option>';
                
                response.data.data.forEach(contract => {
                    // Filter: Show only matching contract type
                    const contractType = contract.contract_type || 'normal';
                    
                    if ((isAdhocInvoice && contractType === 'adhoc') || (!isAdhocInvoice && contractType !== 'adhoc')) {
                        const selected = (URL_CONTRACT_ID && URL_CONTRACT_ID == contract.id) ? 'selected' : '';
                        const valueDisplay = contractType === 'adhoc' 
                            ? `SOW: ₹${formatNumber(contract.sow_value)}`
                            : `₹${formatNumber(contract.contract_value)}`;
                        options += `<option value="${contract.id}" 
                                        data-contract-type="${contractType}"
                                        data-contract-value="${contract.contract_value || 0}"
                                        data-sow-value="${contract.sow_value || 0}"
                                        ${selected}>
                                        ${contract.contract_number} (${valueDisplay})
                                   </option>`;
                    }
                });
                
                $('#contractId').html(options);

                if (URL_CONTRACT_ID) handleContractChange();
            }
        })
        .catch(error => console.error('Failed to load contracts:', error));
}

// =====================================================
// HANDLE CONTRACT CHANGE
// =====================================================
function handleContractChange() {
    const contractId = $('#contractId').val();
    const selectedOption = $('#contractId option:selected');
    
    if (!contractId) {
        contractItems = [];
        currentContract = null;
        $('#lineItemsBody').empty();
        $('#contractInfoCard').hide();
        calculateBaseTotal();
        return;
    }

    // Get contract info from selected option
    currentContract = {
        id: contractId,
        contract_type: selectedOption.data('contract-type'),
        contract_value: parseFloat(selectedOption.data('contract-value')) || 0,
        sow_value: parseFloat(selectedOption.data('sow-value')) || 0
    };

    // Show contract info for ADHOC
    if (isAdhocInvoice && currentContract.contract_type === 'adhoc') {
        $('#contractInfoCard').show();
        $('#contractSowValue').text('₹' + formatNumber(currentContract.sow_value));
        // TODO: Load invoiced amount from API
        $('#contractInvoiced').text('₹0.00');
        $('#contractRemaining').text('₹' + formatNumber(currentContract.sow_value));
    }

    axios.get(`${CONTRACT_API}/${contractId}/items`)
        .then(response => {
            if (response.data.success) {
                contractItems = response.data.data;
                console.log('Contract Items:', contractItems);
                $('#lineItemsBody').empty();
                
                if (contractItems.length > 0) {
                    contractItems.forEach(item => addLineItemFromContract(item));
                }
                calculateBaseTotal();
            }
        })
        .catch(error => {
            console.error('Failed to load contract items:', error);
            showAlert('danger', 'Failed to load contract categories');
        });
}

// =====================================================
// ADD LINE ITEM FROM CONTRACT (Category from Contract)
// =====================================================
function addLineItemFromContract(contractItem) {
    rowCounter++;
    const rowId = `row_${rowCounter}`;

    const row = `
        <tr id="${rowId}" 
            data-contract-item-id="${contractItem.id}" 
            data-tag-id="${contractItem.tag_id || ''}" 
            data-tag-name="${contractItem.tag_name || ''}">
            <td class="text-center align-middle" style="font-size: 11px;">
                <span class="row-number">${$('#lineItemsBody tr').length + 1}</span>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm bg-light category-display" 
                       style="font-size: 11px;" value="${contractItem.category_name}" readonly>
                <input type="hidden" class="contract-item-id" value="${contractItem.id}">
                <input type="hidden" class="category-id" value="${contractItem.category_id}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm particulars-input" style="font-size: 11px;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm sac-input" style="font-size: 11px;">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm qty-input" style="font-size: 11px;" step="0.01" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm uom-input" style="font-size: 11px;">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm rate-input" style="font-size: 11px;" step="0.01" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm amount-input bg-light" style="font-size: 11px;" step="0.01" readonly>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeLineItem('${rowId}')">
                    <i class="bi bi-x"></i>
                </button>
            </td>
        </tr>
    `;

    $('#lineItemsBody').append(row);
    bindLineItemEvents(rowId);
    updateRowNumbers();
}

// =====================================================
// ADD NEW LINE ITEM (Vendor can add extra categories)
// =====================================================
function addNewLineItem() {
    rowCounter++;
    const rowId = `row_${rowCounter}`;

    // Build category options from contract items + all categories
    let categoryOptions = '<option value="">-- Select --</option>';
    
    // First add contract items
    contractItems.forEach(item => {
        categoryOptions += `<option value="${item.id}" 
                                data-category-id="${item.category_id}"
                                data-tag-id="${item.tag_id || ''}"
                                data-tag-name="${item.tag_name || ''}"
                                data-is-contract-item="true">
                                ${item.category_name}
                           </option>`;
    });
    
    // Add separator if we have both
    if (contractItems.length > 0 && allCategories.length > 0) {
        categoryOptions += '<option disabled>──────────</option>';
    }
    
    // Add all categories (that are not already in contract)
    const contractCategoryIds = contractItems.map(i => i.category_id);
    allCategories.forEach(cat => {
        if (!contractCategoryIds.includes(cat.id)) {
            categoryOptions += `<option value="new_${cat.id}" 
                                    data-category-id="${cat.id}"
                                    data-is-contract-item="false">
                                    ${cat.name} (New)
                               </option>`;
        }
    });

    const row = `
        <tr id="${rowId}" data-contract-item-id="" data-tag-id="" data-tag-name="">
            <td class="text-center align-middle" style="font-size: 11px;">
                <span class="row-number">${$('#lineItemsBody tr').length + 1}</span>
            </td>
            <td>
                <select class="form-select form-select-sm category-select" style="font-size: 11px;" onchange="handleCategoryChange('${rowId}', this)">
                    ${categoryOptions}
                </select>
                <input type="hidden" class="contract-item-id" value="">
                <input type="hidden" class="category-id" value="">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm particulars-input" style="font-size: 11px;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm sac-input" style="font-size: 11px;">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm qty-input" style="font-size: 11px;" step="0.01" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm uom-input" style="font-size: 11px;">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm rate-input" style="font-size: 11px;" step="0.01" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm amount-input bg-light" style="font-size: 11px;" step="0.01" readonly>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeLineItem('${rowId}')">
                    <i class="bi bi-x"></i>
                </button>
            </td>
        </tr>
    `;

    $('#lineItemsBody').append(row);
    bindLineItemEvents(rowId);
    updateRowNumbers();
}

// =====================================================
// HANDLE CATEGORY CHANGE (for added items)
// =====================================================
function handleCategoryChange(rowId, selectEl) {
    const row = $(`#${rowId}`);
    const selectedOption = $(selectEl).find('option:selected');
    
    const isContractItem = selectedOption.data('is-contract-item') === true;
    const contractItemId = isContractItem ? selectedOption.val() : '';
    const categoryId = selectedOption.data('category-id') || '';
    const tagId = selectedOption.data('tag-id') || '';
    const tagName = selectedOption.data('tag-name') || '';
    
    row.find('.contract-item-id').val(contractItemId);
    row.find('.category-id').val(categoryId);
    row.data('contract-item-id', contractItemId);
    row.data('tag-id', tagId);
    row.data('tag-name', tagName);
}

// =====================================================
// ADD LINE ITEM (For Edit Mode)
// =====================================================
function addLineItem(data = null) {
    rowCounter++;
    const rowId = `row_${rowCounter}`;

    let categoryName = '';
    let tagId = '';
    let tagName = '';
    
    if (data && data.contract_item_id) {
        const contractItem = contractItems.find(item => item.id == data.contract_item_id);
        if (contractItem) {
            categoryName = contractItem.category_name;
            tagId = contractItem.tag_id || '';
            tagName = contractItem.tag_name || '';
        }
    }

    const row = `
        <tr id="${rowId}" 
            data-contract-item-id="${data?.contract_item_id || ''}" 
            data-tag-id="${tagId}" 
            data-tag-name="${tagName}">
            <td class="text-center align-middle" style="font-size: 11px;">
                <span class="row-number">${$('#lineItemsBody tr').length + 1}</span>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm bg-light category-display" 
                       style="font-size: 11px;" value="${categoryName}" readonly>
                <input type="hidden" class="contract-item-id" value="${data?.contract_item_id || ''}">
                <input type="hidden" class="category-id" value="${data?.category_id || ''}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm particulars-input" style="font-size: 11px;" value="${data?.particulars || ''}">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm sac-input" style="font-size: 11px;" value="${data?.sac || ''}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm qty-input" style="font-size: 11px;" step="0.01" value="${data?.quantity || ''}" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm uom-input" style="font-size: 11px;" value="${data?.unit || ''}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm rate-input" style="font-size: 11px;" step="0.01" value="${data?.rate || ''}" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm amount-input bg-light" style="font-size: 11px;" step="0.01" value="${data?.amount || ''}" readonly>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeLineItem('${rowId}')">
                    <i class="bi bi-x"></i>
                </button>
            </td>
        </tr>
    `;

    $('#lineItemsBody').append(row);
    bindLineItemEvents(rowId);
    updateRowNumbers();
}

// =====================================================
// BIND LINE ITEM EVENTS
// =====================================================
function bindLineItemEvents(rowId) {
    $(`#${rowId} .qty-input, #${rowId} .rate-input`).on('input', function() {
        calculateLineItemAmount(rowId);
    });
    
    // Validate on blur
    $(`#${rowId} .qty-input`).on('blur', function() {
        validateQuantity(rowId);
    });
    
    $(`#${rowId} .rate-input`).on('blur', function() {
        validateRate(rowId);
    });
}

// =====================================================
// CALCULATE LINE ITEM AMOUNT
// =====================================================
function calculateLineItemAmount(rowId) {
    const row = $(`#${rowId}`);
    const qty = parseFloat(row.find('.qty-input').val()) || 0;
    const rate = parseFloat(row.find('.rate-input').val()) || 0;
    const amount = qty * rate;
    
    row.find('.amount-input').val(amount.toFixed(2));
    calculateBaseTotal();
}

// =====================================================
// VALIDATE QUANTITY (Contract Exceed Check)
// =====================================================
function validateQuantity(rowId) {
    const row = $(`#${rowId}`);
    const contractItemId = row.find('.contract-item-id').val();
    
    if (!contractItemId) return; // Skip if no contract item
    
    const enteredQty = parseFloat(row.find('.qty-input').val()) || 0;
    const contractItem = contractItems.find(item => item.id == contractItemId);
    
    if (!contractItem || !contractItem.quantity) return;
    
    const contractQty = parseFloat(contractItem.quantity);
    const qtyInput = row.find('.qty-input');
    
    if (enteredQty > contractQty) {
        // EXCEEDS - Show compact panel
        qtyInput.addClass('input-exceeded');
        showCompactPanel(rowId, 'quantity', contractItem.category_name, contractQty, enteredQty);
    } else {
        // Within limits - Remove exceeded styling and panel
        qtyInput.removeClass('input-exceeded');
        removeExceedNote(rowId, 'quantity');
        hideCompactPanelIfNoExceeds(rowId);
    }
}

// =====================================================
// VALIDATE RATE (Contract Exceed Check)
// =====================================================
function validateRate(rowId) {
    const row = $(`#${rowId}`);
    const contractItemId = row.find('.contract-item-id').val();
    
    if (!contractItemId) return; // Skip if no contract item
    
    const enteredRate = parseFloat(row.find('.rate-input').val()) || 0;
    const contractItem = contractItems.find(item => item.id == contractItemId);
    
    if (!contractItem || !contractItem.rate) return;
    
    const contractRate = parseFloat(contractItem.rate);
    const rateInput = row.find('.rate-input');
    
    if (enteredRate > contractRate) {
        // EXCEEDS - Show compact panel
        rateInput.addClass('input-exceeded');
        showCompactPanel(rowId, 'rate', contractItem.category_name, contractRate, enteredRate);
    } else {
        // Within limits - Remove exceeded styling and panel
        rateInput.removeClass('input-exceeded');
        removeExceedNote(rowId, 'rate');
        hideCompactPanelIfNoExceeds(rowId);
    }
}

// =====================================================
// SHOW COMPACT PANEL
// =====================================================
function showCompactPanel(rowId, type, itemName, contractValue, enteredValue) {
    const row = $(`#${rowId}`);
    const panelId = `${rowId}_exceed_panel`;
    
    // Check if panel already exists
    let panelRow = $(`#${panelId}`);
    
    if (panelRow.length === 0) {
        // Create new panel row
        const panelHtml = `
            <tr id="${panelId}" class="compact-exceed-row show">
                <td colspan="9" class="compact-exceed-cell">
                    <div class="compact-content" id="${panelId}_content">
                        <!-- Content will be updated dynamically -->
                    </div>
                </td>
            </tr>
        `;
        row.after(panelHtml);
        panelRow = $(`#${panelId}`);
    }
    
    // Update panel content with current exceeds
    updateCompactPanelContent(rowId, panelId);
}

// =====================================================
// UPDATE COMPACT PANEL CONTENT
// =====================================================
function updateCompactPanelContent(rowId, panelId) {
    const row = $(`#${rowId}`);
    const contractItemId = row.find('.contract-item-id').val();
    const contractItem = contractItems.find(item => item.id == contractItemId);
    
    if (!contractItem) return;
    
    const enteredQty = parseFloat(row.find('.qty-input').val()) || 0;
    const enteredRate = parseFloat(row.find('.rate-input').val()) || 0;
    const contractQty = parseFloat(contractItem.quantity) || 0;
    const contractRate = parseFloat(contractItem.rate) || 0;
    
    const qtyExceeds = enteredQty > contractQty;
    const rateExceeds = enteredRate > contractRate;
    
    let comparisonHtml = '<div class="comparison-inline">';
    
    // Quantity comparison
    if (qtyExceeds) {
        const qtyDiff = enteredQty - contractQty;
        comparisonHtml += `
            <div class="compare-item">
                <span class="compare-label">Qty:</span>
                <span class="contract-val">${contractQty}</span>
                <span class="arrow">→</span>
                <span class="invoice-val">${enteredQty}</span>
                <span class="diff-val">+${qtyDiff.toFixed(2)}</span>
            </div>
        `;
    } else {
        comparisonHtml += `
            <div class="compare-item">
                <span class="compare-label">Qty:</span>
                <span class="contract-val">${contractQty}</span>
                <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 10px;"></i>
            </div>
        `;
    }
    
    // Rate comparison
    if (rateExceeds) {
        const rateDiff = enteredRate - contractRate;
        comparisonHtml += `
            <div class="compare-item">
                <span class="compare-label">Rate:</span>
                <span class="contract-val">₹${contractRate}</span>
                <span class="arrow">→</span>
                <span class="invoice-val">₹${enteredRate}</span>
                <span class="diff-val">+₹${rateDiff.toFixed(2)}</span>
            </div>
        `;
    } else {
        comparisonHtml += `
            <div class="compare-item">
                <span class="compare-label">Rate:</span>
                <span class="contract-val">₹${contractRate}</span>
                <i class="bi bi-check-circle-fill" style="color: #10b981; font-size: 10px;"></i>
            </div>
        `;
    }
    
    comparisonHtml += '</div>';
    
    // Get existing note if any
    const existingNote = getExceedNote(rowId);
    const noteValue = existingNote ? existingNote.note : '';
    
    // Reason input
    const reasonHtml = `
        <div class="reason-compact">
            <label>
                <i class="bi bi-pencil-square"></i>
                Reason <span style="color: #dc2626;">*</span>
            </label>
            <input 
                type="text" 
                class="reason-input" 
                id="${panelId}_reason"
                placeholder="e.g., Urgent deadline - approved by manager"
                value="${noteValue}"
                required
            >
        </div>
    `;
    
    // Save button
    const saveButton = `
        <button class="save-btn-compact" type="button" onclick="saveExceedNote('${rowId}', '${contractItem.category_name}')">
            <i class="bi bi-check-lg"></i> Save
        </button>
    `;
    
    // Update content
    $(`#${panelId}_content`).html(comparisonHtml + reasonHtml + saveButton);
}

// =====================================================
// SAVE EXCEED NOTE
// =====================================================
function saveExceedNote(rowId, itemName) {
    const panelId = `${rowId}_exceed_panel`;
    
    // CHECK IF ALREADY SAVED - DON'T SAVE AGAIN
    const saveBtn = $(`#${panelId}`).find('.save-btn-compact');
    if (saveBtn.prop('disabled')) {
        return; // Already saved, exit
    }
    
    const note = $(`#${panelId}_reason`).val();
    
    if (!note || note.trim() === '') {
        showAlert('warning', 'Please provide a reason for exceeding contract values');
        return;
    }
    
    const row = $(`#${rowId}`);
    const contractItemId = row.find('.contract-item-id').val();
    const contractItem = contractItems.find(item => item.id == contractItemId);
    
    if (!contractItem) return;
    
    const enteredQty = parseFloat(row.find('.qty-input').val()) || 0;
    const enteredRate = parseFloat(row.find('.rate-input').val()) || 0;
    const contractQty = parseFloat(contractItem.quantity) || 0;
    const contractRate = parseFloat(contractItem.rate) || 0;
    
    // Remove existing notes for this row
    exceedNotesData.items = exceedNotesData.items.filter(item => item.row_id !== rowId);
    
    // Add quantity exceed note if applicable
    if (enteredQty > contractQty) {
        exceedNotesData.items.push({
            row_id: rowId,
            item_name: itemName,
            type: 'quantity_exceeded',
            contract_value: contractQty,
            entered_value: enteredQty,
            note: note
        });
    }
    
    // Add rate exceed note if applicable
    if (enteredRate > contractRate) {
        exceedNotesData.items.push({
            row_id: rowId,
            item_name: itemName,
            type: 'rate_exceeded',
            contract_value: contractRate,
            entered_value: enteredRate,
            note: note
        });
    }
    
    console.log('Exceed notes saved:', exceedNotesData);
    showAlert('success', 'Justification saved successfully!');

    // UPDATE BUTTON TO "SAVED" STATE - DISABLE IT PERMANENTLY
    saveBtn.html('<i class="bi bi-check-circle-fill"></i> Saved')
        .css({
            'background': '#059669',
            'opacity': '0.7',
            'cursor': 'not-allowed'
        })
        .prop('disabled', true)
        .off('click'); // Remove click event

    // ALSO DISABLE THE REASON INPUT
    $(`#${panelId}_reason`).prop('readonly', true)
        .css({
            'background': '#f9fafb',
            'cursor': 'not-allowed'
        });
}



// =====================================================
// GET EXCEED NOTE
// =====================================================
function getExceedNote(rowId) {
    return exceedNotesData.items.find(item => item.row_id === rowId);
}

// =====================================================
// REMOVE EXCEED NOTE
// =====================================================
function removeExceedNote(rowId, type) {
    exceedNotesData.items = exceedNotesData.items.filter(item => {
        if (item.row_id === rowId) {
            if (type === 'quantity' && item.type === 'quantity_exceeded') return false;
            if (type === 'rate' && item.type === 'rate_exceeded') return false;
        }
        return true;
    });
}

// =====================================================
// HIDE COMPACT PANEL IF NO EXCEEDS
// =====================================================
function hideCompactPanelIfNoExceeds(rowId) {
    const row = $(`#${rowId}`);
    const contractItemId = row.find('.contract-item-id').val();
    const contractItem = contractItems.find(item => item.id == contractItemId);
    
    if (!contractItem) return;
    
    const enteredQty = parseFloat(row.find('.qty-input').val()) || 0;
    const enteredRate = parseFloat(row.find('.rate-input').val()) || 0;
    const contractQty = parseFloat(contractItem.quantity) || 0;
    const contractRate = parseFloat(contractItem.rate) || 0;
    
    const qtyExceeds = enteredQty > contractQty;
    const rateExceeds = enteredRate > contractRate;
    
    // If neither exceeds, hide and remove the panel
    if (!qtyExceeds && !rateExceeds) {
        const panelId = `${rowId}_exceed_panel`;
        $(`#${panelId}`).remove();
    } else {
        // Update panel content to reflect current state
        const panelId = `${rowId}_exceed_panel`;
        updateCompactPanelContent(rowId, panelId);
    }
}

// =====================================================
// CALCULATE BASE TOTAL
// =====================================================
function calculateBaseTotal() {
    let baseTotal = 0;
    
 $('#lineItemsBody tr[id^="row_"]').each(function() {
    // Skip exceed panel rows
    if ($(this).hasClass('compact-exceed-row')) return;
    
    const amount = parseFloat($(this).find('.amount-input').val()) || 0;
    baseTotal += amount;
});
    
    $('#baseTotal').val(baseTotal.toFixed(2));
    calculateTotals();
}

// =====================================================
// CALCULATE TOTALS
// =====================================================
function calculateTotals() {
    const baseTotal = parseFloat($('#baseTotal').val()) || 0;
    const gstPercent = parseFloat($('#gstPercent').val()) || 0;
    const tdsPercent = parseFloat($('#tdsPercent').val()) || 0;

    const gstAmount = (baseTotal * gstPercent) / 100;
    const grandTotal = baseTotal + gstAmount;
    const tdsAmount = (baseTotal * tdsPercent) / 100;
    const netPayable = grandTotal - tdsAmount;

    $('#gstTotal').val(gstAmount.toFixed(2));
    $('#grandTotal').val(grandTotal.toFixed(2));
    $('#tdsAmount').val(tdsAmount.toFixed(2));
    $('#netPayable').val(netPayable.toFixed(2));

    updateSummary();
}

// =====================================================
// UPDATE SUMMARY
// =====================================================
function updateSummary() {
    const itemCount = $('#lineItemsBody tr').length;
    const baseTotal = parseFloat($('#baseTotal').val()) || 0;
    const gstPercent = parseFloat($('#gstPercent').val()) || 0;
    const gstAmount = parseFloat($('#gstTotal').val()) || 0;
    const grandTotal = parseFloat($('#grandTotal').val()) || 0;
    const tdsPercent = parseFloat($('#tdsPercent').val()) || 0;
    const tdsAmount = parseFloat($('#tdsAmount').val()) || 0;
    const netPayable = parseFloat($('#netPayable').val()) || 0;

    $('#summaryItems').text(itemCount);
    $('#summaryBase').text('₹' + formatNumber(baseTotal));
    $('#summaryGstPercent').text(gstPercent);
    $('#summaryGst').text('₹' + formatNumber(gstAmount));
    $('#summaryGrandTotal').text('₹' + formatNumber(grandTotal));
    $('#summaryTdsPercent').text(tdsPercent);
    $('#summaryTds').text('-₹' + formatNumber(tdsAmount));
    $('#summaryNetPayable').text('₹' + formatNumber(netPayable));
}

// =====================================================
// REMOVE LINE ITEM
// =====================================================
function removeLineItem(rowId) {
    if ($('#lineItemsBody tr').length > 1) {
        $(`#${rowId}`).remove();
        updateRowNumbers();
        calculateBaseTotal();
    } else {
        showAlert('warning', 'At least one line item is required');
    }
}

// =====================================================
// UPDATE ROW NUMBERS
// =====================================================
function updateRowNumbers() {
  $('#lineItemsBody tr[id^="row_"]').each(function(index) {
    $(this).find('.row-number').text(index + 1);
});
}

// =====================================================
// HANDLE TIMESHEET UPLOAD
// =====================================================
function handleTimesheetUpload() {
    const file = this.files[0];
    const statusEl = $('#timesheetStatus');
    
    if (!file) {
        statusEl.html('');
        return;
    }

    const ext = file.name.split('.').pop().toLowerCase();
    if (!['xlsx', 'xls'].includes(ext)) {
        showAlert('danger', 'Only Excel files (.xlsx, .xls) allowed');
        $(this).val('');
        statusEl.html('');
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        showAlert('danger', 'File size must be less than 5MB');
        $(this).val('');
        statusEl.html('');
        return;
    }

    const fileSize = (file.size / 1024).toFixed(2) + ' KB';
    statusEl.html(`
        <span class="text-success">
            <i class="bi bi-check-circle me-1"></i>${file.name} (${fileSize})
        </span>
    `);
}

// =====================================================
// LOAD INVOICE DATA (Edit Mode)
// =====================================================
function loadInvoiceData() {
    axios.get(`${API_BASE}/${INVOICE_ID}`)
        .then(response => {
            if (response.data.success) {
                const invoice = response.data.data;
                
                // Set invoice type from loaded data
                isAdhocInvoice = invoice.invoice_type === 'adhoc';
                $('#invoiceType').val(invoice.invoice_type);
                updateUIForInvoiceType();
                
                if (MODE === 'edit') populateEditMode(invoice);
                $('#loadingSpinner').hide();
                $('#mainContent').show();
            }
        })
        .catch(error => {
            showAlert('danger', 'Failed to load invoice details.');
            $('#loadingSpinner').html('<p class="text-danger">Failed to load invoice.</p>');
        });
}

// =====================================================
// POPULATE EDIT MODE
// =====================================================
function populateEditMode(invoice) {
    const checkContracts = setInterval(() => {
        if ($('#contractId option').length > 1) {
            clearInterval(checkContracts);
            
            $('#contractId').val(invoice.contract_id);
            
            if (invoice.contract_id) {
                axios.get(`${CONTRACT_API}/${invoice.contract_id}/items`)
                    .then(response => {
                        if (response.data.success) {
                            contractItems = response.data.data;
                            $('#lineItemsBody').empty();
                            if (invoice.items && invoice.items.length > 0) {
                                invoice.items.forEach(item => addLineItem(item));
                            }
                            calculateBaseTotal();
                        }
                    });
            }
        }
    }, 100);

    $('#invoiceNumber').val(invoice.invoice_number);
    $('#invoiceDate').val(invoice.invoice_date);
    $('#dueDate').val(invoice.due_date || '');
    $('#gstPercent').val(invoice.gst_percent || 18);
    $('#tdsPercent').val(invoice.tds_percent || 5);
    $('#description').val(invoice.description || '');

    if (invoice.rejection_reason) {
        $('#rejectionReason').text(invoice.rejection_reason);
        $('#rejectionNotice').removeClass('d-none').addClass('d-flex');
    }

    if (invoice.attachments && invoice.attachments.length > 0) {
        const att = invoice.attachments.find(a => a.attachment_type === 'invoice');
        if (att) {
            $('#currentInvoiceAttachment').html(`<small class="text-muted"><i class="bi bi-file-earmark me-1"></i>${att.file_name}</small>`).show();
            $('#invoiceAttachment').removeAttr('required');
        }
    }
}

// =====================================================
// HANDLE FORM SUBMIT
// =====================================================
function handleFormSubmit(e) {
    e.preventDefault();
    
    if ($('#lineItemsBody tr').length === 0) {
        showAlert('danger', 'Please add at least one line item');
        return;
    }

    const items = [];
    let hasError = false;

  $('#lineItemsBody tr[id^="row_"]').each(function() {
    // Skip compact exceed panel rows
    if ($(this).hasClass('compact-exceed-row')) return;
    
    const row = $(this);
    const contractItemId = row.find('.contract-item-id').val();
        const categoryId = row.find('.category-id').val();
        const quantity = row.find('.qty-input').val();
        const rate = row.find('.rate-input').val();
        const amount = row.find('.amount-input').val();

        if (!quantity || !rate) {
            hasError = true;
            return false;
        }

        // For ADHOC, contract_item_id might be empty for new categories
        if (!contractItemId && !categoryId) {
            hasError = true;
            return false;
        }

        items.push({
            contract_item_id: contractItemId || null,
            category_id: categoryId || null,
            particulars: row.find('.particulars-input').val() || null,
            sac: row.find('.sac-input').val() || null,
            quantity: parseFloat(quantity),
            unit: row.find('.uom-input').val() || null,
            rate: parseFloat(rate),
            amount: parseFloat(amount),
            tag_id: row.data('tag-id') || null,
            tag_name: row.data('tag-name') || null
        });
    });

  if (hasError) {
    showAlert('danger', 'Please fill all required fields in line items');
    return;
}

// Check if any exceed panels need justification
let missingJustifications = false;
$('.compact-exceed-row.show').each(function() {
    const panelId = $(this).attr('id');
    const reasonInput = $(`#${panelId}_reason`);
    if (reasonInput.length > 0 && !reasonInput.val().trim()) {
        missingJustifications = true;
        reasonInput.addClass('border-danger');
    }
});

if (missingJustifications) {
    showAlert('danger', 'Please provide justification for all items that exceed contract values');
    return;
}

console.log('Submitting items:', items);
console.log('Exceed notes:', exceedNotesData);


    const formData = new FormData(document.getElementById('invoiceForm'));
    formData.append('items', JSON.stringify(items));
    formData.append('invoice_type', isAdhocInvoice ? 'adhoc' : 'normal');
    // Add exceed notes if any items exceed contract
if (exceedNotesData.items.length > 0 || exceedNotesData.contract) {
    formData.append('exceed_notes', JSON.stringify(exceedNotesData));
}


    const selectedGstOption = $('#gstPercent option:selected');
    if (selectedGstOption.data('tax-id')) {
        formData.append('zoho_gst_tax_id', selectedGstOption.data('tax-id'));
    }

    const submitBtn = $('#submitBtn');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

    let url = MODE === 'edit' ? `${API_BASE}/${INVOICE_ID}/update` : API_BASE;

    axios.post(url, formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(response => {
            if (response.data.success && MODE === 'create') {
                return axios.post(`${API_BASE}/${response.data.data.id}/submit`);
            }
            return response;
        })
        .then(response => {
            showAlert('success', MODE === 'edit' ? 'Invoice resubmitted successfully!' : 'Invoice submitted successfully!');
            setTimeout(() => window.location.href = '{{ route("vendor.invoices.index") }}', 1500);
        })
        .catch(error => {
            submitBtn.prop('disabled', false).html(originalText);
            showAlert('danger', error.response?.data?.message || 'Failed to submit invoice.');
        });
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================
function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en-IN', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
    });
}

function showAlert(type, message) {
    const icon = type === 'success' ? 'check-circle' : (type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle');
    $('#alertContainer').html(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi bi-${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
@endpush