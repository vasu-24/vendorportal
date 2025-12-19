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

        @if($mode == 'show')
            @include('pages.vendor_portal.invoices.partials.show')
        @else
            <form id="invoiceForm" enctype="multipart/form-data">
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
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="addLineItem()">
                                    <i class="bi bi-plus"></i> Add Row
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0" id="lineItemsTable">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 40px; font-size: 11px;">#</th>
                                                <th style="width: 150px; font-size: 11px;">Category <span class="text-danger">*</span></th>
                                                <th style="font-size: 11px;">Particulars</th>
                                                <th style="width: 80px; font-size: 11px;">SAC</th>
                                                <th style="width: 70px; font-size: 11px;">Qty <span class="text-danger">*</span></th>
                                                <th style="width: 70px; font-size: 11px;">UOM</th>
                                                <th style="width: 100px; font-size: 11px;">Rate <span class="text-danger">*</span></th>
                                                <th style="width: 110px; font-size: 11px;">Amount</th>
                                                <th style="width: 35px;"></th>
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

                                <!-- Timesheet Section -->
                                <div class="border-top pt-3 mt-3">
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
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    const API_BASE = '/api/vendor/invoices';
    const CONTRACT_API = '/api/vendor/contracts';
    const ZOHO_API = '/api/zoho';
    const MODE = '{{ $mode }}';
    const INVOICE_ID = '{{ $invoiceId ?? "" }}';
    
    const urlParams = new URLSearchParams(window.location.search);
    const URL_CONTRACT_ID = urlParams.get('contract_id');

    let contractItems = [];
    let rowCounter = 0;

    // =====================================================
    // INITIALIZATION
    // =====================================================
    $(document).ready(function() {
        loadContracts();
        loadZohoTaxes();

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
    // LOAD CONTRACTS
    // =====================================================
    function loadContracts() {
        axios.get(`${CONTRACT_API}/dropdown`)
            .then(response => {
                if (response.data.success) {
                    let options = '<option value="">-- Select Contract --</option>';
                    response.data.data.forEach(contract => {
                        const selected = (URL_CONTRACT_ID && URL_CONTRACT_ID == contract.id) ? 'selected' : '';
                        options += `<option value="${contract.id}" ${selected}>${contract.contract_number}</option>`;
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
        
        if (!contractId) {
            contractItems = [];
            $('#lineItemsBody').empty();
            calculateBaseTotal();
            return;
        }

        axios.get(`${CONTRACT_API}/${contractId}/items`)
            .then(response => {
                if (response.data.success) {
                    contractItems = response.data.data;
                    $('#lineItemsBody').empty();
                    
                    if (contractItems.length > 0) {
                        contractItems.forEach(item => addLineItemFromContract(item));
                    } else {
                        addLineItem();
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
    // ADD LINE ITEM FROM CONTRACT
    // =====================================================
    function addLineItemFromContract(contractItem) {
        rowCounter++;
        const rowId = `row_${rowCounter}`;

        let categoryOptions = '<option value="">Select</option>';
        contractItems.forEach(item => {
            const selected = (item.id == contractItem.id) ? 'selected' : '';
            categoryOptions += `<option value="${item.id}" ${selected}>${item.category_name}</option>`;
        });

        const row = `
            <tr id="${rowId}">
                <td class="text-center align-middle" style="font-size: 11px;"><span class="row-number">${$('#lineItemsBody tr').length + 1}</span></td>
                <td><select class="form-select form-select-sm category-select" style="font-size: 11px;" required>${categoryOptions}</select></td>
                <td><input type="text" class="form-control form-control-sm particulars-input" style="font-size: 11px;"></td>
                <td><input type="text" class="form-control form-control-sm sac-input" style="font-size: 11px;"></td>
                <td><input type="number" class="form-control form-control-sm qty-input" style="font-size: 11px;" step="0.01" required></td>
                <td><input type="text" class="form-control form-control-sm uom-input" style="font-size: 11px;"></td>
                <td><input type="number" class="form-control form-control-sm rate-input" style="font-size: 11px;" step="0.01" required></td>
                <td><input type="number" class="form-control form-control-sm amount-input bg-light" style="font-size: 11px;" step="0.01" readonly></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeLineItem('${rowId}')"><i class="bi bi-x"></i></button>
                </td>
            </tr>
        `;

        $('#lineItemsBody').append(row);
        bindLineItemEvents(rowId);
        updateRowNumbers();
    }

    // =====================================================
    // ADD EMPTY LINE ITEM
    // =====================================================
    function addLineItem(data = null) {
        rowCounter++;
        const rowId = `row_${rowCounter}`;

        let categoryOptions = '<option value="">Select</option>';
        contractItems.forEach(item => {
            const selected = (data && data.contract_item_id == item.id) ? 'selected' : '';
            categoryOptions += `<option value="${item.id}" ${selected}>${item.category_name}</option>`;
        });

        const row = `
            <tr id="${rowId}">
                <td class="text-center align-middle" style="font-size: 11px;"><span class="row-number">${$('#lineItemsBody tr').length + 1}</span></td>
                <td><select class="form-select form-select-sm category-select" style="font-size: 11px;" required>${categoryOptions}</select></td>
                <td><input type="text" class="form-control form-control-sm particulars-input" style="font-size: 11px;" value="${data?.particulars || ''}"></td>
                <td><input type="text" class="form-control form-control-sm sac-input" style="font-size: 11px;" value="${data?.sac || ''}"></td>
                <td><input type="number" class="form-control form-control-sm qty-input" style="font-size: 11px;" step="0.01" value="${data?.quantity || ''}" required></td>
                <td><input type="text" class="form-control form-control-sm uom-input" style="font-size: 11px;" value="${data?.unit || ''}"></td>
                <td><input type="number" class="form-control form-control-sm rate-input" style="font-size: 11px;" step="0.01" value="${data?.rate || ''}" required></td>
                <td><input type="number" class="form-control form-control-sm amount-input bg-light" style="font-size: 11px;" step="0.01" value="${data?.amount || ''}" readonly></td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeLineItem('${rowId}')"><i class="bi bi-x"></i></button>
                </td>
            </tr>
        `;

        $('#lineItemsBody').append(row);
        bindLineItemEvents(rowId);
        updateRowNumbers();
        calculateBaseTotal();
    }

    // =====================================================
    // BIND LINE ITEM EVENTS (Qty & Rate → Amount)
    // =====================================================
    function bindLineItemEvents(rowId) {
        $(`#${rowId} .qty-input, #${rowId} .rate-input`).on('input', function() {
            calculateLineItemAmount(rowId);
        });
    }

    // =====================================================
    // CALCULATE LINE ITEM AMOUNT (Qty × Rate)
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
    // CALCULATE BASE TOTAL (Sum of all line items)
    // =====================================================
    function calculateBaseTotal() {
        let baseTotal = 0;
        
        $('#lineItemsBody tr').each(function() {
            const amount = parseFloat($(this).find('.amount-input').val()) || 0;
            baseTotal += amount;
        });
        
        $('#baseTotal').val(baseTotal.toFixed(2));
        calculateTotals();
    }

    // =====================================================
    // CALCULATE TOTALS (GST, TDS, Net Payable)
    // =====================================================
    function calculateTotals() {
        const baseTotal = parseFloat($('#baseTotal').val()) || 0;
        const gstPercent = parseFloat($('#gstPercent').val()) || 0;
        const tdsPercent = parseFloat($('#tdsPercent').val()) || 0;

        // GST on Base Total
        const gstAmount = (baseTotal * gstPercent) / 100;
        
        // Grand Total = Base + GST
        const grandTotal = baseTotal + gstAmount;
        
        // TDS on Base Total (not Grand Total)
        const tdsAmount = (baseTotal * tdsPercent) / 100;
        
        // Net Payable = Grand Total - TDS
        const netPayable = grandTotal - tdsAmount;

        // Update fields
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
        $('#lineItemsBody tr').each(function(index) {
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

        // Check extension
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['xlsx', 'xls'].includes(ext)) {
            showAlert('danger', 'Only Excel files (.xlsx, .xls) allowed');
            $(this).val('');
            statusEl.html('');
            return;
        }

        // Check file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showAlert('danger', 'File size must be less than 5MB');
            $(this).val('');
            statusEl.html('');
            return;
        }

        // Show success
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
                                } else {
                                    addLineItem();
                                }
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

        // Show rejection notice
        if (invoice.rejection_reason) {
            $('#rejectionReason').text(invoice.rejection_reason);
            $('#rejectionNotice').removeClass('d-none').addClass('d-flex');
        }

        // Show current attachment
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
        
        // Validate line items
        if ($('#lineItemsBody tr').length === 0) {
            showAlert('danger', 'Please add at least one line item');
            return;
        }

        // Collect line items
        const items = [];
        let hasError = false;

        $('#lineItemsBody tr').each(function() {
            const categoryId = $(this).find('.category-select').val();
            const quantity = $(this).find('.qty-input').val();
            const rate = $(this).find('.rate-input').val();
            const amount = $(this).find('.amount-input').val();

            if (!categoryId || !quantity || !rate) {
                hasError = true;
                return false;
            }

            items.push({
                contract_item_id: categoryId,
                particulars: $(this).find('.particulars-input').val() || null,
                sac: $(this).find('.sac-input').val() || null,
                quantity: parseFloat(quantity),
                unit: $(this).find('.uom-input').val() || null,
                rate: parseFloat(rate),
                amount: parseFloat(amount)
            });
        });

        if (hasError) {
            showAlert('danger', 'Please fill all required fields in line items');
            return;
        }

        // Build form data
        const formData = new FormData(document.getElementById('invoiceForm'));
        formData.append('items', JSON.stringify(items));

        // Add Zoho GST Tax ID
        const selectedGstOption = $('#gstPercent option:selected');
        if (selectedGstOption.data('tax-id')) {
            formData.append('zoho_gst_tax_id', selectedGstOption.data('tax-id'));
        }

        // Show loading
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

        // Submit
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
