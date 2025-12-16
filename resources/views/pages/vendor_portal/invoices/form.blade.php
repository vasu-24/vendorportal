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
        <a href="{{ route('vendor.invoices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <!-- Loading Spinner (for show/edit modes) -->
    @if($mode != 'create')
    <div id="loadingSpinner" class="text-center py-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 text-muted">Loading invoice details...</p>
    </div>
    @endif

    <!-- Main Content -->
    <div id="mainContent" @if($mode != 'create') style="display: none;" @endif>
        
        <!-- Rejection Notice (for edit mode) -->
        <div id="rejectionNotice" class="alert alert-danger align-items-start mb-4 d-none">
            <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
            <div>
                <strong>Invoice Rejected</strong>
                <p class="mb-0 mt-1" id="rejectionReason"></p>
                <small class="text-muted">Please fix the issues and resubmit.</small>
            </div>
        </div>

        @if($mode == 'show')
            <!-- =====================================================
                 SHOW MODE (Read-Only)
                 ===================================================== -->
            @include('pages.vendor_portal.invoices.partials.show')
        @else
            <!-- =====================================================
                 CREATE / EDIT MODE (Form)
                 ===================================================== -->
            <form id="invoiceForm" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-lg-9">
                        
                        <!-- Contract & Basic Info Card -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-primary text-white py-2">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-info-circle me-2"></i>Invoice Details
                                </h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-3">
                                    <!-- Contract -->
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Contract <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm" name="contract_id" id="contractId" required>
                                            <option value="">-- Select Contract --</option>
                                        </select>
                                    </div>

                                    <!-- Invoice Number (NO PLACEHOLDER) -->
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Invoice Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="invoice_number" id="invoiceNumber" required>
                                    </div>

                                    <!-- Invoice Date -->
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Invoice Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-sm" name="invoice_date" id="invoiceDate" value="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <!-- Due Date -->
                                    <div class="col-md-3">
                                        <label class="form-label mb-1">Due Date</label>
                                        <input type="date" class="form-control form-control-sm" name="due_date" id="dueDate">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Line Items Card -->
                        <div class="card shadow-sm mb-3">
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
                                                <th style="width: 40px; font-size: 11px;">S.No</th>
                                                <th style="width: 140px; font-size: 11px;">Category <span class="text-danger">*</span></th>
                                                <th style="font-size: 11px;">Particulars</th>
                                                <th style="width: 90px; font-size: 11px;">SAC</th>
                                                <th style="width: 90px; font-size: 11px;">Qty <span class="text-danger">*</span></th>
                                                <th style="width: 70px; font-size: 11px;">UOM</th>
                                                <th style="width: 90px; font-size: 11px;">Rate <span class="text-danger">*</span></th>
                                                <th style="width: 70px; font-size: 11px;">Tax %</th>
                                                <th style="width: 110px; font-size: 11px;">Amount <span class="text-danger">*</span></th>
                                                <th style="width: 35px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="lineItemsBody">
                                            <!-- Rows added dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Totals Card -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-white py-2">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-calculator me-2"></i>Totals
                                </h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">Base Total <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" name="base_total" id="baseTotal" step="0.01" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">GST Total <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" name="gst_total" id="gstTotal" step="0.01" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">Grand Total <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control fw-bold text-success" name="grand_total" id="grandTotal" step="0.01" placeholder="0.00" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Invoice Card -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-white py-2">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-paperclip me-2"></i>Upload Invoice
                                </h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Invoice Document <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control form-control-sm" name="invoice_attachment" id="invoiceAttachment" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small class="text-muted">PDF, JPG, PNG (Max: 10MB)</small>
                                        <div id="currentInvoiceAttachment" class="mt-2 p-2 bg-light rounded" style="display: none;"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Description / Notes</label>
                                        <textarea class="form-control form-control-sm" name="description" id="description" rows="2" placeholder="Any additional notes..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column - Summary & Submit -->
                    <div class="col-lg-3">
                        <!-- Summary Card -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-white py-2">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-receipt me-2"></i>Summary
                                </h6>
                            </div>
                            <div class="card-body py-2">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">Line Items</td>
                                        <td class="text-end" id="summaryItems">0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Base Total</td>
                                        <td class="text-end" id="summaryBase">₹0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">GST</td>
                                        <td class="text-end" id="summaryGst">₹0.00</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold">Grand Total</td>
                                        <td class="text-end fw-bold text-success" id="summaryTotal">₹0.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Submit Card -->
                        <div class="card shadow-sm border-primary">
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
                                <div class="text-center mt-2">
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Once submitted, cannot be edited.
                                    </small>
                                </div>
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
    const MODE = '{{ $mode }}';
    const INVOICE_ID = '{{ $invoiceId ?? "" }}';
    
    // Get contract_id from URL if present
    const urlParams = new URLSearchParams(window.location.search);
    const URL_CONTRACT_ID = urlParams.get('contract_id');

    // Store contract items (categories)
    let contractItems = [];
    let rowCounter = 0;

    // =====================================================
    // INITIALIZATION
    // =====================================================
    $(document).ready(function() {
        // Load contracts dropdown
        loadContracts();

        // If edit or show mode, load invoice data
        if ((MODE === 'edit' || MODE === 'show') && INVOICE_ID) {
            loadInvoiceData();
        }
        // For create mode, don't add empty row - wait for contract selection

        // Event listeners
        $('#contractId').on('change', handleContractChange);
        $('#baseTotal, #gstTotal, #grandTotal').on('input', updateSummary);
        $('#invoiceForm').on('submit', handleFormSubmit);
    });

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

                    // If contract_id from URL, trigger change
                    if (URL_CONTRACT_ID) {
                        handleContractChange();
                    }
                }
            })
            .catch(error => console.error('Failed to load contracts:', error));
    }

    // =====================================================
    // HANDLE CONTRACT CHANGE - AUTO-FILL LINE ITEMS
    // =====================================================
    function handleContractChange() {
        const contractId = $('#contractId').val();
        
        if (!contractId) {
            contractItems = [];
            $('#lineItemsBody').empty();
            updateSummary();
            return;
        }

        // Load contract items and AUTO-FILL line items
        axios.get(`${CONTRACT_API}/${contractId}/items`)
            .then(response => {
                if (response.data.success) {
                    contractItems = response.data.data;
                    
                    // Clear existing rows
                    $('#lineItemsBody').empty();
                    
                    // AUTO-FILL: Add a row for each contract item
                    if (contractItems.length > 0) {
                        contractItems.forEach(item => {
                            addLineItemFromContract(item);
                        });
                    } else {
                        // No items in contract, add empty row
                        addLineItem();
                    }
                    
                    updateSummary();
                }
            })
            .catch(error => {
                console.error('Failed to load contract items:', error);
                showAlert('danger', 'Failed to load contract categories');
            });
    }

    // =====================================================
    // ADD LINE ITEM FROM CONTRACT (AUTO-FILL CATEGORY ONLY)
    // =====================================================
    function addLineItemFromContract(contractItem) {
        rowCounter++;
        const rowId = `row_${rowCounter}`;

        // Build category options with this item selected
        let categoryOptions = '<option value="">Select</option>';
        contractItems.forEach(item => {
            const selected = (item.id == contractItem.id) ? 'selected' : '';
            categoryOptions += `<option value="${item.id}" data-rate="${item.rate}" data-unit="${item.unit}" ${selected}>${item.category_name}</option>`;
        });

        // ONLY CATEGORY PRE-FILLED - Everything else empty
        const row = `
            <tr id="${rowId}">
                <td class="text-center align-middle" style="font-size: 11px;">
                    <span class="row-number">${$('#lineItemsBody tr').length + 1}</span>
                </td>
                <td>
                    <select class="form-select form-select-sm category-select" style="font-size: 11px;" required>
                        ${categoryOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm particulars-input" style="font-size: 11px;" placeholder="Description" value="">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm sac-input" style="font-size: 11px;" value="">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm qty-input" style="font-size: 11px;" step="0.01" value="" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm uom-input" style="font-size: 11px;" value="">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm rate-input" style="font-size: 11px;" step="0.01" value="" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm tax-input" style="font-size: 11px;" step="0.01" value="">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm amount-input" style="font-size: 11px;" step="0.01" value="" required>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeLineItem('${rowId}')">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#lineItemsBody').append(row);
        bindRowEvents(rowId);
        updateRowNumbers();
    }

    // =====================================================
    // ADD EMPTY LINE ITEM ROW
    // =====================================================
    function addLineItem(data = null) {
        rowCounter++;
        const rowId = `row_${rowCounter}`;

        // Build category options
        let categoryOptions = '<option value="">Select</option>';
        contractItems.forEach(item => {
            const selected = (data && data.contract_item_id == item.id) ? 'selected' : '';
            categoryOptions += `<option value="${item.id}" data-rate="${item.rate}" data-unit="${item.unit}" ${selected}>${item.category_name}</option>`;
        });

        const row = `
            <tr id="${rowId}">
                <td class="text-center align-middle" style="font-size: 11px;">
                    <span class="row-number">${$('#lineItemsBody tr').length + 1}</span>
                </td>
                <td>
                    <select class="form-select form-select-sm category-select" style="font-size: 11px;" required>
                        ${categoryOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm particulars-input" style="font-size: 11px;" placeholder="Description" value="${data?.particulars || ''}">
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
                    <input type="number" class="form-control form-control-sm tax-input" style="font-size: 11px;" step="0.01" value="${data?.tax_percent || ''}">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm amount-input" style="font-size: 11px;" step="0.01" value="${data?.amount || ''}" required>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeLineItem('${rowId}')">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#lineItemsBody').append(row);
        bindRowEvents(rowId);
        updateRowNumbers();
        updateSummary();
    }

    // =====================================================
    // BIND ROW EVENTS
    // =====================================================
    function bindRowEvents(rowId) {
        // Category change - NO auto-fill, vendor enters everything manually
        $(`#${rowId} .category-select`).on('change', function() {
            // Nothing auto-filled - vendor enters all values manually
        });
    }

    // =====================================================
    // REMOVE LINE ITEM ROW
    // =====================================================
    function removeLineItem(rowId) {
        if ($('#lineItemsBody tr').length > 1) {
            $(`#${rowId}`).remove();
            updateRowNumbers();
            updateSummary();
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
    // UPDATE SUMMARY
    // =====================================================
    function updateSummary() {
        const itemCount = $('#lineItemsBody tr').length;
        const baseTotal = parseFloat($('#baseTotal').val()) || 0;
        const gstTotal = parseFloat($('#gstTotal').val()) || 0;
        const grandTotal = parseFloat($('#grandTotal').val()) || 0;

        $('#summaryItems').text(itemCount);
        $('#summaryBase').text('₹' + formatNumber(baseTotal));
        $('#summaryGst').text('₹' + formatNumber(gstTotal));
        $('#summaryTotal').text('₹' + formatNumber(grandTotal));
    }

    // =====================================================
    // LOAD INVOICE DATA (for edit/show)
    // =====================================================
    function loadInvoiceData() {
        axios.get(`${API_BASE}/${INVOICE_ID}`)
            .then(response => {
                if (response.data.success) {
                    const invoice = response.data.data;
                    
                    if (MODE === 'show') {
                        populateShowMode(invoice);
                    } else {
                        populateEditMode(invoice);
                    }

                    $('#loadingSpinner').hide();
                    $('#mainContent').show();
                }
            })
            .catch(error => {
                console.error('Failed to load invoice:', error);
                showAlert('danger', 'Failed to load invoice details.');
                $('#loadingSpinner').html('<p class="text-danger">Failed to load invoice.</p>');
            });
    }

    // =====================================================
    // POPULATE EDIT MODE
    // =====================================================
    function populateEditMode(invoice) {
        // Wait for contracts to load
        const checkContracts = setInterval(() => {
            if ($('#contractId option').length > 1) {
                clearInterval(checkContracts);
                
                $('#contractId').val(invoice.contract_id);
                
                // Load contract items then populate line items
                if (invoice.contract_id) {
                    axios.get(`${CONTRACT_API}/${invoice.contract_id}/items`)
                        .then(response => {
                            if (response.data.success) {
                                contractItems = response.data.data;
                                
                                // Clear default row and add invoice items
                                $('#lineItemsBody').empty();
                                if (invoice.items && invoice.items.length > 0) {
                                    invoice.items.forEach(item => {
                                        addLineItem(item);
                                    });
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
        $('#baseTotal').val(invoice.base_total || invoice.amount);
        $('#gstTotal').val(invoice.gst_total || invoice.gst_amount);
        $('#grandTotal').val(invoice.grand_total || invoice.total_amount);
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
                $('#currentInvoiceAttachment').html(`<small><i class="bi bi-file-earmark-pdf me-1"></i>Current: ${att.file_name}</small>`).show();
                $('#invoiceAttachment').removeAttr('required');
            }
        }

        updateSummary();
    }

    // =====================================================
    // POPULATE SHOW MODE
    // =====================================================
    function populateShowMode(invoice) {
        // This will be handled by the partial view
    }

    // =====================================================
    // HANDLE FORM SUBMIT
    // =====================================================
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Validate at least one line item
        if ($('#lineItemsBody tr').length === 0) {
            showAlert('danger', 'Please add at least one line item');
            return;
        }

        // Collect line items
        const items = [];
        let hasError = false;

        $('#lineItemsBody tr').each(function() {
            const categoryId = $(this).find('.category-select').val();
            const particulars = $(this).find('.particulars-input').val();
            const sac = $(this).find('.sac-input').val();
            const quantity = $(this).find('.qty-input').val();
            const unit = $(this).find('.uom-input').val();
            const rate = $(this).find('.rate-input').val();
            const taxPercent = $(this).find('.tax-input').val();
            const amount = $(this).find('.amount-input').val();

            if (!categoryId || !quantity || !rate || !amount) {
                hasError = true;
                return false;
            }

            items.push({
                contract_item_id: categoryId,
                particulars: particulars || null,
                sac: sac || null,
                quantity: parseFloat(quantity),
                unit: unit || null,
                rate: parseFloat(rate),
                tax_percent: taxPercent ? parseFloat(taxPercent) : null,
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

        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

        let url = API_BASE;
        if (MODE === 'edit') {
            url = `${API_BASE}/${INVOICE_ID}/update`;
        }

        axios.post(url, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(response => {
            if (response.data.success) {
                // Auto-submit after create
                if (MODE === 'create') {
                    const invoiceId = response.data.data.id;
                    return axios.post(`${API_BASE}/${invoiceId}/submit`);
                }
                return response;
            }
        })
        .then(response => {
            showAlert('success', MODE === 'edit' ? 'Invoice resubmitted successfully!' : 'Invoice submitted successfully!');
            setTimeout(() => {
                window.location.href = '{{ route("vendor.invoices.index") }}';
            }, 1500);
        })
        .catch(error => {
            submitBtn.prop('disabled', false).html(originalText);
            
            if (error.response && error.response.data) {
                const data = error.response.data;
                showAlert('danger', data.message || 'Failed to submit invoice.');
            } else {
                showAlert('danger', 'Something went wrong. Please try again.');
            }
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