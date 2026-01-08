@extends('layouts.Vendor')
@section('title', 'Create Travel Invoice')

@section('content')
<style>
    .card { border: none; border-radius: 8px; }

    /* Page Header */
    .page-icon {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        background: #eef4ff;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    .page-title { font-size: 22px; font-weight: 700; color: #0f172a; margin: 0; }
    .page-subtitle { font-size: 13px; color: #6b7280; margin: 0; }

    /* Batch Info */
    .batch-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .batch-number { font-size: 18px; font-weight: 700; }
    .batch-meta { font-size: 13px; opacity: 0.9; }

    /* Invoice Card */
    .invoice-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        margin-bottom: 16px;
        overflow: hidden;
        transition: box-shadow 0.2s;
    }
    .invoice-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .invoice-card.has-error { border-color: #ef4444; }
    .invoice-card.is-saved { border-color: #10b981; background: #f0fdf4; }

    /* Invoice Header */
    .invoice-header {
        display: flex;
        align-items: center;
        padding: 14px 16px;
        background: #f9fafb;
        cursor: pointer;
        gap: 16px;
    }
    .invoice-header:hover { background: #f3f4f6; }
    .invoice-card.is-saved .invoice-header { background: #ecfdf5; }
    .invoice-number-badge {
        background: #2563eb;
        color: #fff;
        font-weight: 600;
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 6px;
    }
    .invoice-card.is-saved .invoice-number-badge { background: #10b981; }
    .invoice-summary {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 20px;
        font-size: 13px;
        flex-wrap: wrap;
    }
    .invoice-summary-item { display: flex; flex-direction: column; }
    .invoice-summary-label { font-size: 10px; color: #6b7280; text-transform: uppercase; }
    .invoice-summary-value { font-weight: 600; color: #1f2937; }
    .invoice-summary-value.amount { color: #059669; }
    .invoice-toggle { color: #6b7280; transition: transform 0.2s; }
    .invoice-toggle.expanded { transform: rotate(180deg); }

    /* Saved Badge */
    .saved-badge {
        background: #10b981;
        color: #fff;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 4px;
        margin-left: 8px;
    }

    /* Invoice Body */
    .invoice-body {
        display: none;
        padding: 20px;
        border-top: 1px solid #e5e7eb;
    }
    .invoice-body.show { display: block; }

    /* Form Sections */
    .form-section { margin-bottom: 20px; }
    .form-section-title {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-section-title i { color: #6b7280; }

    /* Expense Table */
    .expense-table { width: 100%; font-size: 13px; }
    .expense-table th {
        background: #f3f4f6;
        padding: 10px 12px;
        font-weight: 600;
        font-size: 11px;
        text-transform: uppercase;
        color: #4b5563;
    }
    .expense-table td { padding: 8px 6px; border-bottom: 1px solid #f3f4f6; }
    .expense-table input, .expense-table select { font-size: 13px; padding: 6px 8px; }
    .expense-total-row td { background: #f0fdf4; font-weight: 600; }

    /* Remove number input arrows */
    .expense-table input[type=number]::-webkit-outer-spin-button,
    .expense-table input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .expense-table input[type=number] {
        -moz-appearance: textfield;
    }

    /* File Upload */
    .file-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        background: #fafafa;
    }
    .file-upload-area:hover { border-color: #2563eb; background: #eff6ff; }
    .file-upload-area.has-file { border-color: #10b981; background: #f0fdf4; }
    .file-upload-icon { font-size: 28px; color: #9ca3af; margin-bottom: 8px; }
    .file-upload-area.has-file .file-upload-icon { color: #10b981; }
    .file-name { font-size: 12px; color: #059669; margin-top: 4px; }

    /* Summary Card */
    .summary-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        position: sticky;
        top: 20px;
    }
    .summary-header {
        background: #1e40af;
        color: #fff;
        padding: 14px 16px;
        border-radius: 10px 10px 0 0;
        font-weight: 600;
    }
    .summary-body { padding: 16px; }
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 14px;
        border-bottom: 1px solid #f3f4f6;
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-row.total {
        font-weight: 700;
        font-size: 16px;
        color: #059669;
        padding-top: 12px;
        margin-top: 8px;
        border-top: 2px solid #e5e7eb;
    }
    .summary-label { color: #6b7280; }
    .summary-value { font-weight: 600; color: #1f2937; }

    /* Add Invoice Button */
    .add-invoice-btn {
        border: 2px dashed #d1d5db;
        background: #fafafa;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        color: #6b7280;
    }
    .add-invoice-btn:hover { border-color: #2563eb; background: #eff6ff; color: #2563eb; }

    /* Bulk Upload */
    .bulk-upload-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-top: 16px;
    }

    /* TDS Input */
    .tds-input-group {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed #e5e7eb;
    }
    .tds-input-group label {
        font-size: 12px;
        color: #6b7280;
        margin: 0;
    }
    .tds-input-group input {
        width: 70px;
        font-size: 13px;
    }

    /* Disabled state for saved invoices */
    .invoice-card.is-saved .invoice-body input:not(.tds-percent),
    .invoice-card.is-saved .invoice-body select {
        pointer-events: none;
        background: #f3f4f6;
    }

    /* Credit Note Styles */
    .invoice-card.is-credit-note { border-color: #f59e0b; }
    .invoice-card.is-credit-note .invoice-header { background: #fffbeb; }
    .invoice-card.is-credit-note .invoice-number-badge { background: #f59e0b; }
    .invoice-card.is-credit-note.is-saved .invoice-header { background: #ecfdf5; }
    .invoice-card.is-credit-note.is-saved .invoice-number-badge { background: #10b981; }
    .invoice-summary-value.amount.credit { color: #dc2626; }

    .reference-invoice-section {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        display: none;
    }
    .reference-invoice-section.show { display: block; }
    .reference-invoice-section .section-title {
        font-size: 13px;
        font-weight: 600;
        color: #92400e;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .reference-invoice-info {
        background: #fff;
        border-radius: 6px;
        padding: 12px;
        margin-top: 10px;
        display: none;
    }
    .reference-invoice-info.show { display: block; }
    .reference-invoice-info .info-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        font-size: 12px;
        border-bottom: 1px solid #f3f4f6;
    }
    .reference-invoice-info .info-row:last-child { border-bottom: none; }
    .reference-invoice-info .info-label { color: #6b7280; }
    .reference-invoice-info .info-value { font-weight: 600; color: #1f2937; }

    .max-credit-warning {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        margin-top: 10px;
        display: none;
    }
    .max-credit-warning.show { display: block; }




    .invoice-card.is-credit-note .expense-total-row td { background: #fef2f2; }
    .invoice-card.is-credit-note .locked-field { pointer-events: none; background: #f3f4f6 !important; }

/* Entry Mode Toggle */
    .entry-mode-toggle .btn {
        padding: 12px 20px;
    }
    .entry-mode-toggle .btn-check:checked + .btn {
        background: #1e40af;
        color: white;
        border-color: #1e40af;
    }
    
    .excel-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #fafafa;
    }
    .excel-upload-area:hover {
        border-color: #2563eb;
        background: #eff6ff;
    }
    .excel-upload-area.drag-over {
        border-color: #10b981;
        background: #ecfdf5;
    }
    .excel-upload-area.has-file {
        border-color: #10b981;
        background: #f0fdf4;
    }
    
    #previewTable th {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 600;
    }
    #previewTable td {
        font-size: 13px;
        vertical-align: middle;
    }
    .status-valid { color: #10b981; }
    .status-invalid { color: #ef4444; }

</style>

<div class="container-fluid py-3">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="page-icon"><i class="bi bi-airplane"></i></div>
            <div>
                <h2 class="page-title">Create Travel Invoice</h2>
                <p class="page-subtitle">Add travel expense invoices for employees</p>
            </div>
        </div>
        <a href="{{ route('vendor.travel-invoices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to List
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">

         <!-- Batch Info -->
            <div class="batch-info d-flex justify-content-between align-items-center">
                <div>
                    <div class="batch-number" id="batchNumber">Loading...</div>
                    <div class="batch-meta">
                        <span id="savedInvoiceCount">0</span> saved, 
                        <span id="unsavedInvoiceCount">0</span> unsaved
                    </div>
                </div>
                <span class="badge bg-white text-dark" id="batchStatus">Draft</span>
            </div>

            <!-- Entry Mode Toggle (NEW!) -->
            <div class="entry-mode-toggle mb-4">
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="entryMode" id="manualMode" value="manual" checked>
                    <label class="btn btn-outline-primary" for="manualMode">
                        <i class="bi bi-pencil-square me-2"></i>Manual Entry (One by One)
                    </label>
                    
                    <input type="radio" class="btn-check" name="entryMode" id="bulkMode" value="bulk">
                    <label class="btn btn-outline-primary" for="bulkMode">
                        <i class="bi bi-file-earmark-excel me-2"></i>Bulk Upload (Excel)
                    </label>
                </div>
            </div>

            <!-- Manual Entry Section -->
            <div id="manualEntrySection">
                <!-- Invoices Container -->
                <div id="invoicesContainer"></div>

                <!-- Add Invoice Button -->
                <div class="add-invoice-btn" onclick="addNewInvoice()">
                    <i class="bi bi-plus-lg me-2"></i>
                    <strong>Add Another Invoice</strong>
                    <div class="small text-muted mt-1">Click to add more employee invoices</div>
                </div>

                <!-- Bulk Upload Section -->
                <div class="bulk-upload-section">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-file-earmark-zip text-primary"></i>
                        <strong class="small">Bulk Attachment (Optional)</strong>
                    </div>
                    <p class="small text-muted mb-2">Upload a single PDF containing all bills combined</p>
                    <input type="file" id="bulkAttachment" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>

            <!-- Bulk Upload Section (NEW!) -->
            <div id="bulkUploadSection" style="display: none;">
                
                <!-- Step 1: Download Template -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white py-2">
                        <i class="bi bi-1-circle me-2"></i>Step 1: Download Template
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Download the Excel template with all employees pre-filled. 
                            Template includes latest employee data and project assignments.
                        </p>
                        <button type="button" class="btn btn-success" id="downloadTemplateBtn" onclick="downloadExcelTemplate()">
                            <i class="bi bi-download me-2"></i>Download Excel Template
                        </button>
                        <span class="text-muted small ms-2">
                            <i class="bi bi-info-circle"></i> Download fresh template each time for latest data
                        </span>
                    </div>
                </div>
                
                <!-- Step 2: Upload Filled Excel -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white py-2">
                        <i class="bi bi-2-circle me-2"></i>Step 2: Upload Filled Excel
                    </div>
                    <div class="card-body">
                        <div class="excel-upload-area" id="excelUploadArea">
                            <i class="bi bi-cloud-upload display-4 text-muted d-block mb-2"></i>
                            <p class="mb-2">Drag & Drop Excel file here</p>
                            <p class="text-muted small mb-3">or click to browse</p>
                            <input type="file" id="excelFileInput" class="d-none" accept=".xlsx,.xls">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('excelFileInput').click()">
                                <i class="bi bi-folder2-open me-1"></i>Browse Files
                            </button>
                        </div>
                        <div id="selectedFileName" class="mt-2 text-success d-none">
                            <i class="bi bi-file-earmark-excel me-1"></i>
                            <span class="file-name"></span>
                            <button type="button" class="btn btn-sm btn-link text-danger" onclick="clearSelectedFile()">
                                <i class="bi bi-x-circle"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3: Preview -->
                <div class="card mb-3" id="previewSection" style="display: none;">
                    <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-3-circle me-2"></i>Step 3: Preview & Confirm</span>
                        <span class="badge bg-light text-dark" id="previewSummary">0 valid, 0 errors</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="previewTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Row</th>
                                        <th>Invoice No</th>
                                        <th>Employee</th>
                                        <th>Project</th>
                                        <th>Location</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                <span class="text-success"><i class="bi bi-check-circle"></i> Valid: <span id="validCount">0</span></span>
                                <span class="ms-3 text-danger"><i class="bi bi-x-circle"></i> Errors: <span id="errorCount">0</span></span>
                                <span class="ms-3 text-secondary"><i class="bi bi-dash-circle"></i> Skipped: <span id="skippedCount">0</span></span>
                            </div>
                            <button type="button" class="btn btn-primary" id="createInvoicesBtn" onclick="createInvoicesFromExcel()">
                                <i class="bi bi-check-lg me-2"></i>Create <span id="createCount">0</span> Invoice(s)
                            </button>
                        </div>
                    </div>
                </div>
                
            </div>

         

        <!-- Summary Sidebar -->
        <div class="col-lg-4">
            <div class="summary-card">
                <div class="summary-header"><i class="bi bi-calculator me-2"></i>Batch Summary</div>
                <div class="summary-body">
                    <div class="summary-row">
                        <span class="summary-label">Tax Invoices</span>
                        <span class="summary-value" id="summaryTaxCount">0</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Credit Notes</span>
                        <span class="summary-value text-warning" id="summaryCreditCount">0</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Gross Amount</span>
                        <span class="summary-value" id="summaryGross">₹0.00</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Credit Amount</span>
                        <span class="summary-value text-danger" id="summaryCreditAmt">-₹0.00</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Total TDS</span>
                        <span class="summary-value text-danger" id="summaryTds">-₹0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Net Payable</span>
                        <span id="summaryNet">₹0.00</span>
                    </div>

                    <hr class="my-3">

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="submitBatchBtn" onclick="submitBatch()">
                            <i class="bi bi-send me-2"></i>Submit Batch
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="saveDraft()">
                            <i class="bi bi-save me-1"></i>Save Current Invoice as Draft
                        </button>
                    </div>

                    <p class="text-center text-muted small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>Once submitted, invoices cannot be edited
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Template -->
<template id="invoiceTemplate">
  <div class="invoice-card" data-invoice-index="__INDEX__" data-invoice-id="" data-is-saved="false" data-is-credit-note="false" data-reference-invoice-id="" data-reference-batch-id="" data-max-credit="0">
        <div class="invoice-header" onclick="toggleInvoice(__INDEX__)">
            <span class="invoice-number-badge">#<span class="inv-num">__INDEX__</span></span>
            <div class="invoice-summary">
                <div class="invoice-summary-item">
                    <span class="invoice-summary-label">Employee</span>
                    <span class="invoice-summary-value emp-name">Select Employee</span>
                </div>
                <div class="invoice-summary-item">
                    <span class="invoice-summary-label">Invoice No</span>
                    <span class="invoice-summary-value inv-no">-</span>
                </div>
                <div class="invoice-summary-item">
                    <span class="invoice-summary-label">Location</span>
                    <span class="invoice-summary-value loc-name">-</span>
                </div>
                <div class="invoice-summary-item">
                    <span class="invoice-summary-label">Amount</span>
                    <span class="invoice-summary-value amount inv-amount">₹0.00</span>
                </div>
            </div>
            <span class="saved-badge d-none" id="saved-badge-__INDEX__">✓ Saved</span>
            <div onclick="event.stopPropagation()">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeInvoice(__INDEX__)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <i class="bi bi-chevron-down invoice-toggle" id="toggle-__INDEX__"></i>
        </div>

        <div class="invoice-body show" id="body-__INDEX__">
            
            <!-- Basic Info -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-info-circle"></i>Basic Information</div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Employee <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm employee-select" data-index="__INDEX__" required>
                            <option value="">Select Employee</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Project</label>
                        <input type="text" class="form-control form-control-sm bg-light project-name" readonly placeholder="Auto-fill from employee">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm invoice-number" placeholder="e.g., INV-001" required>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label small">Invoice Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm invoice-date" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Invoice Type</label>
                        <select class="form-select form-select-sm invoice-type">
                            <option value="tax_invoice">Tax Invoice</option>
                            <option value="credit_note">Credit Note</option>
                        </select>
                    </div>
                 <div class="col-md-4">
    <label class="form-label small">Travel Type</label>
    <select class="form-select form-select-sm travel-type">
        <option value="">Select Travel Type</option>
    </select>
</div>
                </div>
            </div>

            <!-- Reference Invoice Section (Credit Note Only) -->
            <div class="reference-invoice-section d-none" id="reference-section-__INDEX__">
                <div class="section-title"><i class="bi bi-link-45deg"></i>Reference Invoice (Credit Note Against)</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small">Select Original Invoice <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm reference-invoice-select" data-index="__INDEX__">
                            <option value="">-- Select Invoice --</option>
                        </select>
                    </div>
                </div>
                <div class="reference-invoice-info" id="reference-info-__INDEX__">
                    <div class="info-row"><span class="info-label">Batch</span><span class="info-value ref-batch">-</span></div>
                    <div class="info-row"><span class="info-label">Employee</span><span class="info-value ref-employee">-</span></div>
                    <div class="info-row"><span class="info-label">Location</span><span class="info-value ref-location">-</span></div>
                    <div class="info-row"><span class="info-label">Original Amount</span><span class="info-value ref-amount text-success">₹0.00</span></div>
                </div>
                <div class="max-credit-warning d-none" id="max-credit-warning-__INDEX__">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Credit amount cannot exceed original invoice amount of <strong class="max-credit-amount">₹0.00</strong>
                </div>
            </div>

            <!-- Travel Info -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-geo-alt"></i>Travel Details</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm location" placeholder="e.g., Mumbai, Delhi" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Travel Date</label>
                        <input type="date" class="form-control form-control-sm travel-date">
                    </div>
                </div>
            </div>

            <!-- Expenses -->
            <div class="form-section">
                <div class="form-section-title d-flex justify-content-between">
                    <span><i class="bi bi-cash-stack"></i>Expenses</span>
                    <button type="button" class="btn btn-sm btn-outline-primary add-expense-btn" onclick="addExpenseRow(__INDEX__)">
                        <i class="bi bi-plus me-1"></i>Add Row
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Mode</th>
                                <th>Particulars</th>
                                <th style="width: 85px;">Basic</th>
                                <th style="width: 70px;">Taxes</th>
                                <th style="width: 70px;">Service</th>
                                <th style="width: 70px;">GST</th>
                                <th style="width: 90px;">Total</th>
                                <th style="width: 35px;"></th>
                            </tr>
                        </thead>
                        <tbody class="expense-rows"></tbody>
                        <tfoot>
                            <tr class="expense-total-row">
                                <td colspan="2" class="text-end"><strong>Invoice Total:</strong></td>
                                <td class="basic-total">₹0</td>
                                <td class="taxes-total">₹0</td>
                                <td class="service-total">₹0</td>
                                <td class="gst-total">₹0</td>
                                <td class="gross-total">₹0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- TDS at Invoice Level -->
                <div class="tds-input-group">
                    <label>TDS %:</label>
                    <input type="number" class="form-control form-control-sm tds-percent" value="5" min="0" max="100" step="1">
                    <span class="text-muted small">on Basic Amount</span>
                    <span class="ms-auto">
                        <span class="text-muted small">TDS Amount:</span>
                        <strong class="text-danger tds-amount">₹0</strong>
                    </span>
                    <span class="ms-3">
                        <span class="text-muted small">Net:</span>
                        <strong class="text-success net-amount">₹0</strong>
                    </span>
                </div>
            </div>

            <!-- Attachment -->
            <div class="form-section">
                <div class="form-section-title"><i class="bi bi-paperclip"></i>Bill Attachment</div>
                <div class="file-upload-area" onclick="document.getElementById('file-__INDEX__').click()">
                    <i class="bi bi-cloud-upload file-upload-icon d-block"></i>
                    <span class="small">Click to upload bill (PDF, JPG, PNG)</span>
                    <div class="file-name" id="filename-__INDEX__"></div>
                </div>
                <input type="file" id="file-__INDEX__" class="d-none bill-file" accept=".pdf,.jpg,.jpeg,.png" data-index="__INDEX__">
            </div>
        </div>
    </div>
</template>

<!-- Expense Row Template -->
<template id="expenseRowTemplate">
    <tr data-expense-index="__EXP_INDEX__">
        <td>
            <select class="form-select form-select-sm expense-mode">
                <option value="flight">Flight</option>
                <option value="train">Train</option>
                <option value="cabs">Cabs</option>
                <option value="accommodation">Accommodation</option>
                <option value="insurance">Insurance</option>
                <option value="visa">Visa</option>
                <option value="other">Other</option>
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm expense-particulars" placeholder="Description"></td>
        <td><input type="number" class="form-control form-control-sm expense-basic" placeholder="0" step="1" min="0"></td>
        <td><input type="number" class="form-control form-control-sm expense-taxes" placeholder="0" step="1" min="0"></td>
        <td><input type="number" class="form-control form-control-sm expense-service" placeholder="0" step="1" min="0"></td>
        <td><input type="number" class="form-control form-control-sm expense-gst" placeholder="0" step="1" min="0"></td>
        <td><input type="text" class="form-control form-control-sm expense-total bg-light" value="₹0" readonly></td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 remove-expense-btn" onclick="removeExpenseRow(this)">
                <i class="bi bi-x"></i>
            </button>
        </td>
    </tr>
</template>

@endsection
@push('scripts')
<script>
const API_BASE = '/api/vendor/travel-invoices';
const urlParams = new URLSearchParams(window.location.search);
let BATCH_ID = urlParams.get('batch_id');
let BATCH_NUMBER = null;

let employees = [];
let travelCategories = [];
let invoiceCounter = 0;
let expenseCounters = {};
let savedInvoiceIds = new Set();
let submittedInvoices = []; // For Credit Note reference dropdown

let lastInvoiceData = {
    employee_id: null,
    employee_project: null,
    location: null,
    travel_date: null,
    invoice_date: null,
    travel_type: 'domestic',
    invoice_type: 'tax_invoice',
    tds_percent: 5
};

// =====================================================
// INITIALIZATION
// =====================================================
$(document).ready(function() {
    Promise.all([loadEmployees(), loadSubmittedInvoices(), loadTravelCategories()]).then(() => {
        initializeBatch();
    });
});

function initializeBatch() {
    if (BATCH_ID) {
        loadBatchData();
    } else {
        getNextBatchNumber();
        addNewInvoice();
    }
}

function getNextBatchNumber() {
    axios.get(`${API_BASE}/batches/next-number`)
        .then(res => {
            if (res.data.success) {
                BATCH_NUMBER = res.data.data.batch_number;
                $('#batchNumber').text(BATCH_NUMBER + ' (Draft)');
            }
        })
        .catch(err => {
            $('#batchNumber').text('New Batch (Draft)');
        });
}

async function createBatchIfNeeded() {
    if (BATCH_ID) {
        return BATCH_ID;
    }
    try {
        const res = await axios.post(`${API_BASE}/batches`);
        if (res.data.success) {
            BATCH_ID = res.data.data.id;
            BATCH_NUMBER = res.data.data.batch_number;
            $('#batchNumber').text(BATCH_NUMBER);
            window.history.replaceState({}, '', `?batch_id=${BATCH_ID}`);
            return BATCH_ID;
        }
    } catch (err) {
        Toast.error('Failed to create batch');
        throw err;
    }
}

// =====================================================
// DATA LOADING
// =====================================================
function loadBatchData() {
    axios.get(`${API_BASE}/batches/${BATCH_ID}`)
        .then(res => {
            if (res.data.success) {
                const batch = res.data.data.batch;
                const invoices = res.data.data.invoices || [];
                
                $('#batchNumber').text(batch.batch_number);
                $('#batchStatus').text(batch.status.charAt(0).toUpperCase() + batch.status.slice(1));

                if (invoices.length > 0) {
                    invoices.forEach(inv => loadExistingInvoice(inv));
                    const lastInv = invoices[invoices.length - 1];
                    updateLastInvoiceData(lastInv);
                }
                
                addNewInvoice();
                updateBatchSummary();
            }
        })
        .catch(err => Toast.error('Failed to load batch'));
}

function loadEmployees() {
    return axios.get(`${API_BASE}/employees`)
        .then(res => {
            if (res.data.success) {
                employees = res.data.data;
                return employees;
            }
            return [];
        })
        .catch(err => []);
}

// Load ALL submitted Tax Invoices for Credit Note dropdown
function loadSubmittedInvoices() {
    return axios.get(`${API_BASE}/submitted-invoices`)
        .then(res => {
            if (res.data.success) {
                submittedInvoices = res.data.data || [];
                return submittedInvoices;
            }
            return [];
        })
        .catch(err => {
            console.error('Failed to load submitted invoices:', err);
            return [];
        });
}



// Load Travel Categories from Category Master
function loadTravelCategories() {
    return axios.get('/api/admin/categories/travel')
        .then(res => {
            if (res.data.success) {
                travelCategories = res.data.data;
                return travelCategories;
            }
            return [];
        })
        .catch(err => {
            console.error('Failed to load travel categories:', err);
            return [];
        });
}



function updateLastInvoiceData(invoice) {
    if (invoice) {
        lastInvoiceData = {
            employee_id: invoice.employee_id || null,
            employee_project: invoice.tag_name || invoice.employee_project || null,
            location: invoice.location || null,
            travel_date: invoice.travel_date || null,
            invoice_date: invoice.invoice_date || null,
            travel_type: invoice.travel_type || 'domestic',
            invoice_type: invoice.invoice_type || 'tax_invoice',
            tds_percent: invoice.tds_percent || 5
        };
    }
}

// =====================================================
// DROPDOWN POPULATION
// =====================================================
function updateAllEmployeeDropdowns() {
    let options = '<option value="">Select Employee</option>';
    employees.forEach(emp => {
        options += `<option value="${emp.id}" data-project="${emp.tag_name || ''}">${emp.employee_name}${emp.employee_code ? ` (${emp.employee_code})` : ''}</option>`;
    });
    $('.employee-select').each(function() {
        const currentVal = $(this).val();
        $(this).html(options).val(currentVal);
    });
}

// Populate Reference Invoice dropdown for Credit Notes
function populateReferenceInvoiceDropdown(index) {
    const card = $(`[data-invoice-index="${index}"]`);
    const dropdown = card.find('.reference-invoice-select');
    
    let options = '<option value="">-- Select Invoice --</option>';
    
    // Group by batch
    const grouped = {};
    submittedInvoices.forEach(inv => {
        if (inv.invoice_type === 'tax_invoice') {
            const bn = inv.batch?.batch_number || 'Unknown Batch';
            if (!grouped[bn]) grouped[bn] = [];
            grouped[bn].push(inv);
        }
    });
    
    Object.keys(grouped).forEach(bn => {
        options += `<optgroup label="${bn}">`;
        grouped[bn].forEach(inv => {
            const emp = inv.employee?.employee_name || 'Unknown';
            const amt = formatNumber(inv.gross_amount || 0);
            options += `<option value="${inv.id}" 
                data-batch-id="${inv.batch_id}"
                data-batch-number="${bn}"
                data-employee-id="${inv.employee_id}"
                data-employee-name="${emp}"
                data-project="${inv.tag_name || ''}"
                data-location="${inv.location || ''}"
                data-travel-type="${inv.travel_type || 'domestic'}"
                data-travel-date="${inv.travel_date || ''}"
                data-amount="${inv.gross_amount || 0}"
                data-tds-percent="${inv.tds_percent || 5}">
                ${inv.invoice_number} - ${emp} - ₹${amt}
            </option>`;
        });
        options += '</optgroup>';
    });
    
    dropdown.html(options);
}

// =====================================================
// ADD NEW INVOICE
// =====================================================
function addNewInvoice() {
    invoiceCounter++;
    const index = invoiceCounter;
    expenseCounters[index] = 0;

    const template = $('#invoiceTemplate').html().replace(/__INDEX__/g, index);
    $('#invoicesContainer').append(template);

    const card = $(`[data-invoice-index="${index}"]`);
    
    // Populate employee dropdown
    let options = '<option value="">Select Employee</option>';
    employees.forEach(emp => {
        options += `<option value="${emp.id}" data-project="${emp.tag_name || ''}">${emp.employee_name}${emp.employee_code ? ` (${emp.employee_code})` : ''}</option>`;
    });
    card.find('.employee-select').html(options);

    // Populate travel type dropdown
let travelOptions = '<option value="">Select Travel Type</option>';
travelCategories.forEach(cat => {
    travelOptions += `<option value="${cat.id}" data-zoho-account-id="${cat.zoho_account_id}" data-zoho-account-name="${cat.zoho_account_name}">${cat.name}</option>`;
});
card.find('.travel-type').html(travelOptions);


    
    // Populate reference invoice dropdown
    populateReferenceInvoiceDropdown(index);
    
    // Set default values
    const today = new Date().toISOString().split('T')[0];
    card.find('.invoice-date').val(today);
    card.find('.travel-date').val(today);
    card.find('.tds-percent').val(5);
    
    addExpenseRow(index);
    bindInvoiceEvents(index);
    updateInvoiceCount();
    updateBatchSummary();
    updateInvoiceHeader(index);

    $(`[data-invoice-index="${index}"]`)[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// =====================================================
// LOAD EXISTING INVOICE
// =====================================================
function formatDateForInput(dateString) {
    if (!dateString) return '';
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) return dateString;
    if (typeof dateString === 'string' && dateString.includes('T')) return dateString.split('T')[0];
    if (typeof dateString === 'string' && dateString.includes(' ')) return dateString.split(' ')[0];
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    } catch (e) { return ''; }
}

function loadExistingInvoice(invoice) {
    invoiceCounter++;
    const index = invoiceCounter;
    expenseCounters[index] = 0;

    const template = $('#invoiceTemplate').html().replace(/__INDEX__/g, index);
    $('#invoicesContainer').append(template);

    const card = $(`[data-invoice-index="${index}"]`);
    
    // Mark as saved
    card.attr('data-invoice-id', invoice.id);
    card.attr('data-is-saved', 'true');
    card.addClass('is-saved');
    $(`#saved-badge-${index}`).removeClass('d-none');
    savedInvoiceIds.add(invoice.id);
    
    // Check if credit note
    if (invoice.invoice_type === 'credit_note') {
        card.attr('data-is-credit-note', 'true');
        card.addClass('is-credit-note');
        $(`#reference-section-${index}`).removeClass('d-none').addClass('show');
        if (invoice.reference_invoice_id) {
            card.attr('data-reference-invoice-id', invoice.reference_invoice_id);
            card.attr('data-reference-batch-id', invoice.reference_batch_id || '');
        }
    }
    
    // Populate dropdowns
    let empOptions = '<option value="">Select Employee</option>';
    employees.forEach(emp => {
        empOptions += `<option value="${emp.id}" data-project="${emp.tag_name || ''}">${emp.employee_name}${emp.employee_code ? ` (${emp.employee_code})` : ''}</option>`;
    });
    card.find('.employee-select').html(empOptions);

    // Populate travel type dropdown
let travelOptions = '<option value="">Select Travel Type</option>';
travelCategories.forEach(cat => {
    travelOptions += `<option value="${cat.id}" data-zoho-account-id="${cat.zoho_account_id}" data-zoho-account-name="${cat.zoho_account_name}">${cat.name}</option>`;
});
card.find('.travel-type').html(travelOptions);
    populateReferenceInvoiceDropdown(index);
    
    // Fill values
    card.find('.invoice-type').val(invoice.invoice_type);
    card.find('.employee-select').val(invoice.employee_id);
    card.find('.project-name').val(invoice.tag_name || '');
    card.find('.invoice-number').val(invoice.invoice_number);
    card.find('.invoice-date').val(formatDateForInput(invoice.invoice_date));
    card.find('.travel-date').val(formatDateForInput(invoice.travel_date));
    card.find('.travel-type').val(invoice.travel_type);
    card.find('.location').val(invoice.location);
    card.find('.tds-percent').val(invoice.tds_percent || 5);
    
    if (invoice.reference_invoice_id) {
        card.find('.reference-invoice-select').val(invoice.reference_invoice_id);
    }

    // Show existing bills
    if (invoice.bills && invoice.bills.length > 0) {
        const bill = invoice.bills[0];
        card.find('.file-upload-area').addClass('has-file');
        $(`#filename-${index}`).html(`
            <i class="bi bi-check-circle text-success me-1"></i>${bill.file_name}
            <a href="/storage/${bill.file_path}" target="_blank" class="ms-2 small"><i class="bi bi-eye"></i> View</a>
        `);
        card.attr('data-existing-bill-id', bill.id);
    }

    // Load expense items
    if (invoice.items && invoice.items.length > 0) {
        invoice.items.forEach(item => addExpenseRow(index, item));
    } else {
        addExpenseRow(index);
    }

    bindInvoiceEvents(index);
    card.find('.invoice-body').removeClass('show');
    updateInvoiceHeader(index);
    calculateInvoiceTotals(index);
}

// =====================================================
// EVENT BINDINGS
// =====================================================
function bindInvoiceEvents(index) {
    const card = $(`[data-invoice-index="${index}"]`);

    // Invoice Type change - Show/Hide Credit Note section
    card.find('.invoice-type').on('change', function() {
        const isCN = $(this).val() === 'credit_note';
        const refSection = $(`#reference-section-${index}`);
        
        card.attr('data-is-credit-note', isCN);
        
        if (isCN) {
            card.addClass('is-credit-note');
            refSection.removeClass('d-none').addClass('show');
            
            // Lock fields until reference is selected
            card.find('.employee-select').addClass('locked-field').prop('disabled', true);
            card.find('.location').addClass('locked-field').prop('readonly', true);
            card.find('.travel-type').addClass('locked-field').prop('disabled', true);
            card.find('.travel-date').addClass('locked-field').prop('readonly', true);
        } else {
            card.removeClass('is-credit-note');
            refSection.addClass('d-none').removeClass('show');
            
            // Unlock fields
            card.find('.employee-select').removeClass('locked-field').prop('disabled', false);
            card.find('.location').removeClass('locked-field').prop('readonly', false);
            card.find('.travel-type').removeClass('locked-field').prop('disabled', false);
            card.find('.travel-date').removeClass('locked-field').prop('readonly', false);
            
            // Clear reference data
            card.attr('data-reference-invoice-id', '');
            card.attr('data-reference-batch-id', '');
            card.find('.reference-invoice-select').val('');
            $(`#reference-info-${index}`).removeClass('show');
        }
        
        updateInvoiceHeader(index);
        updateBatchSummary();
    });

    // Reference Invoice selection
    card.find('.reference-invoice-select').on('change', function() {
        const opt = $(this).find(':selected');
        const refInfo = $(`#reference-info-${index}`);
        
        if ($(this).val()) {
            // Store reference data
            card.attr('data-reference-invoice-id', $(this).val());
            card.attr('data-reference-batch-id', opt.data('batch-id'));
            card.attr('data-max-credit', opt.data('amount'));
            
            // Show reference info
            refInfo.addClass('show');
            refInfo.find('.ref-batch').text(opt.data('batch-number'));
            refInfo.find('.ref-employee').text(opt.data('employee-name'));
            refInfo.find('.ref-location').text(opt.data('location') || '-');
            refInfo.find('.ref-amount').text('₹' + formatNumber(opt.data('amount')));
            
            $(`#max-credit-warning-${index}`).find('.max-credit-amount').text('₹' + formatNumber(opt.data('amount')));
            
            // Auto-fill fields from reference
            card.find('.employee-select').val(opt.data('employee-id'));
            card.find('.project-name').val(opt.data('project'));
            card.find('.location').val(opt.data('location'));
            card.find('.travel-type').val(opt.data('travel-type'));
            if (opt.data('travel-date')) {
                card.find('.travel-date').val(formatDateForInput(opt.data('travel-date')));
            }
            card.find('.tds-percent').val(opt.data('tds-percent'));
            
            updateInvoiceHeader(index);
        } else {
            card.attr('data-reference-invoice-id', '');
            card.attr('data-reference-batch-id', '');
            refInfo.removeClass('show');
            $(`#max-credit-warning-${index}`).removeClass('show').addClass('d-none');
            
            card.find('.employee-select').val('');
            card.find('.project-name').val('');
            card.find('.location').val('');
            
            updateInvoiceHeader(index);
        }
    });

    // Employee change
    card.find('.employee-select').on('change', function() {
        const project = $(this).find(':selected').data('project') || '';
        card.find('.project-name').val(project);
        updateInvoiceHeader(index);
    });

    card.find('.invoice-number, .location').on('input', function() {
        updateInvoiceHeader(index);
    });

    card.find('.tds-percent').on('input', function() {
        calculateInvoiceTotals(index);
        updateBatchSummary();
    });

    card.find('.bill-file').on('change', function() {
        const file = this.files[0];
        if (file) {
            $(`#filename-${index}`).text(file.name);
            card.find('.file-upload-area').addClass('has-file');
        }
    });
}

// =====================================================
// INVOICE ACTIONS
// =====================================================
function toggleInvoice(index) {
    const body = $(`#body-${index}`);
    const toggle = $(`#toggle-${index}`);
    body.toggleClass('show');
    toggle.toggleClass('expanded');
}

function removeInvoice(index) {
    const card = $(`[data-invoice-index="${index}"]`);
    const isSaved = card.attr('data-is-saved') === 'true';
    const invoiceId = card.attr('data-invoice-id');
    
    if ($('#invoicesContainer .invoice-card').length <= 1) {
        Toast.warning('At least one invoice is required');
        return;
    }
    
    if (!confirm('Remove this invoice?')) return;
    
    if (isSaved && invoiceId) {
        axios.delete(`${API_BASE}/${invoiceId}`)
            .then(res => {
                if (res.data.success) {
                    savedInvoiceIds.delete(parseInt(invoiceId));
                    card.remove();
                    updateInvoiceCount();
                    updateBatchSummary();
                    Toast.success('Invoice deleted');
                }
            })
            .catch(err => Toast.error('Failed to delete invoice'));
    } else {
        card.remove();
        updateInvoiceCount();
        updateBatchSummary();
    }
}

// =====================================================
// EXPENSE ROW HANDLING
// =====================================================
function addExpenseRow(invoiceIndex, data = null) {
    expenseCounters[invoiceIndex]++;
    const expIndex = expenseCounters[invoiceIndex];

    const template = $('#expenseRowTemplate').html().replace(/__EXP_INDEX__/g, expIndex);
    const card = $(`[data-invoice-index="${invoiceIndex}"]`);
    card.find('.expense-rows').append(template);

    const row = card.find(`[data-expense-index="${expIndex}"]`);

    if (data) {
        row.find('.expense-mode').val(data.mode);
        row.find('.expense-particulars').val(data.particulars);
        row.find('.expense-basic').val(data.basic || '');
        row.find('.expense-taxes').val(data.taxes || '');
        row.find('.expense-service').val(data.service_charge || '');
        row.find('.expense-gst').val(data.gst || '');
    }

    row.find('.expense-basic, .expense-taxes, .expense-service, .expense-gst').on('input', function() {
        calculateExpenseRow(row);
        calculateInvoiceTotals(invoiceIndex);
        updateBatchSummary();
        
        // Validate credit amount for credit notes
        if ($(`[data-invoice-index="${invoiceIndex}"]`).attr('data-is-credit-note') === 'true') {
            validateCreditAmount(invoiceIndex);
        }
    });

    calculateExpenseRow(row);
    calculateInvoiceTotals(invoiceIndex);
}

function removeExpenseRow(btn) {
    const row = $(btn).closest('tr');
    const card = row.closest('.invoice-card');
    const invoiceIndex = card.data('invoice-index');

    if (card.find('.expense-rows tr').length <= 1) {
        Toast.warning('At least one expense row is required');
        return;
    }
    row.remove();
    calculateInvoiceTotals(invoiceIndex);
    updateBatchSummary();
}

// =====================================================
// CALCULATIONS
// =====================================================
function calculateExpenseRow(row) {
    const basic = parseFloat(row.find('.expense-basic').val()) || 0;
    const taxes = parseFloat(row.find('.expense-taxes').val()) || 0;
    const service = parseFloat(row.find('.expense-service').val()) || 0;
    const gst = parseFloat(row.find('.expense-gst').val()) || 0;
    row.find('.expense-total').val('₹' + formatNumber(basic + taxes + service + gst));
}

function calculateInvoiceTotals(invoiceIndex) {
    const card = $(`[data-invoice-index="${invoiceIndex}"]`);
    let basic = 0, taxes = 0, service = 0, gst = 0;

    card.find('.expense-rows tr').each(function() {
        basic += parseFloat($(this).find('.expense-basic').val()) || 0;
        taxes += parseFloat($(this).find('.expense-taxes').val()) || 0;
        service += parseFloat($(this).find('.expense-service').val()) || 0;
        gst += parseFloat($(this).find('.expense-gst').val()) || 0;
    });

    const gross = basic + taxes + service + gst;
    const tdsPercent = parseFloat(card.find('.tds-percent').val()) || 0;
    const tdsAmount = (basic * tdsPercent) / 100;
    const netAmount = gross - tdsAmount;

    card.find('.basic-total').text('₹' + formatNumber(basic));
    card.find('.taxes-total').text('₹' + formatNumber(taxes));
    card.find('.service-total').text('₹' + formatNumber(service));
    card.find('.gst-total').text('₹' + formatNumber(gst));
    card.find('.gross-total').text('₹' + formatNumber(gross));
    card.find('.tds-amount').text('₹' + formatNumber(tdsAmount));
    card.find('.net-amount').text('₹' + formatNumber(netAmount));

    updateInvoiceHeader(invoiceIndex);
}

function validateCreditAmount(index) {
    const card = $(`[data-invoice-index="${index}"]`);
    const maxCredit = parseFloat(card.attr('data-max-credit')) || 0;
    const warning = $(`#max-credit-warning-${index}`);
    
    let currentGross = 0;
    card.find('.expense-rows tr').each(function() {
        currentGross += parseFloat($(this).find('.expense-basic').val()) || 0;
        currentGross += parseFloat($(this).find('.expense-taxes').val()) || 0;
        currentGross += parseFloat($(this).find('.expense-service').val()) || 0;
        currentGross += parseFloat($(this).find('.expense-gst').val()) || 0;
    });
    
    if (maxCredit > 0 && currentGross > maxCredit) {
        warning.removeClass('d-none').addClass('show');
        return false;
    } else {
        warning.addClass('d-none').removeClass('show');
        return true;
    }
}

// =====================================================
// HEADER & SUMMARY UPDATES
// =====================================================
function updateInvoiceHeader(index) {
    const card = $(`[data-invoice-index="${index}"]`);
    const empText = card.find('.employee-select option:selected').text();
    const isCN = card.find('.invoice-type').val() === 'credit_note';
    
    card.find('.emp-name').text(empText && empText !== 'Select Employee' ? empText : 'Select Employee');
    card.find('.inv-no').text(card.find('.invoice-number').val() || '-');
    card.find('.loc-name').text(card.find('.location').val() || '-');
    
    const netAmountText = card.find('.net-amount').text();
    if (isCN) {
        card.find('.inv-amount').text('-' + netAmountText).addClass('credit');
    } else {
        card.find('.inv-amount').text(netAmountText).removeClass('credit');
    }
}

function updateBatchSummary() {
    let taxCount = 0, creditCount = 0;
    let totalGross = 0, totalCredit = 0, totalTds = 0;
    let savedCount = 0, unsavedCount = 0;

    $('#invoicesContainer .invoice-card').each(function() {
        const isCN = $(this).attr('data-is-credit-note') === 'true' || $(this).find('.invoice-type').val() === 'credit_note';
        const isSaved = $(this).attr('data-is-saved') === 'true';
        
        if (isSaved) savedCount++; else unsavedCount++;
        
        let invoiceBasic = 0, invoiceGross = 0;
        
        $(this).find('.expense-rows tr').each(function() {
            const basic = parseFloat($(this).find('.expense-basic').val()) || 0;
            invoiceBasic += basic;
            invoiceGross += basic;
            invoiceGross += parseFloat($(this).find('.expense-taxes').val()) || 0;
            invoiceGross += parseFloat($(this).find('.expense-service').val()) || 0;
            invoiceGross += parseFloat($(this).find('.expense-gst').val()) || 0;
        });

        const tdsPercent = parseFloat($(this).find('.tds-percent').val()) || 0;
        const invoiceTds = (invoiceBasic * tdsPercent) / 100;

        if (isCN) {
            creditCount++;
            totalCredit += invoiceGross;
            totalTds -= invoiceTds; // TDS reduced for credit
        } else {
            taxCount++;
            totalGross += invoiceGross;
            totalTds += invoiceTds;
        }
    });

    const netPayable = totalGross - totalCredit - totalTds;

    $('#summaryTaxCount').text(taxCount);
    $('#summaryCreditCount').text(creditCount);
    $('#summaryGross').text('₹' + formatNumber(totalGross));
    $('#summaryCreditAmt').text('-₹' + formatNumber(totalCredit));
    $('#summaryTds').text('-₹' + formatNumber(totalTds));
    $('#summaryNet').text('₹' + formatNumber(netPayable));
    $('#savedInvoiceCount').text(savedCount);
    $('#unsavedInvoiceCount').text(unsavedCount);
    updateInvoiceCount();
}

function updateInvoiceCount() {
    const total = $('#invoicesContainer .invoice-card').length;
    $('#invoiceCount').text(total);
}

// =====================================================
// COLLECT & SAVE INVOICES
// =====================================================
function collectUnsavedInvoices() {
    const invoices = [];
    let hasError = false;

    $('#invoicesContainer .invoice-card[data-is-saved="false"]').each(function() {
        const card = $(this);
        const invoiceType = card.find('.invoice-type').val();
        const isCreditNote = invoiceType === 'credit_note';
        const employeeId = card.find('.employee-select').val();
        const invoiceNumber = card.find('.invoice-number').val().trim();
        const invoiceDate = card.find('.invoice-date').val();
        const location = card.find('.location').val().trim();
        const referenceInvoiceId = card.attr('data-reference-invoice-id');
        const referenceBatchId = card.attr('data-reference-batch-id');

        // Required field validation
        if (!employeeId || !invoiceNumber || !invoiceDate || !location) {
            hasError = true;
            card.addClass('has-error');
            return;
        }
        
        // Credit Note must have reference
        if (isCreditNote && !referenceInvoiceId) {
            hasError = true;
            card.addClass('has-error');
            Toast.error('Please select a reference invoice for Credit Note');
            return;
        }
        
        // Validate credit amount
        if (isCreditNote && !validateCreditAmount(card.data('invoice-index'))) {
            hasError = true;
            card.addClass('has-error');
            Toast.error('Credit amount exceeds original invoice amount');
            return;
        }
        
        card.removeClass('has-error');

        const items = [];
        card.find('.expense-rows tr').each(function() {
            items.push({
                mode: $(this).find('.expense-mode').val(),
                particulars: $(this).find('.expense-particulars').val(),
                basic: parseFloat($(this).find('.expense-basic').val()) || 0,
                taxes: parseFloat($(this).find('.expense-taxes').val()) || 0,
                service_charge: parseFloat($(this).find('.expense-service').val()) || 0,
                gst: parseFloat($(this).find('.expense-gst').val()) || 0
            });
        });

        invoices.push({
            card: card,
            index: card.data('invoice-index'),
            employee_id: employeeId,
            invoice_number: invoiceNumber,
            invoice_date: invoiceDate,
            invoice_type: invoiceType,
         category_id: card.find('.travel-type').val(),
travel_type: card.find('.travel-type option:selected').text() || 'Domestic',
            location: location,
            travel_date: card.find('.travel-date').val() || null,
            tds_percent: parseFloat(card.find('.tds-percent').val()) || 5,
            reference_invoice_id: referenceInvoiceId || null,
            reference_batch_id: referenceBatchId || null,
            items: items,
            file: card.find('.bill-file')[0].files[0] || null
        });
    });

    return hasError ? null : invoices;
}

function saveDraft() {
    const invoices = collectUnsavedInvoices();
    
    if (!invoices) {
        Toast.error('Please fill all required fields (Employee, Invoice No, Invoice Date, Location)');
        return;
    }
    
    if (invoices.length === 0) {
        Toast.info('No new invoices to save. All invoices are already saved.');
        return;
    }
    
    saveInvoices(invoices, false);
}

function submitBatch() {
    const unsavedInvoices = collectUnsavedInvoices();
    
    if (unsavedInvoices === null) {
        Toast.error('Please fill all required fields (Employee, Invoice No, Invoice Date, Location)');
        return;
    }
    
    const totalInvoices = $('#invoicesContainer .invoice-card').length;
    const savedInvoices = $('#invoicesContainer .invoice-card[data-is-saved="true"]').length;
    
    if (totalInvoices === 0 || (savedInvoices === 0 && unsavedInvoices.length === 0)) {
        Toast.error('Please add at least one invoice before submitting');
        return;
    }
    
    if (unsavedInvoices.length > 0) {
        saveInvoices(unsavedInvoices, true);
    } else {
        submitBatchOnly();
    }
}

async function saveInvoices(invoices, submitAfter) {
    const btn = $('#submitBatchBtn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

    try {
        for (const inv of invoices) {
            // DEBUG - Add these lines
            console.log('Invoice type:', inv.invoice_type);
            console.log('Reference batch ID:', inv.reference_batch_id);
            console.log('Current BATCH_ID:', BATCH_ID);
            
            // For Credit Notes, use the reference batch ID (add to SAME batch as reference)
            let batchId = BATCH_ID;
            if (inv.invoice_type === 'credit_note' && inv.reference_batch_id) {
                batchId = inv.reference_batch_id;
            } else {
                await createBatchIfNeeded();
                batchId = BATCH_ID;
            }
            
            // DEBUG - Add this line
            console.log('Final batchId being used:', batchId);
            
            // ... rest of code
            const formData = new FormData();
            formData.append('batch_id', batchId);
            formData.append('employee_id', inv.employee_id);
            formData.append('invoice_number', inv.invoice_number);
            formData.append('invoice_date', inv.invoice_date);
            formData.append('invoice_type', inv.invoice_type);
         formData.append('category_id', inv.category_id);
formData.append('travel_type', inv.travel_type);
            formData.append('location', inv.location);
            formData.append('travel_date', inv.travel_date || '');
            formData.append('tds_percent', inv.tds_percent);
            formData.append('items', JSON.stringify(inv.items));
            
            // Add reference invoice ID for credit notes
            if (inv.reference_invoice_id) {
                formData.append('reference_invoice_id', inv.reference_invoice_id);
            }

            const res = await axios.post(API_BASE, formData);

            if (res.data.success) {
                const savedInvoice = res.data.data;
                
                inv.card.attr('data-invoice-id', savedInvoice.id);
                inv.card.attr('data-is-saved', 'true');
                inv.card.addClass('is-saved');
                $(`#saved-badge-${inv.index}`).removeClass('d-none');
                savedInvoiceIds.add(savedInvoice.id);
                
                // Upload bill if exists
                if (inv.file) {
                    const billData = new FormData();
                    billData.append('bills[]', inv.file);
                    await axios.post(`${API_BASE}/${savedInvoice.id}/bills`, billData);
                }
                
                updateLastInvoiceData(inv);
            }
        }

        // ... after saving all invoices

        updateBatchSummary();

        if (submitAfter) {
            // Only submit if we have a new batch (not just credit notes to existing batch)
            if (BATCH_ID) {
                await submitBatchOnly();
            } else {
                Toast.success('Credit Note saved successfully!');
                setTimeout(() => window.location.href = '{{ route("vendor.travel-invoices.index") }}', 1500);
            }
        } else {
            Toast.success('Draft saved successfully!');
            btn.prop('disabled', false).html('<i class="bi bi-send me-2"></i>Submit Batch');
            addNewInvoice();
        }
    } catch (err) {
        // ...
    }
}

async function submitBatchOnly() {
    const btn = $('#submitBatchBtn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');
    
    try {
        await axios.post(`${API_BASE}/batches/${BATCH_ID}/submit`);
        Toast.success('Batch submitted successfully!');
        setTimeout(() => window.location.href = '{{ route("vendor.travel-invoices.index") }}', 1500);
    } catch (err) {
        Toast.error(err.response?.data?.message || 'Failed to submit batch');
        btn.prop('disabled', false).html('<i class="bi bi-send me-2"></i>Submit Batch');
    }
}

// =====================================================
// UTILITY
// =====================================================
function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>
@endpush