@extends('layouts.app')
@section('title', 'Travel Invoice Batch')

@section('content')
<style>
    .page-container { min-height: calc(100vh - 80px); }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .page-title { font-size: 18px; font-weight: 700; color: #1f2937; margin: 0; }
    .page-subtitle { font-size: 12px; color: #6b7280; }
    
    .card-custom { background: white; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); height: 100%; }
    .card-header-custom { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; font-size: 13px; font-weight: 600; color: #374151; display: flex; align-items: center; gap: 8px; }
    .card-body-custom { padding: 12px 14px; }
    
    .info-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
    .info-row:last-child { border-bottom: none; }
    .info-label { color: #6b7280; font-size: 13px; }
    .info-value { font-weight: 600; color: #111827; font-size: 13px; }
    
    .badge-status { padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
    .badge-draft { background: #e9ecef; color: #495057; }
    .badge-submitted { background: #e0e7ff; color: #3730a3; }
    .badge-pending_rm { background: #fef3c7; color: #92400e; }
    .badge-pending_vp { background: #dbeafe; color: #1e40af; }
    .badge-pending_ceo { background: #fce7f3; color: #9d174d; }
    .badge-pending_finance { background: #d1fae5; color: #065f46; }
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }
    .badge-paid { background: #dbeafe; color: #1e40af; }
    .badge-partial { background: #fef3c7; color: #92400e; }
    
    .inv-table { width: 100%; font-size: 12px; border-collapse: collapse; }
    .inv-table th { background: #f9fafb; padding: 10px 8px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb; }
    .inv-table td { padding: 10px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .inv-table tbody tr:hover { background: #f8fafc; }
    
    .amount-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
    .amount-row.total { border-top: 2px solid #e5e7eb; margin-top: 8px; padding-top: 10px; font-weight: 700; }
    .amount-row.net { background: #ecfdf5; margin: 10px -14px -12px; padding: 12px 14px; border-radius: 0 0 10px 10px; color: #059669; font-weight: 700; }
    
    .attachment-item { display: flex; align-items: center; gap: 8px; padding: 8px 10px; background: #f9fafb; border-radius: 6px; margin-bottom: 6px; font-size: 12px; }
    .att-icon { width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
    .att-icon.pdf { background: #fee2e2; color: #dc2626; }
    .att-icon.img { background: #dbeafe; color: #2563eb; }
    
    .btn-action { padding: 10px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .btn-approve { background: #059669; color: white; }
    .btn-approve:hover { background: #047857; color: white; }

    .btn-reject { background: white; color: #dc2626; border: 1px solid #dc2626; }
    .btn-reject:hover { background: #dc2626; color: white; }
    .btn-summary { background: #3B82F6; color: white; border: none; padding: 8px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; }
    .btn-summary:hover { opacity: 0.9; color: white; }
    
    .btn-sm-action { padding: 4px 8px; font-size: 11px; border-radius: 4px; }
    
    .badge-travel { padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 500; }
    .badge-domestic { background: #d1fae5; color: #065f46; }
    .badge-international { background: #dbeafe; color: #1e40af; }
    
    .badge-inv-status { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 600; text-transform: uppercase; }
    
    .modal-summary .modal-dialog { max-width: 750px; }
    .modal-summary .modal-content { border: none; border-radius: 12px; overflow: hidden; }
    .modal-summary .modal-header { background: #174081; color: white; padding: 16px 20px; border: none; }
    .modal-summary .modal-body { padding: 0; max-height: 70vh; overflow-y: auto; }
    
    .summary-section { padding: 16px 20px; border-bottom: 1px solid #f3f4f6; }
    .summary-section:last-child { border-bottom: none; }
    .summary-title { font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    
    .approval-flow { display: flex; align-items: flex-start; justify-content: space-between; position: relative; padding: 10px 0; }
    .approval-flow::before { content: ''; position: absolute; top: 30px; left: 40px; right: 40px; height: 3px; background: #e5e7eb; z-index: 0; }
    .approval-step { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; flex: 1; }
    .step-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; margin-bottom: 8px; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    .step-icon.waiting { background: #e5e7eb; color: #9ca3af; }
    .step-icon.current { background: #f59e0b; color: white; animation: pulse 2s infinite; }
    .step-icon.done { background: #10b981; color: white; }
    .step-icon.rejected { background: #ef4444; color: white; }
    @keyframes pulse { 0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); } 50% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); } }
    .step-label { font-size: 11px; font-weight: 600; color: #374151; }
    .step-info { font-size: 9px; color: #9ca3af; margin-top: 2px; }
    
    .batch-summary-box { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; background: #f9fafb; padding: 12px; border-radius: 8px; }
    .summary-item { text-align: center; }
    .summary-item-label { font-size: 10px; color: #6b7280; margin-bottom: 4px; }
    .summary-item-value { font-size: 16px; font-weight: 700; color: #111827; }
    .summary-item-value.success { color: #059669; }
    .summary-item-value.warning { color: #f59e0b; }
    
    .status-message { padding: 12px 16px; border-radius: 8px; font-size: 13px; display: flex; align-items: center; gap: 10px; }
    .status-message.info { background: #dbeafe; color: #1e40af; }
    .status-message.success { background: #d1fae5; color: #065f46; }
    .status-message.warning { background: #fef3c7; color: #92400e; }
    
    /* Activity Timeline */
    .activity-item { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 4px; flex-shrink: 0; }
    .activity-dot.blue { background: #3b82f6; }
    .activity-dot.green { background: #10b981; }
    .activity-dot.yellow { background: #f59e0b; }
    .activity-dot.red { background: #ef4444; }
    .activity-text { flex: 1; }
    .activity-label { font-weight: 600; color: #374151; font-size: 13px; }
    .activity-time { font-size: 11px; color: #9ca3af; margin-top: 2px; }
    
    /* Rejected Section */
    .rejected-section { margin-top: 12px; border-top: 1px solid #fee2e2; padding-top: 12px; }
    .rejected-header { background: #fef2f2; padding: 10px 14px; border-radius: 8px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
    .rejected-header:hover { background: #fee2e2; }
    .rejected-header .title { font-weight: 600; color: #991b1b; font-size: 13px; display: flex; align-items: center; gap: 6px; }
    .rejected-header .badge { background: #dc2626; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; }
    .rejected-content { display: none; margin-top: 10px; }
    .rejected-content.show { display: block; }
    .rejected-table th { background: #fee2e2 !important; color: #991b1b !important; }
    .rejected-table td { background: #fef2f2; }
    .amount-row.rejected-amount { color: #dc2626; border-top: 1px dashed #fecaca; margin-top: 10px; padding-top: 10px; }

    /* Edit Modal Styles */
    .edit-expense-table { width: 100%; font-size: 11px; }
    .edit-expense-table th { background: #f3f4f6; padding: 8px 6px; font-weight: 600; }
    .edit-expense-table td { padding: 6px 4px; vertical-align: middle; }
    .edit-expense-table input, .edit-expense-table select { font-size: 11px; padding: 4px 6px; }
    .edit-field-sm { width: 70px !important; }
    .edit-field-md { width: 100px !important; }
    .tds-select { min-width: 200px; }
</style>

<div class="page-container">
    
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.travel-invoices.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title" id="pageBatchNumber">Loading...</h1>
                <p class="page-subtitle"><span id="pageVendorName">-</span> • <span id="pageInvoiceCount">0</span> invoices</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="pageStatus"></span>
            <button class="btn-summary" onclick="openSummaryModal()">
                <i class="bi bi-eye me-1"></i>Summary
            </button>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="row g-3">
        
        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="row g-3">
                
                <!-- Batch Info -->
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-header-custom"><i class="bi bi-receipt text-primary"></i>Batch Info</div>
                        <div class="card-body-custom">
                            <div class="info-row"><span class="info-label">Batch #</span><span class="info-value" id="batchNumber">-</span></div>
                            <div class="info-row"><span class="info-label">Total Invoices</span><span class="info-value" id="totalInvoices">0</span></div>
                            <div class="info-row"><span class="info-label">Created Date</span><span class="info-value" id="createdDate">-</span></div>
                            <div class="info-row"><span class="info-label">Submitted Date</span><span class="info-value" id="submittedDate">-</span></div>
                        </div>
                    </div>
                </div>
                
                <!-- Vendor Info -->
                <div class="col-md-6">
                    <div class="card-custom">
                        <div class="card-header-custom"><i class="bi bi-building text-primary"></i>Vendor Info</div>
                        <div class="card-body-custom">
                            <div class="info-row"><span class="info-label">Name</span><span class="info-value" id="vendorName">-</span></div>
                            <div class="info-row"><span class="info-label">Company</span><span class="info-value" id="vendorCompany">-</span></div>
                            <div class="info-row"><span class="info-label">Email</span><span class="info-value" id="vendorEmail">-</span></div>
                            <div class="info-row"><span class="info-label">Phone</span><span class="info-value" id="vendorPhone">-</span></div>
                        </div>
                    </div>
                </div>
                
                <!-- Invoices List -->
                <div class="col-12">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="bi bi-list-ul text-primary"></i>Invoices
                            <span class="ms-auto badge bg-primary" id="invoicesCount">0</span>
                        </div>
                        <div class="card-body-custom p-0">
                            <div style="max-height: 350px; overflow-y: auto;">
                                <table class="inv-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Invoice #</th>
                                            <th>Employee</th>
                                            <th>Location</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end" style="width: 160px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoicesTableBody">
                                        <tr><td colspan="8" class="text-center py-4 text-muted">Loading...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rejected Invoices Section -->
                <div class="col-12">
                    <div class="rejected-section" id="rejectedSection" style="display: none; padding: 12px;">
                        <div class="rejected-header" onclick="toggleRejectedSection()">
                            <span class="title"><i class="bi bi-x-circle-fill"></i>Rejected Invoices</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge" id="rejectedBadgeCount">0</span>
                                <i class="bi bi-chevron-down" id="rejectedChevron"></i>
                            </div>
                        </div>
                        <div class="rejected-content" id="rejectedContent">
                            <table class="inv-table rejected-table mt-2">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Invoice #</th>
                                        <th>Employee</th>
                                        <th>Rejected By</th>
                                        <th>Reason</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="rejectedInvoicesTableBody"></tbody>
                            </table>
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
                        <div class="card-header-custom"><i class="bi bi-calculator text-primary"></i>Amount Summary</div>
                        <div class="card-body-custom">
                            <div class="amount-row"><span class="text-muted">Basic Total</span><span class="fw-semibold" id="sumBasic">₹0.00</span></div>
                            <div class="amount-row"><span class="text-muted">Taxes</span><span id="sumTaxes">₹0.00</span></div>
                            <div class="amount-row"><span class="text-muted">Service Charges</span><span id="sumService">₹0.00</span></div>
                            <div class="amount-row"><span class="text-muted">GST</span><span id="sumGst">₹0.00</span></div>
                            <div class="amount-row total"><span>Gross Total</span><span id="sumGross">₹0.00</span></div>
                            <div class="amount-row"><span class="text-muted">TDS</span><span class="text-danger" id="sumTds">-₹0.00</span></div>
                            <div class="amount-row net"><span>Net Payable</span><span id="sumNet">₹0.00</span></div>
                            <!-- Rejected Amount -->
                            <div class="amount-row rejected-amount" id="rejectedAmountRow" style="display: none;">
                                <span><i class="bi bi-x-circle me-1"></i>Rejected Amount</span>
                                <span id="sumRejected">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Attachments -->
                <div class="col-12">
                    <div class="card-custom">
                        <div class="card-header-custom">
                            <i class="bi bi-paperclip text-primary"></i>Attachments
                            <span class="ms-auto badge bg-secondary" id="attachmentsCount">0</span>
                        </div>
                        <div class="card-body-custom" id="attachmentsContainer">
                            <div class="text-muted text-center py-2" style="font-size: 12px;">No attachments</div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="col-12">
                    <div class="card-custom">
                        <div class="card-header-custom"><i class="bi bi-lightning text-primary"></i>Actions</div>
                        <div class="card-body-custom" id="actionsContainer">
                            <div class="text-center py-2 text-muted" style="font-size: 12px;">Loading...</div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<!-- Summary Modal -->
<div class="modal fade modal-summary" id="summaryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title fw-bold" id="modalBatchNumber">Batch Summary</h5>
                    <small class="opacity-75" id="modalVendorName">Vendor</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Approval Flow -->
                <div class="summary-section">
                    <div class="summary-title"><i class="bi bi-diagram-3"></i>Approval Flow</div>
                    <div class="approval-flow" id="approvalFlow"></div>
                </div>
                
                <!-- Batch Summary -->
                <div class="summary-section">
                    <div class="summary-title"><i class="bi bi-bar-chart"></i>Batch Summary</div>
                    <div class="batch-summary-box">
                        <div class="summary-item"><div class="summary-item-label">Total</div><div class="summary-item-value" id="modalTotal">0</div></div>
                        <div class="summary-item"><div class="summary-item-label">Pending</div><div class="summary-item-value warning" id="modalPending">0</div></div>
                        <div class="summary-item"><div class="summary-item-label">Approved</div><div class="summary-item-value success" id="modalApproved">0</div></div>
                        <div class="summary-item"><div class="summary-item-label">Rejected</div><div class="summary-item-value" style="color:#dc2626" id="modalRejected">0</div></div>
                    </div>
                </div>
                
                <!-- Activity Timeline -->
                <div class="summary-section">
                    <div class="summary-title"><i class="bi bi-clock-history"></i>Activity Timeline</div>
                    <div id="activityTimeline">
                        <div class="text-muted text-center py-2" style="font-size: 12px;">No activity yet</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Single Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 10px;">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Invoice</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rejectInvoiceId">
                <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                <textarea class="form-control" id="rejectReasonInput" rows="3" placeholder="Enter reason..."></textarea>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmRejectSingle()"><i class="bi bi-x-circle me-1"></i>Reject</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject All Modal -->
<div class="modal fade" id="rejectAllModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 10px;">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject All Invoices</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-3">
                    <small><i class="bi bi-exclamation-triangle me-1"></i>This will reject <strong id="rejectAllCount">0</strong> invoices.</small>
                </div>
                <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                <textarea class="form-control" id="rejectAllReasonInput" rows="3" placeholder="Enter reason..."></textarea>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmRejectAll()"><i class="bi bi-x-circle me-1"></i>Reject All</button>
            </div>
        </div>
    </div>
</div>

<!-- View Invoice Modal -->
<div class="modal fade" id="viewInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 10px;">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title"><i class="bi bi-receipt me-2"></i><span id="viewInvNumber">Invoice</span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-8 p-3 border-end">
                        <h6 class="fw-bold mb-2"><i class="bi bi-person me-1"></i>Employee & Travel</h6>
                        <div class="row mb-3">
                            <div class="col-6"><small class="text-muted">Employee</small><div class="fw-semibold" id="viewEmpName">-</div></div>
                            <div class="col-6"><small class="text-muted">Code</small><div class="fw-semibold" id="viewEmpCode">-</div></div>
                            <div class="col-6 mt-2"><small class="text-muted">Location</small><div class="fw-semibold" id="viewLocation">-</div></div>
                            <div class="col-6 mt-2"><small class="text-muted">Travel Date</small><div class="fw-semibold" id="viewTravelDate">-</div></div>
                        </div>
                        <h6 class="fw-bold mb-2"><i class="bi bi-list me-1"></i>Expense Items</h6>
                        <table class="table table-sm table-bordered" style="font-size: 11px;">
                            <thead class="table-light">
                                <tr><th>#</th><th>Mode</th><th>Particulars</th><th class="text-end">Basic</th><th class="text-end">Tax</th><th class="text-end">Total</th></tr>
                            </thead>
                            <tbody id="viewItemsBody"></tbody>
                        </table>
                    </div>
                    <div class="col-md-4 p-3 bg-light">
                        <h6 class="fw-bold mb-2"><i class="bi bi-calculator me-1"></i>Summary</h6>
                        <div class="d-flex justify-content-between py-1"><span class="text-muted">Basic</span><span id="viewBasic">₹0</span></div>
                        <div class="d-flex justify-content-between py-1"><span class="text-muted">Taxes</span><span id="viewTaxes">₹0</span></div>
                        <div class="d-flex justify-content-between py-1"><span class="text-muted">Service</span><span id="viewService">₹0</span></div>
                        <div class="d-flex justify-content-between py-1"><span class="text-muted">GST</span><span id="viewGst">₹0</span></div>
                        <div class="d-flex justify-content-between py-1 border-top mt-2 pt-2 fw-bold"><span>Gross</span><span id="viewGross">₹0</span></div>
                        <div class="d-flex justify-content-between py-1"><span class="text-muted">TDS</span><span class="text-danger" id="viewTds">-₹0</span></div>
                        <div class="d-flex justify-content-between py-2 bg-success text-white rounded mt-2 px-2"><span>Net</span><span id="viewNet">₹0</span></div>
                        <h6 class="fw-bold mb-2 mt-3"><i class="bi bi-paperclip me-1"></i>Bills</h6>
                        <div id="viewBills"><small class="text-muted">No bills</small></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Invoice Modal (Finance Only) -->
<div class="modal fade" id="editInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 10px;">
            <div class="modal-header bg-warning text-dark py-2">
                <h6 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Invoice - <span id="editInvNumber">INV-001</span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editInvoiceId">
                
                <!-- Basic Info -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Employee</label>
                        <input type="text" class="form-control form-control-sm bg-light" id="editEmpName" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Location</label>
                        <input type="text" class="form-control form-control-sm" id="editLocation">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Travel Date</label>
                        <input type="date" class="form-control form-control-sm" id="editTravelDate">
                    </div>
                </div>
                
                <!-- Expense Items -->
                <h6 class="fw-bold mb-2"><i class="bi bi-list-ul me-1"></i>Expense Items</h6>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-bordered edit-expense-table">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Mode</th>
                                <th>Particulars</th>
                                <th style="width: 80px;">Basic</th>
                                <th style="width: 70px;">Taxes</th>
                                <th style="width: 70px;">Service</th>
                                <th style="width: 70px;">GST</th>
                                <th style="width: 85px;">Total</th>
                            </tr>
                        </thead>
                        <tbody id="editItemsBody"></tbody>
                        <tfoot>
                            <tr class="table-success">
                                <td colspan="2" class="text-end fw-bold">Totals:</td>
                                <td class="fw-bold" id="editTotalBasic">₹0</td>
                                <td class="fw-bold" id="editTotalTaxes">₹0</td>
                                <td class="fw-bold" id="editTotalService">₹0</td>
                                <td class="fw-bold" id="editTotalGst">₹0</td>
                                <td class="fw-bold" id="editTotalGross">₹0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- TDS Section -->
                <div class="row mt-3 p-2 bg-light rounded align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small mb-1">TDS Type (from Zoho)</label>
                        <select class="form-select form-select-sm tds-select" id="editTdsSelect" onchange="onTdsSelectChange()">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">TDS %</label>
                        <input type="number" class="form-control form-control-sm" id="editTdsPercent" min="0" max="100" step="0.01" onchange="calculateEditTotals()">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">TDS Amount</label>
                        <input type="text" class="form-control form-control-sm bg-light text-danger fw-bold" id="editTdsAmount" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Net Payable</label>
                        <input type="text" class="form-control form-control-sm bg-success text-white fw-bold" id="editNetAmount" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-warning" id="saveEditBtn" onclick="saveInvoiceEdit()">
                    <i class="bi bi-check-lg me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const API_BASE = '/api/admin/travel-invoices';
const BATCH_ID = {{ $batchId ?? 'null' }};
const USER_ROLE = '{{ auth()->user()->role->slug ?? "super-admin" }}';

let batchData = null;
let invoices = [];
let summary = {};
let approvableCount = 0;

let activeInvoices = [];
let rejectedInvoices = [];

// For Finance Edit
let zohoTdsTaxes = [];
let currentEditInvoice = null;

$(document).ready(function() {
    if (BATCH_ID) loadBatch();
    loadZohoTds();
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
// LOAD BATCH
// =====================================================
function loadBatch() {
    axios.get(`${API_BASE}/batches/${BATCH_ID}/summary`)
        .then(res => {
            if (res.data.success) {
                batchData = res.data.data.batch;
                invoices = res.data.data.invoices || [];
                summary = res.data.data.summary || {};
                approvableCount = res.data.data.approvable_count || 0;
              
                // Separate active and rejected invoices
                activeInvoices = invoices.filter(inv => inv.status !== 'rejected');
                rejectedInvoices = invoices.filter(inv => inv.status === 'rejected');
                renderPage();
            }
        })
        .catch(err => {
            console.error('Failed to load batch:', err);
            Toast.error('Failed to load batch');
        });
}

// =====================================================
// RENDER PAGE
// =====================================================
function renderPage() {
    // Header
    $('#pageBatchNumber').text(batchData.batch_number);
    $('#pageVendorName').text(batchData.vendor?.vendor_name || '-');
    $('#pageInvoiceCount').text(invoices.length);
    $('#pageStatus').html(getStatusBadge(batchData.status));
    
    // Batch Info
    $('#batchNumber').text(batchData.batch_number);
    $('#totalInvoices').text(invoices.length);
    $('#createdDate').text(formatDate(batchData.created_at));
    $('#submittedDate').text(formatDate(batchData.submitted_at));
    
    // Vendor Info
    $('#vendorName').text(batchData.vendor?.vendor_name || '-');
    $('#vendorCompany').text(batchData.vendor?.company_info?.legal_entity_name || '-');
    $('#vendorEmail').text(batchData.vendor?.vendor_email || '-');
    $('#vendorPhone').text(batchData.vendor?.contact?.mobile || '-');  

    renderInvoicesTable();
    renderRejectedInvoicesTable();  
    renderAmountSummary();
    renderAttachments();
    renderActions();
}

// =====================================================
// RENDER INVOICES TABLE
// =====================================================
function renderInvoicesTable() {
    $('#invoicesCount').text(activeInvoices.length);  
    
    if (activeInvoices.length === 0) { 
        $('#invoicesTableBody').html('<tr><td colspan="8" class="text-center py-4 text-muted">No invoices</td></tr>');
        return;
    }
    
    let html = '';
    activeInvoices.forEach((inv, i) => {  
        const canAct = canUserActOnInvoice(inv);
        const canEditInv = canUserEditInvoice(inv);
        
        html += `
            <tr>
                <td>${i + 1}</td>
                <td><a href="javascript:void(0)" onclick="viewInvoice(${inv.id})" class="text-primary fw-semibold">${inv.invoice_number}</a></td>
                <td>
                    <div>${inv.employee?.employee_name || '-'}</div>
                    <small class="text-muted">${inv.employee?.employee_code || ''}</small>
                </td>
                <td>${inv.location || '-'}</td>
                <td><span class="badge-travel badge-${inv.travel_type}">${inv.travel_type === 'domestic' ? 'Domestic' : 'Int\'l'}</span></td>
                <td>${getInvoiceStatusBadge(inv.status)}</td>
                <td class="text-end fw-semibold text-success">₹${formatNum(inv.gross_amount)}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary btn-sm-action me-1" onclick="viewInvoice(${inv.id})" title="View"><i class="bi bi-eye"></i></button>
                    ${canEditInv ? `<button class="btn btn-sm btn-outline-warning btn-sm-action me-1" onclick="openEditModal(${inv.id})" title="Edit"><i class="bi bi-pencil"></i></button>` : ''}
                    ${canAct ? `
                        <button class="btn btn-sm btn-success btn-sm-action me-1" onclick="approveSingle(${inv.id})" title="Approve"><i class="bi bi-check"></i></button>
                        <button class="btn btn-sm btn-outline-danger btn-sm-action" onclick="showRejectSingleModal(${inv.id})" title="Reject"><i class="bi bi-x"></i></button>
                    ` : ''}
                </td>
            </tr>
        `;
    });
    
    $('#invoicesTableBody').html(html);
}

// =====================================================
// RENDER REJECTED INVOICES TABLE
// =====================================================
function renderRejectedInvoicesTable() {
    if (rejectedInvoices.length === 0) {
        $('#rejectedSection').hide();
        return;
    }
    
    $('#rejectedSection').show();
    $('#rejectedBadgeCount').text(rejectedInvoices.length);
    
    let html = '';
    rejectedInvoices.forEach((inv, i) => {
        html += `
            <tr>
                <td>${i + 1}</td>
                <td><a href="javascript:void(0)" onclick="viewInvoice(${inv.id})" class="text-danger fw-semibold">${inv.invoice_number}</a></td>
                <td>${inv.employee?.employee_name || '-'}</td>
                <td><span class="badge bg-danger" style="font-size: 10px;">${inv.rejected_by_role || 'Unknown'}</span></td>
                <td><small class="text-muted">${(inv.rejection_reason || '-').substring(0, 30)}</small></td>
                <td class="text-end fw-semibold text-danger">₹${formatNum(inv.gross_amount)}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary btn-sm-action" onclick="viewInvoice(${inv.id})"><i class="bi bi-eye"></i></button>
                </td>
            </tr>
        `;
    });
    $('#rejectedInvoicesTableBody').html(html);
}

function toggleRejectedSection() {
    $('#rejectedContent').toggleClass('show');
    $('#rejectedChevron').toggleClass('bi-chevron-down bi-chevron-up');
}

// =====================================================
// PERMISSION CHECKS
// =====================================================
function canUserActOnInvoice(invoice) {
    const status = invoice.status;
    
    if (['approved', 'rejected', 'paid', 'draft'].includes(status)) return false;
    if (USER_ROLE === 'super-admin') return true;
    if (USER_ROLE === 'manager' && status === 'pending_rm') return true;
    if (USER_ROLE === 'vp' && status === 'pending_vp') return true;
    if (USER_ROLE === 'ceo' && status === 'pending_ceo') return true;
    if (USER_ROLE === 'finance' && status === 'pending_finance') return true;
    
    return false;
}

function canUserEditInvoice(invoice) {
    // Only Finance (and super-admin) can edit at pending_finance stage
    return (USER_ROLE === 'finance' || USER_ROLE === 'super-admin') && invoice.status === 'pending_finance';
}

// =====================================================
// RENDER AMOUNT SUMMARY
// =====================================================
function renderAmountSummary() {
    let basic = 0, taxes = 0, service = 0, gst = 0, gross = 0, tds = 0, net = 0;
    
    activeInvoices.forEach(inv => {
        basic += parseFloat(inv.basic_total) || 0;
        taxes += parseFloat(inv.taxes_total) || 0;
        service += parseFloat(inv.service_charge_total) || 0;
        gst += parseFloat(inv.gst_total) || 0;
        gross += parseFloat(inv.gross_amount) || 0;
        tds += parseFloat(inv.tds_amount) || 0;
        net += parseFloat(inv.net_amount) || 0;
    });
    
    $('#sumBasic').text('₹' + formatNum(basic));
    $('#sumTaxes').text('₹' + formatNum(taxes));
    $('#sumService').text('₹' + formatNum(service));
    $('#sumGst').text('₹' + formatNum(gst));
    $('#sumGross').text('₹' + formatNum(gross));
    $('#sumTds').text('-₹' + formatNum(tds));
    $('#sumNet').text('₹' + formatNum(net));

    // Show rejected amount
    let rejectedAmount = 0;
    rejectedInvoices.forEach(inv => {
        rejectedAmount += parseFloat(inv.gross_amount) || 0;
    });
    
    if (rejectedAmount > 0) {
        $('#rejectedAmountRow').show();
        $('#sumRejected').text('₹' + formatNum(rejectedAmount));
    } else {
        $('#rejectedAmountRow').hide();
    }
}

// =====================================================
// RENDER ATTACHMENTS
// =====================================================
function renderAttachments() {
    let allBills = [];
    activeInvoices.forEach(inv => {  
        if (inv.bills && inv.bills.length > 0) {
            inv.bills.forEach(bill => allBills.push({ ...bill, invoice_number: inv.invoice_number }));
        }
    });
    
    $('#attachmentsCount').text(allBills.length);
    
    if (allBills.length === 0) {
        $('#attachmentsContainer').html('<div class="text-muted text-center py-2" style="font-size: 12px;">No attachments</div>');
        return;
    }
    
    let html = '';
    allBills.slice(0, 5).forEach(bill => {
        const isPdf = bill.file_name?.toLowerCase().endsWith('.pdf');
        html += `
            <div class="attachment-item">
                <div class="att-icon ${isPdf ? 'pdf' : 'img'}"><i class="bi bi-file-earmark-${isPdf ? 'pdf' : 'image'}"></i></div>
                <div class="flex-grow-1">
                    <div class="fw-semibold text-truncate" style="max-width: 150px;">${bill.file_name || 'Bill'}</div>
                    <small class="text-muted">${bill.invoice_number}</small>
                </div>
                <a href="/storage/${bill.file_path}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
            </div>
        `;
    });
    
    if (allBills.length > 5) {
        html += `<div class="text-center"><small class="text-muted">+${allBills.length - 5} more files</small></div>`;
    }
    
    $('#attachmentsContainer').html(html);
}

// =====================================================
// RENDER ACTIONS
// =====================================================
function renderActions() {
    const status = batchData.status;
    
    // Final statuses - no action
    if (['approved', 'rejected', 'paid'].includes(status) && approvableCount === 0) {
        let msg = '';
        if (status === 'approved') msg = '<i class="bi bi-check-circle-fill text-success me-2"></i>Batch Approved';
        else if (status === 'rejected') msg = '<i class="bi bi-x-circle-fill text-danger me-2"></i>Batch Rejected';
        else if (status === 'paid') msg = '<i class="bi bi-currency-rupee me-2"></i>Batch Paid';
        
        $('#actionsContainer').html(`<div class="status-message success">${msg}</div>`);
        return;
    }
    
    // Check if user has invoices to approve
    const hasApprovable = approvableCount > 0;
    
    // No actions available for this user
    if (!hasApprovable) {
        const pendingLabels = {
            'pending_rm': 'Pending RM Approval',
            'pending_vp': 'Pending VOO Approval',
            'pending_ceo': 'Pending CEO Approval',
            'pending_finance': 'Pending Finance Approval',
        };
        const msg = pendingLabels[status] || 'No actions available';
        $('#actionsContainer').html(`<div class="status-message info"><i class="bi bi-hourglass-split me-2"></i>${msg}</div>`);
        return;
    }
    
    // User can take action - Show Approve/Reject buttons
    let html = '<div class="d-flex gap-2 flex-wrap">';
    
    html += `
        <button class="btn-action btn-approve flex-grow-1" onclick="approveAll()">
            <i class="bi bi-check-all"></i>Approve All (${approvableCount})
        </button>
        <button class="btn-action btn-reject" onclick="showRejectAllModal()">
            <i class="bi bi-x-lg"></i>Reject All
        </button>
    `;
    
    html += '</div>';
    $('#actionsContainer').html(html);
}

// =====================================================
// ACTIONS - APPROVE / REJECT
// =====================================================
function approveSingle(id) {
    if (!confirm('Approve this invoice?')) return;
    
    axios.post(`${API_BASE}/${id}/approve`)
        .then(res => {
            if (res.data.success) {
                Toast.success(res.data.message || 'Invoice approved');
                loadBatch();
            }
        })
        .catch(err => Toast.error(err.response?.data?.message || 'Failed'));
}

function showRejectSingleModal(id) {
    $('#rejectInvoiceId').val(id);
    $('#rejectReasonInput').val('');
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function confirmRejectSingle() {
    const id = $('#rejectInvoiceId').val();
    const reason = $('#rejectReasonInput').val().trim();
    if (!reason) { alert('Please enter reason'); return; }
    
    axios.post(`${API_BASE}/${id}/reject`, { rejection_reason: reason })
        .then(res => {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                Toast.success(res.data.message || 'Invoice rejected');
                loadBatch();
            }
        })
        .catch(err => Toast.error(err.response?.data?.message || 'Failed'));
}

function approveAll() {
    if (!confirm(`Approve ${approvableCount} invoices?`)) return;
    
    axios.post(`${API_BASE}/batches/${BATCH_ID}/approve-all`)
        .then(res => {
            if (res.data.success) {
                Toast.success(res.data.message || 'Invoices approved');
                loadBatch();
            }
        })
        .catch(err => Toast.error(err.response?.data?.message || 'Failed'));
}

function showRejectAllModal() {
    $('#rejectAllCount').text(approvableCount);
    $('#rejectAllReasonInput').val('');
    new bootstrap.Modal(document.getElementById('rejectAllModal')).show();
}

function confirmRejectAll() {
    const reason = $('#rejectAllReasonInput').val().trim();
    if (!reason) { alert('Please enter reason'); return; }
    
    axios.post(`${API_BASE}/batches/${BATCH_ID}/reject-all`, { rejection_reason: reason })
        .then(res => {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('rejectAllModal')).hide();
                Toast.success(res.data.message || 'Invoices rejected');
                loadBatch();
            }
        })
        .catch(err => Toast.error(err.response?.data?.message || 'Failed'));
}

// =====================================================
// VIEW INVOICE MODAL
// =====================================================
function viewInvoice(id) {
    const inv = invoices.find(i => i.id === id);
    if (!inv) return;
    
    $('#viewInvNumber').text(inv.invoice_number);
    $('#viewEmpName').text(inv.employee?.employee_name || '-');
    $('#viewEmpCode').text(inv.employee?.employee_code || '-');
    $('#viewLocation').text(inv.location || '-');
    $('#viewTravelDate').text(formatDate(inv.travel_date));
    
    const items = inv.items || [];
    const modes = { 'flight': 'Flight', 'train': 'Train', 'cabs': 'Cabs', 'accommodation': 'Hotel', 'insurance': 'Insurance', 'visa': 'Visa', 'other': 'Other' };
    let itemsHtml = '';
    items.forEach((item, i) => {
        itemsHtml += `<tr>
            <td>${i + 1}</td>
            <td>${item.mode === 'other' && item.mode_other ? item.mode_other : modes[item.mode] || item.mode}</td>
            <td>${item.particulars || '-'}</td>
            <td class="text-end">₹${formatNum(item.basic)}</td>
            <td class="text-end">₹${formatNum((item.taxes || 0) + (item.service_charge || 0) + (item.gst || 0))}</td>
            <td class="text-end fw-bold">₹${formatNum(item.gross_amount)}</td>
        </tr>`;
    });
    $('#viewItemsBody').html(itemsHtml || '<tr><td colspan="6" class="text-center text-muted">No items</td></tr>');
    
    $('#viewBasic').text('₹' + formatNum(inv.basic_total));
    $('#viewTaxes').text('₹' + formatNum(inv.taxes_total));
    $('#viewService').text('₹' + formatNum(inv.service_charge_total));
    $('#viewGst').text('₹' + formatNum(inv.gst_total));
    $('#viewGross').text('₹' + formatNum(inv.gross_amount));
    $('#viewTds').text('-₹' + formatNum(inv.tds_amount));
    $('#viewNet').text('₹' + formatNum(inv.net_amount));
    
    const bills = inv.bills || [];
    let billsHtml = '';
    bills.forEach(bill => {
        billsHtml += `<a href="/storage/${bill.file_path}" target="_blank" class="d-block small text-primary mb-1"><i class="bi bi-paperclip me-1"></i>${bill.file_name || 'Bill'}</a>`;
    });
    $('#viewBills').html(billsHtml || '<small class="text-muted">No bills</small>');
    
    new bootstrap.Modal(document.getElementById('viewInvoiceModal')).show();
}

// =====================================================
// FINANCE EDIT FUNCTIONALITY
// =====================================================

// Open Edit Modal
function openEditModal(invoiceId) {
    const inv = invoices.find(i => i.id === invoiceId);
    if (!inv) return;
    
    currentEditInvoice = inv;
    
    // Fill basic info
    $('#editInvoiceId').val(inv.id);
    $('#editInvNumber').text(inv.invoice_number);
    $('#editEmpName').val(inv.employee?.employee_name || '-');
    $('#editLocation').val(inv.location || '');
    $('#editTravelDate').val(inv.travel_date ? inv.travel_date.split('T')[0] : '');
    $('#editTdsPercent').val(inv.tds_percent || 5);
    
    // Populate TDS dropdown
    populateTdsDropdown(inv.tds_percent);
    
    // Render expense items
    renderEditItems(inv.items || []);
    
    // Calculate totals
    calculateEditTotals();
    
    // Show modal
    new bootstrap.Modal(document.getElementById('editInvoiceModal')).show();
}

// Populate TDS dropdown from Zoho
function populateTdsDropdown(currentPercent) {
    let options = '<option value="">-- Select TDS --</option>';
    
    if (zohoTdsTaxes.length === 0) {
        // Fallback if Zoho not loaded
        options += `
            <option value="1" ${currentPercent == 1 ? 'selected' : ''}>TDS 1%</option>
            <option value="2" ${currentPercent == 2 ? 'selected' : ''}>TDS 2%</option>
            <option value="5" ${currentPercent == 5 ? 'selected' : ''}>TDS 5%</option>
            <option value="10" ${currentPercent == 10 ? 'selected' : ''}>TDS 10%</option>
        `;
    } else {
        zohoTdsTaxes.forEach(tax => {
            const selected = parseFloat(tax.tax_percentage) === parseFloat(currentPercent) ? 'selected' : '';
            options += `<option value="${tax.tax_percentage}" data-tax-id="${tax.tax_id}" ${selected}>${tax.tax_name} (${tax.tax_percentage}%)</option>`;
        });
    }
    
    $('#editTdsSelect').html(options);
}

// When TDS dropdown changes
function onTdsSelectChange() {
    const selectedPercent = $('#editTdsSelect').val();
    if (selectedPercent) {
        $('#editTdsPercent').val(selectedPercent);
        calculateEditTotals();
    }
}

// Render expense items in edit modal
function renderEditItems(items) {
    const modes = {
        'flight': 'Flight', 'train': 'Train', 'cabs': 'Cabs',
        'accommodation': 'Hotel', 'insurance': 'Insurance', 'visa': 'Visa', 'other': 'Other'
    };
    
    let html = '';
    items.forEach((item, i) => {
        let modeOptions = '';
        Object.entries(modes).forEach(([key, label]) => {
            modeOptions += `<option value="${key}" ${item.mode === key ? 'selected' : ''}>${label}</option>`;
        });
        
        html += `
            <tr data-item-id="${item.id}">
                <td>
                    <select class="form-select form-select-sm edit-mode">${modeOptions}</select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm edit-particulars" value="${item.particulars || ''}">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm edit-basic edit-field-sm" value="${item.basic || 0}" min="0" onchange="calculateEditTotals()">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm edit-taxes edit-field-sm" value="${item.taxes || 0}" min="0" onchange="calculateEditTotals()">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm edit-service edit-field-sm" value="${item.service_charge || 0}" min="0" onchange="calculateEditTotals()">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm edit-gst edit-field-sm" value="${item.gst || 0}" min="0" onchange="calculateEditTotals()">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm bg-light edit-row-total" value="₹0" readonly>
                </td>
            </tr>
        `;
    });
    
    if (items.length === 0) {
        html = '<tr><td colspan="7" class="text-center text-muted py-3">No items</td></tr>';
    }
    
    $('#editItemsBody').html(html);
}

// Calculate totals in edit modal
function calculateEditTotals() {
    let totalBasic = 0, totalTaxes = 0, totalService = 0, totalGst = 0;
    
    $('#editItemsBody tr').each(function() {
        const basic = parseFloat($(this).find('.edit-basic').val()) || 0;
        const taxes = parseFloat($(this).find('.edit-taxes').val()) || 0;
        const service = parseFloat($(this).find('.edit-service').val()) || 0;
        const gst = parseFloat($(this).find('.edit-gst').val()) || 0;
        const rowTotal = basic + taxes + service + gst;
        
        $(this).find('.edit-row-total').val('₹' + formatNum(rowTotal));
        
        totalBasic += basic;
        totalTaxes += taxes;
        totalService += service;
        totalGst += gst;
    });
    
    const grossTotal = totalBasic + totalTaxes + totalService + totalGst;
    const tdsPercent = parseFloat($('#editTdsPercent').val()) || 0;
    const tdsAmount = (totalBasic * tdsPercent) / 100;
    const netAmount = grossTotal - tdsAmount;
    
    $('#editTotalBasic').text('₹' + formatNum(totalBasic));
    $('#editTotalTaxes').text('₹' + formatNum(totalTaxes));
    $('#editTotalService').text('₹' + formatNum(totalService));
    $('#editTotalGst').text('₹' + formatNum(totalGst));
    $('#editTotalGross').text('₹' + formatNum(grossTotal));
    $('#editTdsAmount').val('-₹' + formatNum(tdsAmount));
    $('#editNetAmount').val('₹' + formatNum(netAmount));
}

// Save Invoice Edit
function saveInvoiceEdit() {
    const invoiceId = $('#editInvoiceId').val();
    if (!invoiceId) return;
    
    const btn = $('#saveEditBtn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    
    // Collect data
    const data = {
        location: $('#editLocation').val(),
        travel_date: $('#editTravelDate').val(),
        tds_percent: parseFloat($('#editTdsPercent').val()) || 0,
        tds_tax_id: $('#editTdsSelect option:selected').data('tax-id') || null,
        items: []
    };
    
    // Collect items
    $('#editItemsBody tr').each(function() {
        const itemId = $(this).data('item-id');
        if (itemId) {
            data.items.push({
                id: itemId,
                mode: $(this).find('.edit-mode').val(),
                particulars: $(this).find('.edit-particulars').val(),
                basic: parseFloat($(this).find('.edit-basic').val()) || 0,
                taxes: parseFloat($(this).find('.edit-taxes').val()) || 0,
                service_charge: parseFloat($(this).find('.edit-service').val()) || 0,
                gst: parseFloat($(this).find('.edit-gst').val()) || 0
            });
        }
    });
    
    axios.put(`${API_BASE}/${invoiceId}/update`, data)
        .then(res => {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editInvoiceModal')).hide();
                Toast.success('Invoice updated successfully!');
                loadBatch(); // Reload data
            }
        })
        .catch(err => {
            Toast.error(err.response?.data?.message || 'Failed to save');
        })
        .finally(() => {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>Save Changes');
        });
}

// =====================================================
// SUMMARY MODAL
// =====================================================
function openSummaryModal() {
    $('#modalBatchNumber').text(batchData.batch_number);
    $('#modalVendorName').text(batchData.vendor?.vendor_name || '-');
    
    // Summary counts
    $('#modalTotal').text(summary.total || invoices.length);
    $('#modalPending').text((summary.submitted || 0) + (summary.pending_rm || 0) + (summary.pending_vp || 0) + (summary.pending_ceo || 0) + (summary.pending_finance || 0));
    $('#modalApproved').text((summary.approved || 0) + (summary.paid || 0));
    $('#modalRejected').text(summary.rejected || 0);
    
    // Render approval flow & timeline
    renderApprovalFlow();
    renderActivityTimeline();
    
    new bootstrap.Modal(document.getElementById('summaryModal')).show();
}

function renderApprovalFlow() {
    // Check if CEO is needed
    const needsCeo = batchData.status === 'pending_ceo' || 
                     batchData.ceo_approved_at ||
                     invoices.some(inv => inv.status === 'pending_ceo' || inv.ceo_approved_at || inv.auto_escalated);
    
    // Build steps dynamically
    let steps = [
        { key: 'rm', label: 'RM', icon: 'bi-person' },
        { key: 'vp', label: 'VOO', icon: 'bi-person-badge' },
    ];
    
    if (needsCeo) {
        steps.push({ key: 'ceo', label: 'CEO', icon: 'bi-person-fill' });
    }
    
    steps.push({ key: 'finance', label: 'Finance', icon: 'bi-bank' });
    
    // Status order mapping
    let statusOrder;
    if (needsCeo) {
        statusOrder = {
            'submitted': 0, 'resubmitted': 0, 'pending_rm': 0,
            'pending_vp': 1, 'pending_ceo': 2, 'pending_finance': 3,
            'approved': 4, 'paid': 4, 'rejected': -1
        };
    } else {
        statusOrder = {
            'submitted': 0, 'resubmitted': 0, 'pending_rm': 0,
            'pending_vp': 1, 'pending_finance': 2,
            'approved': 3, 'paid': 3, 'rejected': -1
        };
    }
    
    const currentStep = statusOrder[batchData.status] ?? 0;
    
    let html = '';
    steps.forEach((step, i) => {
        let iconClass = 'waiting';
        if (batchData.status === 'rejected') iconClass = 'rejected';
        else if (i < currentStep) iconClass = 'done';
        else if (i === currentStep) iconClass = 'current';
        
        html += `
            <div class="approval-step">
                <div class="step-icon ${iconClass}"><i class="bi ${step.icon}"></i></div>
                <div class="step-label">${step.label}</div>
            </div>
        `;
    });
    
    $('#approvalFlow').html(html);
}

function renderActivityTimeline() {
    let html = '';
    
    if (batchData.submitted_at) {
        html += createActivityItem('blue', 'Batch Submitted', batchData.submitted_at);
    }
    if (batchData.rm_approved_at) {
        html += createActivityItem('green', 'RM Approved', batchData.rm_approved_at);
    }
    if (batchData.vp_approved_at) {
        html += createActivityItem('green', 'VOO Approved', batchData.vp_approved_at);
    }
    if (batchData.ceo_approved_at) {
        html += createActivityItem('green', 'CEO Approved', batchData.ceo_approved_at);
    }
    if (batchData.finance_approved_at) {
        html += createActivityItem('green', 'Finance Approved', batchData.finance_approved_at);
    }
    if (batchData.rejected_at) {
        html += createActivityItem('red', `Rejected by ${batchData.rejected_by_role || 'Unknown'}`, batchData.rejected_at);
    }
    if (batchData.paid_at) {
        html += createActivityItem('green', 'Payment Completed', batchData.paid_at);
    }
    
    // Current pending status
    if (!batchData.approved_at && !batchData.rejected_at && !batchData.paid_at) {
        const pendingLabels = {
            'submitted': 'Awaiting Review',
            'resubmitted': 'Awaiting Review',
            'pending_rm': 'Pending RM Approval',
            'pending_vp': 'Pending VOO Approval',
            'pending_ceo': 'Pending CEO Approval (Escalated)',
            'pending_finance': 'Pending Finance Approval',
        };
        if (pendingLabels[batchData.status]) {
            html += `
                <div class="activity-item">
                    <div class="activity-dot yellow"></div>
                    <div class="activity-text">
                        <div class="activity-label">${pendingLabels[batchData.status]}</div>
                        <div class="activity-time">In Progress</div>
                    </div>
                </div>
            `;
        }
    }
    
    if (!html) {
        html = '<div class="text-muted text-center py-2" style="font-size: 12px;">No activity yet</div>';
    }
    
    $('#activityTimeline').html(html);
}

function createActivityItem(color, label, datetime) {
    return `
        <div class="activity-item">
            <div class="activity-dot ${color}"></div>
            <div class="activity-text">
                <div class="activity-label">${label}</div>
                <div class="activity-time">${formatDateTime(datetime)}</div>
            </div>
        </div>
    `;
}

// =====================================================
// HELPERS
// =====================================================
function getStatusBadge(status) {
    const labels = { 'draft': 'Draft', 'submitted': 'Submitted', 'resubmitted': 'Resubmitted', 'pending_rm': 'Pending RM', 'pending_vp': 'Pending VOO', 'pending_ceo': 'Pending CEO', 'pending_finance': 'Pending Finance', 'approved': 'Approved', 'rejected': 'Rejected', 'paid': 'Paid', 'partial': 'Partial' };
    return `<span class="badge-status badge-${status}">${labels[status] || status}</span>`;
}

function getInvoiceStatusBadge(status) {
    const labels = { 'draft': 'Draft', 'submitted': 'Submitted', 'resubmitted': 'Resubmitted', 'pending_rm': 'RM', 'pending_vp': 'VOO', 'pending_ceo': 'CEO', 'pending_finance': 'Finance', 'approved': 'Approved', 'rejected': 'Rejected', 'paid': 'Paid' };
    return `<span class="badge-inv-status badge-${status}">${labels[status] || status}</span>`;
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
    return new Date(str).toLocaleString('en-IN', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}
</script>
@endpush
