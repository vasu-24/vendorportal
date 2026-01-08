@extends('layouts.app')
@section('title', 'Invoice Details')

@section('content')
<style>
    /* Clean Layout - No Scroll */
    body { overflow:auto; }
    
   .page-container {
    min-height: calc(100vh - 80px);
    overflow-y: auto;  /* Allow scroll */
}
    
    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    
    .page-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }
    
    .page-subtitle {
        font-size: 12px;
        color: #6b7280;
    }
    
    /* Cards */
    .card-custom {
        background: white;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        height: 100%;
    }
    
    .card-header-custom {
        padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .card-body-custom {
        padding: 12px 14px;
    }
    
.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;  /* Add this */
}

.edit-field {
    max-width: 150px;  /* Limit width */
}
    
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6b7280; }
    .info-value { font-weight: 600; color: #111827; }
    
    /* Status Badges */
    .badge-status {
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .badge-submitted { background: #e0e7ff; color: #3730a3; }
    .badge-pending_rm { background: #fef3c7; color: #92400e; }
    .badge-pending_vp { background: #dbeafe; color: #1e40af; }
    .badge-pending_ceo { background: #fce7f3; color: #9d174d; }
    .badge-pending_finance { background: #d1fae5; color: #065f46; }
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }
    .badge-paid { background: #dbeafe; color: #1e40af; }
    
    /* Line Items Table */
    .items-table {
        width: 100%;
        font-size: 11px;
        border-collapse: collapse;
    }
    
    .items-table th {
        background: #f9fafb;
        padding: 8px 6px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .items-table td {
        padding: 8px 6px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    
    .items-table .text-end { text-align: right; }
    .items-table .text-center { text-align: center; }
    
    /* Tag Select */
    .tag-select {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px solid #d1d5db;
        min-width: 100px;
    }
    
    .tag-badge {
        background: #e0e7ff;
        color: #3730a3;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 500;
    }
    
    /* Amount Summary */
    .amount-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        font-size: 12px;
    }
    
    .amount-row.total {
        border-top: 2px solid #e5e7eb;
        margin-top: 6px;
        padding-top: 8px;
        font-weight: 700;
    }
    
    .amount-row.net {
        background: #ecfdf5;
        margin: 8px -14px -12px;
        padding: 10px 14px;
        border-radius: 0 0 10px 10px;
        color: #059669;
        font-weight: 700;
    }
    
    /* Attachments */
    .attachment-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        background: #f9fafb;
        border-radius: 6px;
        margin-bottom: 6px;
        font-size: 11px;
    }
    
    .att-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
    }
    
    .att-icon.pdf { background: #fee2e2; color: #dc2626; }
    .att-icon.excel { background: #d1fae5; color: #059669; }
    
    /* Action Buttons */
    .btn-action {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .btn-approve { background: #059669; color: white; }
    .btn-approve:hover { background: #047857; color: white; }
    
    .btn-reject { background: white; color: #dc2626; border: 1px solid #dc2626; }
    .btn-reject:hover { background: #dc2626; color: white; }
    
    .btn-summary {
        background: #3B82F6;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .btn-summary:hover { opacity: 0.9; color: white; }
    
    .btn-save { background: #3b82f6; color: white; }
    .btn-save:hover { background: #2563eb; color: white; }
    
    /* Rejection Alert */
    .rejection-box {
        background: #fef2f2;
        border-left: 3px solid #dc2626;
        padding: 8px 12px;
        border-radius: 0 6px 6px 0;
        font-size: 12px;
        margin-bottom: 12px;
    }
    
    /* ============================================= */
    /* SUMMARY MODAL STYLES */
    /* ============================================= */
    
    .modal-summary .modal-dialog {
        max-width: 800px;
    }
    
    .modal-summary .modal-content {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }
    .modal-summary .modal-header {
        background: #174081;
        color: white;
        padding: 16px 20px;
        border: none;
    }
    
    .modal-summary .modal-body {
        padding: 0;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .modal-summary .modal-footer {
        border-top: 1px solid #f3f4f6;
        padding: 12px 20px;
    }
    
    /* Summary Sections */
    .summary-section {
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .summary-section:last-child { border-bottom: none; }
    
    .summary-title {
        font-size: 12px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Approval Flow */
    .approval-flow {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        position: relative;
        padding: 10px 0;
    }
    
    .approval-flow::before {
        content: '';
        position: absolute;
        top: 30px;
        left: 40px;
        right: 40px;
        height: 3px;
        background: #e5e7eb;
        z-index: 0;
    }
    
    .approval-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
    }
    
    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        margin-bottom: 8px;
        border: 3px solid white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    
    .step-icon.waiting { background: #e5e7eb; color: #9ca3af; }
    .step-icon.current { background: #f59e0b; color: white; animation: pulse 2s infinite; }
    .step-icon.done { background: #10b981; color: white; }
    .step-icon.rejected { background: #ef4444; color: white; }
    .step-icon.skipped { background: #d1d5db; color: #9ca3af; opacity: 0.5; }
    
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        50% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
    }
    
    .step-label {
        font-size: 11px;
        font-weight: 600;
        color: #374151;
    }
    
    .step-name {
        font-size: 10px;
        color: #9ca3af;
    }
    
    /* Comparison Table */
    .comparison-table {
        width: 100%;
        font-size: 11px;
        border-collapse: collapse;
    }
    
    .comparison-table th {
        background: #f9fafb;
        padding: 8px;
        text-align: left;
        font-weight: 600;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .comparison-table td {
        padding: 8px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .comparison-table .text-end { text-align: right; }
    .comparison-table .text-center { text-align: center; }
    
    .match-ok { color: #10b981; }
    .match-warn { color: #f59e0b; }
    .match-error { color: #ef4444; }
    
    /* Contract Summary Box */
    .contract-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        background: #f9fafb;
        padding: 12px;
        border-radius: 8px;
    }
    
    .contract-item {
        text-align: center;
    }
    
    .contract-item-label {
        font-size: 10px;
        color: #6b7280;
        margin-bottom: 4px;
    }
    
    .contract-item-value {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }
    
    .contract-item-value.success { color: #059669; }
    .contract-item-value.warning { color: #f59e0b; }
    .contract-item-value.danger { color: #dc2626; }
    
    /* Contract Type Badge */
    .contract-type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        margin-left: 8px;
    }
    
    .contract-type-badge.normal {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .contract-type-badge.adhoc {
        background: #fef3c7;
        color: #92400e;
    }
    
    /* Activity Timeline */
    .activity-item {
        display: flex;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 12px;
    }
    
    .activity-item:last-child { border-bottom: none; }
    
    .activity-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-top: 4px;
        flex-shrink: 0;
    }
    
    .activity-dot.blue { background: #3b82f6; }
    .activity-dot.green { background: #10b981; }
    .activity-dot.yellow { background: #f59e0b; }
    .activity-dot.red { background: #ef4444; }
    
    .activity-text { flex: 1; }
    .activity-time { font-size: 10px; color: #9ca3af; }
    
    /* Edit Fields */
    .edit-field {
        font-size: 11px;
        padding: 2px 6px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
    }
    
    /* ADHOC Info Box */
    .adhoc-info-box {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
    }
    
    .adhoc-info-box .label {
        font-size: 10px;
        color: #92400e;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .adhoc-info-box .value {
        font-size: 14px;
        font-weight: 700;
        color: #78350f;
    }
</style>

<div class="page-container">
    
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title" id="pageInvoiceNumber">Loading...</h1>
                <p class="page-subtitle"><span id="pageVendorName">-</span> • <span id="pageContractNumber">-</span></p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="pageStatus"></span>
            <button class="btn-summary" onclick="openSummaryModal()">
                <i class="bi bi-eye me-1"></i>Summary
            </button>
        </div>
    </div>
    
    <!-- Rejection Alert -->
    <div id="rejectionBox" class="rejection-box" style="display: none;">
        <strong class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Rejected:</strong>
        <span id="rejectionReason"></span>
    </div>
    
    <!-- Main Content -->
    <div class="row g-3">
        
        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="row g-3">
                
                <!-- Invoice Info -->
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="bi bi-receipt text-primary"></i>Invoice Info
                        </div>
                        <div class="card-body-custom">
                            <div class="info-row">
                                <span class="info-label">Invoice #</span>
                                <span class="info-value" id="invoiceNumberDisplay">-</span>
                                <input type="text" class="form-control form-control-sm edit-field" id="invoiceNumberInput" style="display:none; width: 120px;">
                            </div>
                            <div class="info-row">
                                <span class="info-label">Contract</span>
                                <span class="info-value text-primary" id="contractNumber">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Invoice Date</span>
                                <span class="info-value" id="invoiceDateDisplay">-</span>
                                <input type="date" class="form-control form-control-sm edit-field" id="invoiceDateInput" style="display:none; width: 130px;">
                            </div>
                            <div class="info-row">
                                <span class="info-label">Due Date</span>
                                <span class="info-value" id="dueDateDisplay">-</span>
                                <input type="date" class="form-control form-control-sm edit-field" id="dueDateInput" style="display:none; width: 130px;">
                            </div>
                            <div class="info-row">
                                <span class="info-label">Description</span>
                                <span class="info-value" id="descriptionDisplay">-</span>
                                <input type="text" class="form-control form-control-sm edit-field" id="descriptionInput" style="display:none; width: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Vendor Info -->
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="bi bi-building text-primary"></i>Vendor Info
                        </div>
                        <div class="card-body-custom">
                            <div class="info-row">
                                <span class="info-label">Name</span>
                                <span class="info-value" id="vendorName">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Company</span>
                                <span class="info-value" id="vendorCompany">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value" id="vendorEmail">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phone</span>
                                <span class="info-value" id="vendorPhone">-</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">GSTIN</span>
                                <span class="info-value" id="vendorGstin">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Line Items -->
                <div class="col-12">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="bi bi-list-ul text-primary"></i>Line Items
                            <span class="ms-auto badge bg-secondary" id="itemsCount">0 items</span>
                        </div>
                        <div class="card-body-custom p-0">
                            <div style="max-height: 180px; overflow-y: auto;">
                                <table class="items-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;">#</th>
                                            <th>Category</th>
                                            <th>Particulars</th>
                                            <th class="text-center" style="width: 60px;">Qty</th>
                                            <th class="text-end" style="width: 80px;">Rate</th>
                                            <th class="text-end" style="width: 90px;">Amount</th>
                                            <th style="width: 110px;">Tag</th>
                                        </tr>
                                    </thead>
                                    <tbody id="lineItemsBody">
                                        <tr><td colspan="7" class="text-center py-3 text-muted">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="row g-3">
                
                <!-- Amount Summary -->
                <div class="col-12">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="bi bi-calculator text-primary"></i>Amount Summary
                        </div>
                        <div class="card-body-custom">
                            <div class="amount-row">
                                <span class="text-muted">Base Total</span>
                                <span class="fw-semibold" id="baseTotal">₹0</span>
                            </div>
                            <div class="amount-row">
                                <span class="text-muted">GST (<span id="gstPercentDisplay">18</span><input type="number" class="form-control form-control-sm edit-field" id="gstPercentInput" style="display:none; width: 60px;" step="0.01" onchange="recalculateTotals()">%)</span>
                                <span id="gstAmount">₹0</span>
                            </div>
                            <div class="amount-row total">
                                <span>Grand Total</span>
                                <span id="grandTotal">₹0</span>
                            </div>
                           <div class="amount-row">
    <span class="text-muted">
        TDS (<span id="tdsPercentDisplay">5</span>%)
    </span>
    <span class="text-danger" id="tdsAmount">-₹0</span>
</div>
<!-- TDS Dropdown (Finance Edit) -->
<div class="amount-row edit-field" id="tdsDropdownRow" style="display:none;">
    <span class="text-muted">TDS Type</span>
    <select class="form-select form-select-sm" id="tdsSelect" style="width: 180px; font-size: 11px;" onchange="onTdsSelectChange()">
        <option value="">Loading...</option>
    </select>
</div>
                            <div class="amount-row net">
                                <span>Net Payable</span>
                                <span id="netPayable">₹0</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attachments -->
                <div class="col-12">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="bi bi-paperclip text-primary"></i>Attachments
                        </div>
                        <div class="card-body-custom" id="attachmentsContainer">
                            <div class="text-muted text-center py-2" style="font-size: 12px;">No attachments</div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="col-12">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="bi bi-lightning text-primary"></i>Actions
                        </div>
                        <div class="card-body-custom" id="actionsContainer">
                            <div class="text-muted text-center py-2" style="font-size: 12px;">Loading...</div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<!-- ============================================= -->
<!-- SUMMARY MODAL -->
<!-- ============================================= -->
<div class="modal fade modal-summary" id="summaryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold">
                        <span id="modalInvoiceNumber">Invoice Summary</span>
                        <span class="contract-type-badge" id="contractTypeBadge"></span>
                    </h5>
                    <small class="opacity-75" id="modalVendorName">Vendor</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <!-- Approval Flow -->
                <div class="summary-section">
                    <div class="summary-title">
                        <i class="bi bi-diagram-3"></i>Approval Flow
                    </div>
                    <div class="approval-flow" id="approvalFlow">
                        <!-- Dynamic -->
                    </div>
                </div>
                
                <!-- Contract vs Invoice -->
                <div class="summary-section">
                    <div class="summary-title">
                        <i class="bi bi-file-diff"></i><span id="comparisonTitle">Contract vs Invoice Comparison</span>
                    </div>
                    
                    <!-- Contract/SOW Summary -->
                    <div class="contract-summary mb-3" id="contractSummaryBox">
                        <div class="contract-item">
                            <div class="contract-item-label" id="valueLabel">Contract Value</div>
                            <div class="contract-item-value" id="contractValue">₹0</div>
                        </div>
                        <div class="contract-item">
                            <div class="contract-item-label">Previous Used</div>
                            <div class="contract-item-value" id="previousUsed">₹0</div>
                        </div>
                        <div class="contract-item">
                            <div class="contract-item-label">This Invoice</div>
                            <div class="contract-item-value" id="thisInvoice">₹0</div>
                        </div>
                        <div class="contract-item">
                            <div class="contract-item-label">Remaining</div>
                            <div class="contract-item-value success" id="remaining">₹0</div>
                        </div>
                    </div>
                    
                    <!-- Line Items Comparison (Only for Normal Contracts) -->
                    <div id="comparisonTableContainer">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th class="text-center">Con. Qty</th>
                                    <th class="text-center">Inv. Qty</th>
                                    <th class="text-end">Con. Rate</th>
                                    <th class="text-end">Inv. Rate</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="comparisonBody">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- ADHOC Info (Only for ADHOC Contracts) -->
                    <div id="adhocInfoContainer" style="display: none;">
                        <div class="adhoc-info-box">
                            <div class="row">
                                <div class="col-6">
                                    <div class="label">Category</div>
                                    <div class="value" id="adhocCategory">-</div>
                                </div>
                                <div class="col-6">
                                    <div class="label">Tag / Manager</div>
                                    <div class="value" id="adhocTag">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Timeline -->
                <div class="summary-section">
                    <div class="summary-title">
                        <i class="bi bi-clock-history"></i>Activity Timeline
                    </div>
                    <div id="activityTimeline">
                        <!-- Dynamic -->
                    </div>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================= -->
<!-- REJECT MODAL -->
<!-- ============================================= -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 10px;">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Invoice</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                <textarea class="form-control" id="rejectReasonInput" rows="3" placeholder="Enter reason..."></textarea>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" id="confirmRejectBtn">
                    <i class="bi bi-x-circle me-1"></i>Reject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================= -->
<!-- MARK PAID MODAL -->
<!-- ============================================= -->
<div class="modal fade" id="paidModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 10px;">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title"><i class="bi bi-check-circle me-2"></i>Mark as Paid</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Payment Reference / UTR</label>
                    <input type="text" class="form-control form-control-sm" id="paymentRefInput" placeholder="Transaction ID">
                </div>
                <div>
                    <label class="form-label small fw-semibold">Notes</label>
                    <textarea class="form-control form-control-sm" id="paymentNotesInput" rows="2" placeholder="Optional..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-success" id="confirmPaidBtn">
                    <i class="bi bi-check-circle me-1"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const API_BASE = '/api/admin/invoices';
const INVOICE_ID = '{{ $invoiceId }}';
const USER_ROLE = '{{ auth()->user()->role->slug ?? "super-admin" }}';

let canEdit = false;
let canChangeTag = false;

let invoiceData = null;
let contractData = null;
let availableTags = [];

$(document).ready(function() {
    // Load tags first, then invoice
    loadTags().then(() => {
        loadInvoice();
    });
    
    $('#confirmRejectBtn').on('click', rejectInvoice);
    $('#confirmPaidBtn').on('click', markAsPaid);
});

// =====================================================
// LOAD INVOICE
// =====================================================
function loadInvoice() {
    axios.get(`${API_BASE}/${INVOICE_ID}`)
        .then(res => {
            if (res.data.success) {
                invoiceData = res.data.data;
                contractData = invoiceData.contract;
                renderPage();
            }
        })
        .catch(err => {
            console.error('Failed to load invoice:', err);
            alert('Failed to load invoice');
        });
}

// =====================================================
// LOAD AVAILABLE TAGS
// =====================================================
function loadTags() {
    return axios.get('/api/admin/manager-tags/assigned-tags-dropdown')
        .then(res => {
            if (res.data.success) {
                availableTags = res.data.data;
                console.log('Tags loaded:', availableTags.length);
            }
        })
        .catch(err => {
            console.log('Tags load failed');
            availableTags = [];
        });
}

// TDS Data
let zohoTdsTaxes = [];

// Load TDS when page loads
$(document).ready(function() {
    loadTags().then(() => {
        loadInvoice();
    });
    loadZohoTds();  // 👈 ADD THIS
    
    $('#confirmRejectBtn').on('click', rejectInvoice);
    $('#confirmPaidBtn').on('click', markAsPaid);
});

// =====================================================
// LOAD ZOHO TDS
// =====================================================
function loadZohoTds() {
    axios.get('/api/zoho/taxes')
        .then(res => {
            if (res.data.success) {
                zohoTdsTaxes = res.data.data.tds || [];
                console.log('Loaded TDS from Zoho:', zohoTdsTaxes.length);
            }
        })
        .catch(err => {
            console.error('Failed to load Zoho TDS:', err);
            zohoTdsTaxes = [];
        });
}

// =====================================================
// POPULATE TDS DROPDOWN
// =====================================================
function populateTdsDropdown(currentTaxId, currentPercent) {
    let options = '<option value="">-- Select TDS --</option>';
    
    if (zohoTdsTaxes.length === 0) {
        // Fallback if API not loaded
        options += `
            <option value="" data-percent="1" ${currentPercent == 1 ? 'selected' : ''}>TDS 1%</option>
            <option value="" data-percent="2" ${currentPercent == 2 ? 'selected' : ''}>TDS 2%</option>
            <option value="" data-percent="5" ${currentPercent == 5 ? 'selected' : ''}>TDS 5%</option>
            <option value="" data-percent="10" ${currentPercent == 10 ? 'selected' : ''}>TDS 10%</option>
        `;
    } else {
        zohoTdsTaxes.forEach(tax => {
            const selected = (tax.tax_id === currentTaxId) || 
                           (parseFloat(tax.tax_percentage) === parseFloat(currentPercent)) ? 'selected' : '';
            options += `<option value="${tax.tax_id}" data-percent="${tax.tax_percentage}" ${selected}>
                ${tax.tax_name} (${tax.tax_percentage}%)
            </option>`;
        });
    }
    
    $('#tdsSelect').html(options);
}

// =====================================================
// ON TDS SELECT CHANGE
// =====================================================
function onTdsSelectChange() {
    const selected = $('#tdsSelect option:selected');
    const percent = selected.data('percent') || 0;
    
    $('#tdsPercentDisplay').text(percent);
    
    recalculateTotals();
}


// =====================================================
// CHECK IF CONTRACT IS ADHOC
// =====================================================
function isAdhocContract() {
    return contractData && contractData.contract_type === 'adhoc';
}

// =====================================================
// GET CONTRACT/SOW VALUE
// =====================================================
function getContractOrSowValue() {
    if (!contractData) return 0;
    
    if (isAdhocContract()) {
        // For ADHOC: use sow_value or contract_value (they store SOW value in contract_value)
        return parseFloat(contractData.sow_value) || parseFloat(contractData.contract_value) || 0;
    }
    
    // For Normal: use contract_value
    return parseFloat(contractData.contract_value) || 0;
}

// =====================================================
// RENDER PAGE
// =====================================================
function renderPage() {
    const inv = invoiceData;

    // Set edit permissions
    canEdit = (USER_ROLE === 'finance' && inv.status === 'pending_finance') || (USER_ROLE === 'super-admin' && inv.status === 'pending_finance');
    canChangeTag = (USER_ROLE === 'manager' && inv.status === 'pending_rm') || USER_ROLE === 'super-admin';
    
    // Header
    $('#pageInvoiceNumber').text(inv.invoice_number);
    $('#pageVendorName').text(inv.vendor?.vendor_name || '-');
    $('#pageContractNumber').text(inv.contract?.contract_number || '-');
    $('#pageStatus').html(getStatusBadge(inv.status));
    
    // Invoice Info - Display values
    $('#invoiceNumberDisplay').text(inv.invoice_number);
    $('#contractNumber').text(inv.contract?.contract_number || '-');
    $('#invoiceDateDisplay').text(formatDate(inv.invoice_date));
    $('#dueDateDisplay').text(inv.due_date ? formatDate(inv.due_date) : '-');
    $('#descriptionDisplay').text(inv.description || '-');
    
    // Invoice Info - Edit inputs
    $('#invoiceNumberInput').val(inv.invoice_number);
    $('#invoiceDateInput').val(inv.invoice_date ? inv.invoice_date.split('T')[0] : '');
    $('#dueDateInput').val(inv.due_date ? inv.due_date.split('T')[0] : '');
    $('#descriptionInput').val(inv.description || '');
    $('#gstPercentInput').val(inv.gst_percent || 18);
    $('#tdsPercentInput').val(inv.tds_percent || 5);
    
 // Show edit fields if Finance can edit
if (canEdit) {
    $('.edit-field').show();
    $('#invoiceNumberDisplay, #invoiceDateDisplay, #dueDateDisplay, #descriptionDisplay, #gstPercentDisplay').hide();
    $('#tdsDropdownRow').show();
    
    // Populate TDS dropdown
    populateTdsDropdown(inv.tds_tax_id, inv.tds_percent);
}
    
    // Vendor Info
    $('#vendorName').text(inv.vendor?.vendor_name || '-');
    $('#vendorCompany').text(inv.vendor?.company_info?.legal_entity_name || '-');
    $('#vendorEmail').text(inv.vendor?.vendor_email || '-');
    $('#vendorPhone').text(inv.vendor?.contact?.mobile || '-');
    $('#vendorGstin').text(inv.vendor?.statutory_info?.gstin || '-');
    
    // Calculate amounts
    const baseTotal = parseFloat(inv.base_total) || 0;
    const gstPercent = parseFloat(inv.gst_percent) || 18;
    const tdsPercent = parseFloat(inv.tds_percent) || 5;
    
    const gstAmount = (baseTotal * gstPercent) / 100;
    const grandTotal = baseTotal + gstAmount;
    const tdsAmount = (baseTotal * tdsPercent) / 100;
    const netPayable = grandTotal - tdsAmount;
    
    $('#baseTotal').text('₹' + formatNum(baseTotal));
    $('#gstPercentDisplay').text(gstPercent);
    $('#gstAmount').text('₹' + formatNum(gstAmount));
    $('#grandTotal').text('₹' + formatNum(grandTotal));
    $('#tdsPercentDisplay').text(tdsPercent);
    $('#tdsAmount').text('-₹' + formatNum(tdsAmount));
    $('#netPayable').text('₹' + formatNum(netPayable));
    
    // Rejection
    if (inv.rejection_reason) {
        $('#rejectionReason').text(inv.rejection_reason);
        $('#rejectionBox').show();
    }
    
    // Render sections
    renderLineItems(inv.items);
    renderAttachments(inv.attachments);
    renderActions(inv.status);
}

// =====================================================
// RENDER LINE ITEMS
// =====================================================
function renderLineItems(items) {
    if (!items || items.length === 0) {
        $('#lineItemsBody').html('<tr><td colspan="7" class="text-center py-4 text-muted">No line items</td></tr>');
        $('#itemsCount').text('0');
        return;
    }
    
    $('#itemsCount').text(items.length);
    
    let html = '';
    items.forEach((item, i) => {
        const categoryName = item.contract_item?.category?.name || item.category?.name || '-';
        const tagId = invoiceData.assigned_tag_id || item.contract_item?.tag_id || '';
        const tagName = invoiceData.assigned_tag_name || item.contract_item?.tag_name || '';
        
        // Tag cell - dropdown for RM, badge for others
        let tagCell = '';
        if (canChangeTag) {
            tagCell = `
                <select class="tag-select" onchange="changeInvoiceTag(this.value)">
                    <option value="">Select Tag</option>
                    ${availableTags.map(t => `<option value="${t.tag_id}" data-name="${t.tag_name}" ${t.tag_id === tagId ? 'selected' : ''}>${t.tag_name}</option>`).join('')}
                </select>
            `;
        } else {
            tagCell = tagName ? `<span class="tag-badge">${tagName}</span>` : '<span class="text-muted">-</span>';
        }
        
        // Line item cells - editable for Finance
        let qtyCell = canEdit ? 
            `<input type="number" class="form-control form-control-sm text-center item-qty" data-item-id="${item.id}" value="${item.quantity}" style="width: 60px;" onchange="recalculateItem(${item.id})">` : 
            item.quantity;
            
        let rateCell = canEdit ? 
            `<input type="number" class="form-control form-control-sm text-end item-rate" data-item-id="${item.id}" value="${item.rate}" style="width: 80px;" onchange="recalculateItem(${item.id})">` : 
            '₹' + formatNum(item.rate);
            
        let amountCell = canEdit ? 
            `<span class="item-amount" data-item-id="${item.id}">₹${formatNum(item.amount)}</span>` : 
            '₹' + formatNum(item.amount);
        
        html += `
            <tr data-item-id="${item.id}">
                <td>${i + 1}</td>
                <td>${categoryName}</td>
                <td>${item.particulars || '-'}</td>
                <td class="text-center">${qtyCell}</td>
                <td class="text-end">${rateCell}</td>
                <td class="text-end fw-bold">${amountCell}</td>
                <td>${tagCell}</td>
            </tr>
        `;
    });
    
    $('#lineItemsBody').html(html);
}

// =====================================================
// RENDER ATTACHMENTS
// =====================================================
function renderAttachments(attachments) {
    let html = '';
    
    if (attachments && attachments.length > 0) {
        attachments.forEach(att => {
            const isExcel = att.file_name?.match(/\.(xlsx|xls)$/i);
            const iconClass = isExcel ? 'excel' : 'pdf';
            const icon = isExcel ? 'bi-file-earmark-excel' : 'bi-file-earmark-pdf';
            const typeLabel = att.attachment_type === 'invoice' ? 'Invoice Document' : 
                              att.attachment_type === 'timesheet' ? 'Timesheet' : 'Document';
            
            html += `
                <div class="attachment-item">
                    <div class="att-icon ${iconClass}"><i class="bi ${icon}"></i></div>
                    <div class="att-info">
                        <div class="att-name">${att.file_name || 'Document'}</div>
                        <div class="att-type">${typeLabel}</div>
                    </div>
                    <a href="${API_BASE}/${INVOICE_ID}/attachment/${att.id}/download" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="bi bi-download"></i>
                    </a>
                </div>
            `;
        });
    }
    
    const inv = invoiceData;
    if (inv.include_timesheet && inv.timesheet_path) {
        html += `
            <div class="attachment-item">
                <div class="att-icon excel"><i class="bi bi-file-earmark-excel"></i></div>
                <div class="att-info">
                    <div class="att-name">${inv.timesheet_filename || 'Timesheet.xlsx'}</div>
                    <div class="att-type">Timesheet</div>
                </div>
                <a href="/storage/${inv.timesheet_path}" class="btn btn-sm btn-outline-success" target="_blank">
                    <i class="bi bi-download"></i>
                </a>
            </div>
        `;
    }
    
    if (!html) {
        html = '<div class="text-muted text-center py-3">No attachments</div>';
    }
    
    $('#attachmentsContainer').html(html);
}

// =====================================================
// RENDER ACTIONS
// =====================================================
function renderActions(status) {
    let html = '';
    
    const canApprove = (
        (status === 'submitted') ||
        (status === 'pending_rm' && USER_ROLE === 'manager') ||
        (status === 'pending_vp' && USER_ROLE === 'vp') ||
        (status === 'pending_ceo' && USER_ROLE === 'ceo') ||
        (status === 'pending_finance' && USER_ROLE === 'finance') ||
        (USER_ROLE === 'super-admin')
    );
    
    if (canApprove && !['approved', 'rejected', 'paid'].includes(status)) {
        html = `
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn-action btn-approve flex-grow-1" onclick="approveInvoice()">
                    <i class="bi bi-check-circle"></i>Approve
                </button>
                <button class="btn-action btn-reject" onclick="showRejectModal()">
                    <i class="bi bi-x-circle"></i>Reject
                </button>
                ${canEdit ? `<button class="btn-action btn-save w-100 mt-2" onclick="saveInvoice()"><i class="bi bi-save"></i>Save Changes</button>` : ''}
            </div>
        `;
    } else if (status === 'approved' && (USER_ROLE === 'finance' || USER_ROLE === 'super-admin')) {
        html = `
            <button class="btn-action btn-approve w-100" onclick="showPaidModal()">
                <i class="bi bi-currency-rupee"></i>Mark as Paid
            </button>
        `;
    } else if (status === 'paid') {
        html = `
            <div class="text-center py-2">
                <i class="bi bi-check-circle-fill text-success fs-3"></i>
                <div class="text-muted mt-1" style="font-size: 12px;">Payment Completed</div>
            </div>
        `;
    } else if (status === 'rejected') {
        html = `
            <div class="text-center py-2">
                <i class="bi bi-x-circle-fill text-danger fs-3"></i>
                <div class="text-muted mt-1" style="font-size: 12px;">Invoice Rejected</div>
            </div>
        `;
    } else {
        html = `<div class="text-muted text-center py-2" style="font-size: 12px;">Waiting for approval</div>`;
    }
    
    $('#actionsContainer').html(html);
}

// =====================================================
// SAVE INVOICE (Finance)
// =====================================================

// =====================================================
// SAVE INVOICE (Finance)
// =====================================================
function saveInvoice() {
    if (!confirm('Save changes to this invoice?')) return;
    
    const selectedTds = $('#tdsSelect option:selected');
    const tdsPercent = parseFloat(selectedTds.data('percent')) || parseFloat($('#tdsPercentDisplay').text()) || 5;
    const tdsTaxId = selectedTds.val() || null;

    const data = {
        invoice_number: $('#invoiceNumberInput').val(),
        invoice_date: $('#invoiceDateInput').val(),
        due_date: $('#dueDateInput').val(),
        description: $('#descriptionInput').val(),
        gst_percent: parseFloat($('#gstPercentInput').val()) || 18,
        tds_percent: tdsPercent,
     zoho_tds_tax_id: tdsTaxId,
        items: []
    };
    
    // Collect line items
    $('#lineItemsBody tr').each(function() {
        const itemId = $(this).data('item-id');
        if (itemId) {
            data.items.push({
                id: itemId,
                quantity: parseFloat($(this).find('.item-qty').val()) || 0,
                rate: parseFloat($(this).find('.item-rate').val()) || 0,
                amount: parseFloat($(this).find('.item-qty').val()) * parseFloat($(this).find('.item-rate').val()) || 0
            });
        }
    });
    
    // Calculate totals
    let baseTotal = 0;
    data.items.forEach(item => baseTotal += item.amount);
    
    data.base_total = baseTotal;
    data.gst_total = (baseTotal * data.gst_percent) / 100;
    data.grand_total = baseTotal + data.gst_total;
    data.tds_amount = (baseTotal * data.tds_percent) / 100;
    data.net_payable = data.grand_total - data.tds_amount;
    
    axios.put(`${API_BASE}/${INVOICE_ID}/update`, data)
        .then(res => {
            if (res.data.success) {
                showToast('success', 'Invoice saved successfully');
                loadInvoice();
            }
        })
        .catch(err => showToast('error', err.response?.data?.message || 'Failed to save'));
}


// =====================================================
// RECALCULATE ITEM
// =====================================================
function recalculateItem(itemId) {
    const row = $(`tr[data-item-id="${itemId}"]`);
    const qty = parseFloat(row.find('.item-qty').val()) || 0;
    const rate = parseFloat(row.find('.item-rate').val()) || 0;
    const amount = qty * rate;
    
    row.find('.item-amount').text('₹' + formatNum(amount));
    
    recalculateTotals();
}

// =====================================================
// RECALCULATE TOTALS
// =====================================================


function recalculateTotals() {
    let baseTotal = 0;
    
    $('#lineItemsBody tr').each(function() {
        const qty = parseFloat($(this).find('.item-qty').val()) || 0;
        const rate = parseFloat($(this).find('.item-rate').val()) || 0;
        baseTotal += qty * rate;
    });
    
    const gstPercent = parseFloat($('#gstPercentInput').val()) || 18;
    
    // Get TDS from dropdown or display
    const selectedTds = $('#tdsSelect option:selected');
    const tdsPercent = parseFloat(selectedTds.data('percent')) || parseFloat($('#tdsPercentDisplay').text()) || 5;
    
    const gstAmount = (baseTotal * gstPercent) / 100;
    const grandTotal = baseTotal + gstAmount;
    const tdsAmount = (baseTotal * tdsPercent) / 100;
    const netPayable = grandTotal - tdsAmount;
    
    $('#baseTotal').text('₹' + formatNum(baseTotal));
    $('#gstAmount').text('₹' + formatNum(gstAmount));
    $('#grandTotal').text('₹' + formatNum(grandTotal));
    $('#tdsAmount').text('-₹' + formatNum(tdsAmount));
    $('#netPayable').text('₹' + formatNum(netPayable));
}



// =====================================================
// CHANGE INVOICE TAG (RM)
// =====================================================
function changeInvoiceTag(newTagId) {
    if (!newTagId) return;
    
    const tag = availableTags.find(t => t.tag_id === newTagId);
    if (!tag) return;
    
    if (!confirm(`Change tag to "${tag.tag_name}"? Invoice will be reassigned to the manager of this tag.`)) {
        loadInvoice();
        return;
    }
    
    axios.post(`${API_BASE}/${INVOICE_ID}/change-tag`, {
        tag_id: newTagId,
        tag_name: tag.tag_name
    })
    .then(res => {
        if (res.data.success) {
            showToast('success', res.data.message);
            setTimeout(() => {
                window.location.href = '{{ route("invoices.index") }}';
            }, 1500);
        }
    })
    .catch(err => showToast('error', err.response?.data?.message || 'Failed to change tag'));
}

// =====================================================
// OPEN SUMMARY MODAL
// =====================================================
function openSummaryModal() {
    const modal = new bootstrap.Modal(document.getElementById('summaryModal'));
    modal.show();
    
    renderSummaryModal();
}

// =====================================================
// RENDER SUMMARY MODAL - DYNAMIC FOR CONTRACT/ADHOC
// =====================================================
function renderSummaryModal() {
    const inv = invoiceData;
    const contract = contractData;
    const isAdhoc = isAdhocContract();
    
    $('#modalInvoiceNumber').text(inv.invoice_number);
    $('#modalVendorName').text(inv.vendor?.vendor_name || '-');
    
    // Contract Type Badge
    if (isAdhoc) {
        $('#contractTypeBadge').text('ADHOC').removeClass('normal').addClass('adhoc').show();
        $('#comparisonTitle').text('SOW vs Invoice Comparison');
        $('#valueLabel').text('SOW Value');
    } else {
        $('#contractTypeBadge').text('CONTRACT').removeClass('adhoc').addClass('normal').show();
        $('#comparisonTitle').text('Contract vs Invoice Comparison');
        $('#valueLabel').text('Contract Value');
    }
    
    renderApprovalFlow(inv, contract);
    renderContractSummary(inv, contract);
    
    // Show/Hide comparison table based on contract type
    if (isAdhoc) {
        $('#comparisonTableContainer').hide();
        $('#adhocInfoContainer').show();
        renderAdhocInfo(inv, contract);
    } else {
        $('#comparisonTableContainer').show();
        $('#adhocInfoContainer').hide();
        renderComparisonTable(inv);
    }
    
    renderActivityTimeline(inv);
}

// =====================================================
// RENDER ADHOC INFO
// =====================================================
function renderAdhocInfo(inv, contract) {
    // Get category and tag from contract items or invoice
    const categoryName = contract?.items?.[0]?.category?.name || inv.items?.[0]?.category?.name || '-';
    const tagName = inv.assigned_tag_name || contract?.items?.[0]?.tag_name || '-';
    
    $('#adhocCategory').text(categoryName);
    $('#adhocTag').text(tagName);
}

// =====================================================
// RENDER APPROVAL FLOW
// =====================================================
function renderApprovalFlow(inv, contract) {
    const invoiceAmount = parseFloat(inv.grand_total) || 0;
    const contractOrSowValue = getContractOrSowValue();
    const needsCEO = invoiceAmount > contractOrSowValue;
    
    let steps = [
        { key: 'rm', label: 'RM', icon: 'bi-person' },
        { key: 'vp', label: 'VP', icon: 'bi-person-badge' }
    ];
    
    if (needsCEO) {
        steps.push({ key: 'ceo', label: 'CEO', icon: 'bi-star' });
    }
    
    steps.push({ key: 'finance', label: 'Finance', icon: 'bi-bank' });
    
    const statusMap = {
        'submitted': 0,
        'pending_rm': 0,
        'pending_vp': 1,
        'pending_ceo': 2,
        'pending_finance': needsCEO ? 3 : 2,
        'approved': steps.length,
        'paid': steps.length,
        'rejected': -1
    };
    
    const currentStep = statusMap[inv.status] ?? 0;
    
    let html = '';
    steps.forEach((step, i) => {
        let iconClass = 'waiting';
        
        if (inv.status === 'rejected') {
            iconClass = i <= currentStep ? 'rejected' : 'waiting';
        } else if (i < currentStep) {
            iconClass = 'done';
        } else if (i === currentStep) {
            iconClass = 'current';
        }
        
        let stepName = '';
        if (step.key === 'rm' && inv.assigned_tag_name) {
            stepName = inv.assigned_tag_name;
        }
        
        html += `
            <div class="approval-step">
                <div class="step-icon ${iconClass}">
                    <i class="bi ${step.icon}"></i>
                </div>
                <div class="step-label">${step.label}</div>
                ${stepName ? `<div class="step-name">${stepName}</div>` : ''}
            </div>
        `;
    });
    
    $('#approvalFlow').html(html);
}

// =====================================================
// RENDER CONTRACT/SOW SUMMARY - DYNAMIC
// =====================================================
function renderContractSummary(inv, contract) {
    const contractOrSowValue = getContractOrSowValue();
    const thisInvoice = parseFloat(inv.grand_total) || 0;
    const previousUsed = parseFloat(contract?.used_amount) || 0;
    const remaining = contractOrSowValue - previousUsed - thisInvoice;
    
    $('#contractValue').text('₹' + formatNum(contractOrSowValue));
    $('#previousUsed').text('₹' + formatNum(previousUsed));
    $('#thisInvoice').text('₹' + formatNum(thisInvoice));
    
    const remainingEl = $('#remaining');
    remainingEl.text('₹' + formatNum(remaining));
    
    if (remaining < 0) {
        remainingEl.removeClass('success warning').addClass('danger');
    } else if (remaining < contractOrSowValue * 0.2) {
        remainingEl.removeClass('success danger').addClass('warning');
    } else {
        remainingEl.removeClass('warning danger').addClass('success');
    }
}

// =====================================================
// RENDER COMPARISON TABLE (Only for Normal Contracts)
// =====================================================
function renderComparisonTable(inv) {
    const items = inv.items || [];
    
    if (items.length === 0) {
        $('#comparisonBody').html('<tr><td colspan="6" class="text-center py-3 text-muted">No items</td></tr>');
        return;
    }
    
    let html = '';
    items.forEach(item => {
        const ci = item.contract_item || {};
        const conQty = parseFloat(ci.quantity) || 0;
        const conRate = parseFloat(ci.rate) || 0;
        const invQty = parseFloat(item.quantity) || 0;
        const invRate = parseFloat(item.rate) || 0;
        
        const qtyOk = invQty <= conQty;
        const rateOk = invRate <= conRate;
        const allOk = qtyOk && rateOk;
        
        const category = ci.category?.name || item.category?.name || '-';
        
        html += `
            <tr>
                <td>${category}</td>
                <td class="text-center">${conQty}</td>
                <td class="text-center ${!qtyOk ? 'text-danger fw-bold' : ''}">${invQty}</td>
                <td class="text-end">₹${formatNum(conRate)}</td>
                <td class="text-end ${!rateOk ? 'text-danger fw-bold' : ''}">₹${formatNum(invRate)}</td>
                <td class="text-center">
                    <i class="bi ${allOk ? 'bi-check-circle-fill match-ok' : 'bi-exclamation-triangle-fill match-warn'} match-icon"></i>
                </td>
            </tr>
        `;
    });
    
    $('#comparisonBody').html(html);
}

// =====================================================
// RENDER ACTIVITY TIMELINE
// =====================================================
function renderActivityTimeline(inv) {
    const events = [
        { field: 'created_at', label: 'Invoice Created', color: 'blue' },
        { field: 'submitted_at', label: 'Submitted', color: 'blue' },
        { field: 'rm_approved_at', label: 'RM Approved', color: 'green' },
        { field: 'vp_approved_at', label: 'VP Approved', color: 'green' },
        { field: 'ceo_approved_at', label: 'CEO Approved', color: 'green' },
        { field: 'approved_at', label: 'Finance Approved', color: 'green' },
        { field: 'rejected_at', label: 'Rejected', color: 'red' },
        { field: 'paid_at', label: 'Payment Completed', color: 'green' }
    ];
    
    let html = '';
    events.forEach(event => {
        if (inv[event.field]) {
            html += `
                <div class="activity-item">
                    <div class="activity-dot ${event.color}"></div>
                    <div class="activity-text">
                        <div>${event.label}</div>
                        <div class="activity-time">${formatDateTime(inv[event.field])}</div>
                    </div>
                </div>
            `;
        }
    });
    
    if (!html) {
        html = '<div class="text-muted text-center py-2" style="font-size: 12px;">No activity</div>';
    }
    
    $('#activityTimeline').html(html);
}

// =====================================================
// ACTIONS
// =====================================================
function approveInvoice() {
    if (!confirm('Approve this invoice?')) return;
    
    axios.post(`${API_BASE}/${INVOICE_ID}/approve`)
        .then(res => {
            if (res.data.success) {
                const currentStatus = invoiceData.status;
                const newStatus = res.data.data.status;
                
                // Show message based on flow
                if (newStatus === 'pending_rm') {
                    showToast('success', '✅ Invoice submitted for RM approval');
                } 
                else if (newStatus === 'pending_vp') {
                    showToast('success', '✅ RM approved! Invoice sent to COO for approval');
                } 
                else if (newStatus === 'pending_ceo') {
                    showToast('warning', '⚠️ COO approved! Contract value exceeded - Sent to CEO for approval');
                } 
                else if (newStatus === 'pending_finance') {
                    if (currentStatus === 'pending_ceo') {
                        showToast('success', '✅ CEO approved! Invoice sent to Finance for final approval');
                    } else {
                        showToast('success', '✅ COO approved! Invoice sent to Finance for final approval');
                    }
                } 
                else if (newStatus === 'approved') {
                    // Final approval - Check Zoho sync
                    if (res.data.zoho_synced === true) {
                        showToast('success', '✅ Invoice approved & pushed to Zoho successfully!');
                    } else {
                        showToast('warning', '⚠️ Invoice approved but failed to push to Zoho');
                    }
                } 
                else {
                    showToast('success', res.data.message || 'Invoice approved');
                }
                
                loadInvoice();
            }
        })
        .catch(err => showToast('error', err.response?.data?.message || 'Failed'));
}


function showRejectModal() {
    $('#rejectReasonInput').val('');
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function rejectInvoice() {
    const reason = $('#rejectReasonInput').val().trim();
    if (!reason) {
        alert('Please enter reason');
        return;
    }
    
    const btn = $('#confirmRejectBtn');
    btn.prop('disabled', true);
    
    axios.post(`${API_BASE}/${INVOICE_ID}/reject`, { rejection_reason: reason })
        .then(res => {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                showToast('success', 'Invoice rejected');
                loadInvoice();
            }
        })
        .catch(err => showToast('error', err.response?.data?.message || 'Failed'))
        .finally(() => btn.prop('disabled', false));
}

function showPaidModal() {
    $('#paymentRefInput').val('');
    $('#paymentNotesInput').val('');
    new bootstrap.Modal(document.getElementById('paidModal')).show();
}

function markAsPaid() {
    const btn = $('#confirmPaidBtn');
    btn.prop('disabled', true);
    
    axios.post(`${API_BASE}/${INVOICE_ID}/mark-paid`, {
        payment_reference: $('#paymentRefInput').val(),
        payment_notes: $('#paymentNotesInput').val()
    })
    .then(res => {
        if (res.data.success) {
            bootstrap.Modal.getInstance(document.getElementById('paidModal')).hide();
            showToast('success', 'Marked as paid');
            loadInvoice();
        }
    })
    .catch(err => showToast('error', err.response?.data?.message || 'Failed'))
    .finally(() => btn.prop('disabled', false));
}

// =====================================================
// HELPERS
// =====================================================
function getStatusBadge(status) {
    const labels = {
        'submitted': 'Submitted',
        'pending_rm': 'Pending RM',
        'pending_vp': 'Pending VP',
        'pending_ceo': 'Pending CEO',
        'pending_finance': 'Pending Finance',
        'approved': 'Approved',
        'rejected': 'Rejected',
        'paid': 'Paid'
    };
    return `<span class="badge-status badge-${status}">${labels[status] || status}</span>`;
}

function formatNum(num) {
    return parseFloat(num || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDate(str) {
    if (!str) return '-';
    return new Date(str).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTime(str) {
    if (!str) return '-';
    return new Date(str).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function showToast(type, msg) {
    if (typeof Toast !== 'undefined' && Toast[type]) {
        Toast[type](msg);
    } else {
        alert(msg);
    }
}
</script>
@endpush