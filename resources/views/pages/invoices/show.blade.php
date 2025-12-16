@extends('layouts.app')
@section('title', 'Invoice Details')

@section('content')
<style>
    .card { border: none; border-radius: 8px; }
    .modal-content { border: none; border-radius: 8px; }

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
    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: 0.3px;
        margin: 0;
    }
    .page-subtitle {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }

    /* Soft Badge Colors */
    .badge-draft { background-color: #e9ecef; color: #495057; font-weight: 500; }
    .badge-submitted { background-color: #e2e3f1; color: #383d6e; font-weight: 500; }
    .badge-under-review { background-color: #fff3cd; color: #856404; font-weight: 500; }
    .badge-approved { background-color: #d4edda; color: #155724; font-weight: 500; }
    .badge-rejected { background-color: #f8d7da; color: #721c24; font-weight: 500; }
    .badge-resubmitted { background-color: #cce5ff; color: #004085; font-weight: 500; }
    .badge-paid { background-color: #d1ecf1; color: #0c5460; font-weight: 500; }

    .timeline-item {
        position: relative;
        padding-left: 30px;
        padding-bottom: 20px;
        border-left: 2px solid #e9ecef;
    }
    .timeline-item:last-child {
        border-left: 2px solid transparent;
        padding-bottom: 0;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -8px;
        top: 0;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #0d6efd;
        border: 2px solid #fff;
    }
    .timeline-item.success::before { background: #198754; }
    .timeline-item.warning::before { background: #ffc107; }
    .timeline-item.danger::before { background: #dc3545; }
    .timeline-item.info::before { background: #0dcaf0; }
</style>

<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="d-flex align-items-start gap-3">
            <div class="page-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <h2 class="page-title">Invoice Details</h2>
                <p class="page-subtitle">
                    <a href="{{ route('invoices.index') }}" class="text-decoration-none">Invoices</a> / 
                    <span id="breadcrumbInvoiceNumber">Loading...</span>
                </p>
            </div>
        </div>
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to List
        </a>
    </div>

    {{-- Loading --}}
    <div id="loadingSpinner" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
        <p class="mt-2 text-muted">Loading invoice details...</p>
    </div>

    {{-- Main Content --}}
    <div id="mainContent" style="display: none;">
        <div class="row">
            {{-- Left Column --}}
            <div class="col-lg-8">
                
                {{-- Status Alert --}}
                <div id="statusAlert" class="mb-4"></div>

                {{-- Invoice Info --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>Invoice Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted small">Invoice Number</div>
                                <div class="fw-bold" id="invoiceNumber">-</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Contract</div>
                                <div id="contractNumber" class="fw-medium text-primary">-</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Status</div>
                                <div id="invoiceStatus">-</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Invoice Date</div>
                                <div id="invoiceDate">-</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Due Date</div>
                                <div id="dueDate">-</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Submitted At</div>
                                <div id="submittedAt">-</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Description</div>
                                <div id="description">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vendor Info --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-building me-2"></i>Vendor Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small">Vendor Name</div>
                                <div class="fw-semibold" id="vendorName">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Email</div>
                                <div id="vendorEmail">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Phone</div>
                                <div id="vendorPhone">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Company</div>
                                <div id="vendorCompany">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-list-ul me-2"></i>Line Items</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">#</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Rate</th>
                                        <th class="text-end">GST %</th>
                                        <th class="text-end">GST Amt</th>
                                        <th class="text-end pe-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="lineItemsBody">
                                    <tr><td colspan="8" class="text-center py-3 text-muted">No line items</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Amount Summary --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-currency-rupee me-2"></i>Amount Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row justify-content-end">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">Base Total</td>
                                        <td class="text-end fw-medium" id="baseTotal">₹0.00</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">GST Total</td>
                                        <td class="text-end fw-medium" id="gstTotal">₹0.00</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="fw-bold">Grand Total</td>
                                        <td class="text-end fw-bold fs-5 text-success" id="grandTotal">₹0.00</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-paperclip me-2"></i>Attachments</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3" id="attachmentsContainer">
                            <div class="col-12 text-center text-muted py-3">
                                <i class="bi bi-paperclip fs-4 d-block mb-2"></i>No attachments
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Rejection Details --}}
                <div class="card shadow-sm mb-4 border-danger" id="rejectionCard" style="display: none;">
                    <div class="card-header bg-danger text-white py-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-x-circle me-2"></i>Rejection Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger mb-0">
                            <strong>Reason:</strong>
                            <p class="mb-0 mt-1" id="rejectionReason"></p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column --}}
            <div class="col-lg-4">
                
                {{-- Actions --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-lightning me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body" id="actionCardBody">
                        <p class="text-muted text-center">Loading...</p>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light py-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2"></i>Timeline</h6>
                    </div>
                    <div class="card-body" id="timelineContainer">
                        <p class="text-muted text-center">No timeline data</p>
                    </div>
                </div>

                {{-- Payment Info --}}
                <div class="card shadow-sm mb-4 border-success" id="paymentCard" style="display: none;">
                    <div class="card-header bg-success text-white py-2">
                        <h6 class="mb-0 fw-semibold"><i class="bi bi-check-circle me-2"></i>Payment Completed</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="text-muted small">Payment Date:</span>
                            <div class="fw-semibold" id="paymentDate">-</div>
                        </div>
                        <div>
                            <span class="text-muted small">Reference:</span>
                            <div class="fw-semibold" id="paymentReference">-</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold text-danger"><i class="bi bi-x-circle me-2"></i>Reject Invoice</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Please provide a reason for rejecting this invoice.</p>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejectReason" rows="4" placeholder="Enter detailed reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmRejectBtn">
                    <i class="bi bi-x-circle me-1"></i>Reject Invoice
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Mark Paid Modal --}}
<div class="modal fade" id="markPaidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2 bg-light">
                <h6 class="modal-title fw-semibold"><i class="bi bi-currency-rupee me-2"></i>Mark as Paid</h6>
                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Enter payment details for this invoice.</p>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Payment Reference / UTR</label>
                    <input type="text" class="form-control" id="paymentRef" placeholder="Transaction ID / UTR">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Payment Notes (Optional)</label>
                    <textarea class="form-control" id="paymentNotes" rows="2" placeholder="Any additional notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="confirmPaidBtn">
                    <i class="bi bi-check-circle me-1"></i>Mark as Paid
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
    let invoiceData = null;

    // =====================================================
    // INIT
    // =====================================================
    $(document).ready(function() {
        loadInvoice();
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
                    renderInvoice();
                    $('#loadingSpinner').hide();
                    $('#mainContent').show();
                }
            })
            .catch(err => {
                console.error('Failed:', err);
                $('#loadingSpinner').html(`
                    <div class="text-danger">
                        <i class="bi bi-exclamation-circle fs-1"></i>
                        <p class="mt-2">Failed to load invoice details</p>
                        <a href="{{ route('invoices.index') }}" class="btn btn-primary btn-sm">Back to List</a>
                    </div>
                `);
            });
    }

    // =====================================================
    // RENDER INVOICE
    // =====================================================
    function renderInvoice() {
        const inv = invoiceData;

        // Breadcrumb
        $('#breadcrumbInvoiceNumber').text(inv.invoice_number);

        // Invoice Info
        $('#invoiceNumber').text(inv.invoice_number);
        $('#invoiceStatus').html(getStatusBadge(inv.status));
        $('#invoiceDate').text(formatDate(inv.invoice_date));
        $('#dueDate').text(inv.due_date ? formatDate(inv.due_date) : '-');
        $('#submittedAt').text(inv.submitted_at ? formatDateTime(inv.submitted_at) : '-');
        $('#description').text(inv.description || '-');
        $('#contractNumber').text(inv.contract?.contract_number || '-');

        // Vendor Info
        $('#vendorName').text(inv.vendor?.vendor_name || '-');
        $('#vendorEmail').text(inv.vendor?.vendor_email || '-');
        $('#vendorPhone').text(inv.vendor?.companyInfo?.phone || inv.vendor?.phone || '-');
        $('#vendorCompany').text(inv.vendor?.companyInfo?.legal_entity_name || '-');

        // Amounts - FIXED FIELD NAMES
        $('#baseTotal').text('₹' + formatNumber(inv.base_total));
        $('#gstTotal').text('₹' + formatNumber(inv.gst_total));
        $('#grandTotal').text('₹' + formatNumber(inv.grand_total));

        // Line Items
        renderLineItems(inv.items);

        // Attachments
        renderAttachments(inv.attachments);

        // Timeline
        renderTimeline(inv);

        // Actions
        renderActionButtons(inv.status);

        // Status Alert
        renderStatusAlert(inv.status);

        // Rejection
        if (inv.rejection_reason) {
            $('#rejectionReason').text(inv.rejection_reason);
            $('#rejectionCard').show();
        }

        // Payment Info
        if (inv.status === 'paid' && inv.paid_at) {
            $('#paymentDate').text(formatDateTime(inv.paid_at));
            $('#paymentReference').text(inv.payment_reference || '-');
            $('#paymentCard').show();
        }
    }

    // =====================================================
    // RENDER LINE ITEMS
    // =====================================================
    function renderLineItems(items) {
        const tbody = $('#lineItemsBody');
        
        if (!items || items.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center py-3 text-muted">No line items</td></tr>');
            return;
        }

        let html = '';
        items.forEach((item, i) => {
            const baseAmount = parseFloat(item.quantity) * parseFloat(item.rate);
        const gstAmount = baseAmount * (parseFloat(item.tax_percent || 0) / 100);
            const total = baseAmount + gstAmount;

            html += `
                <tr>
                    <td class="ps-3">${i + 1}</td>
                    <td>${item.category?.name || '-'}</td>
                 <td>${item.particulars || '-'}</td>

                    <td class="text-center">${item.quantity} ${item.unit || ''}</td>
                    <td class="text-end">₹${formatNumber(item.rate)}</td>
               <td class="text-end">${item.tax_percent || 0}%</td>
                    <td class="text-end">₹${formatNumber(gstAmount)}</td>
                    <td class="text-end pe-3 fw-semibold">₹${formatNumber(total)}</td>
                </tr>
            `;
        });

        tbody.html(html);
    }

    // =====================================================
    // RENDER STATUS ALERT
    // =====================================================
    function renderStatusAlert(status) {
        const alerts = {
            'submitted': { class: 'info', icon: 'send', title: 'Submitted', text: 'Waiting for review' },
            'under_review': { class: 'warning', icon: 'hourglass-split', title: 'Under Review', text: 'Currently being reviewed' },
            'approved': { class: 'success', icon: 'check-circle', title: 'Approved', text: 'Pending payment' },
            'rejected': { class: 'danger', icon: 'x-circle', title: 'Rejected', text: 'Invoice has been rejected' },
            'paid': { class: 'primary', icon: 'currency-rupee', title: 'Paid', text: 'Payment completed' }
        };

        const alert = alerts[status];
        if (alert) {
            $('#statusAlert').html(`
                <div class="alert alert-${alert.class} d-flex align-items-center py-2">
                    <i class="bi bi-${alert.icon} fs-4 me-3"></i>
                    <div>
                        <strong>${alert.title}</strong>
                        <span class="ms-2 small">${alert.text}</span>
                    </div>
                </div>
            `);
        }
    }

    // =====================================================
    // RENDER ACTION BUTTONS
    // =====================================================
    function renderActionButtons(status) {
        let html = '';

        switch (status) {
            case 'submitted':
                html = `
                    <p class="text-muted small mb-3">Start reviewing this invoice</p>
                    <button class="btn btn-warning btn-sm w-100" onclick="startReview()">
                        <i class="bi bi-hourglass-split me-2"></i>Start Review
                    </button>
                `;
                break;

            case 'under_review':
                html = `
                    <p class="text-muted small mb-3">Review and take action</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-sm" onclick="approveInvoice()">
                            <i class="bi bi-check-circle me-2"></i>Approve
                        </button>
                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle me-2"></i>Reject
                        </button>
                    </div>
                `;
                break;

            case 'approved':
                html = `
                    <p class="text-muted small mb-3">Mark as paid when payment is done</p>
                    <button class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#markPaidModal">
                        <i class="bi bi-currency-rupee me-2"></i>Mark as Paid
                    </button>
                `;
                break;

            case 'rejected':
                html = `
                    <div class="text-center py-3">
                        <i class="bi bi-x-circle text-danger fs-1"></i>
                        <p class="text-muted mt-2 mb-0 small">Invoice rejected</p>
                    </div>
                `;
                break;

            case 'paid':
                html = `
                    <div class="text-center py-3">
                        <i class="bi bi-check-circle text-success fs-1"></i>
                        <p class="text-muted mt-2 mb-0 small">Payment completed</p>
                    </div>
                `;
                break;

            default:
                html = `<p class="text-muted text-center small">No actions available</p>`;
        }

        $('#actionCardBody').html(html);
    }

    // =====================================================
    // RENDER ATTACHMENTS
    // =====================================================
    function renderAttachments(attachments) {
        if (!attachments || attachments.length === 0) {
            $('#attachmentsContainer').html(`
                <div class="col-12 text-center text-muted py-3">
                    <i class="bi bi-paperclip fs-4 d-block mb-2"></i>No attachments
                </div>
            `);
            return;
        }

        let html = '';
        attachments.forEach(att => {
            const icon = att.attachment_type === 'invoice' ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark text-primary';
            const label = att.attachment_type === 'invoice' ? 'Invoice Document' : 'Supporting Document';

            html += `
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex align-items-center">
                            <i class="bi ${icon} fs-3 me-3"></i>
                            <div class="flex-grow-1">
                                <div class="fw-medium small">${label}</div>
                                <small class="text-muted">${escapeHtml(att.file_name)}</small>
                            </div>
                            <a href="${API_BASE}/${INVOICE_ID}/attachment/${att.id}/download" 
                               class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#attachmentsContainer').html(html);
    }

    // =====================================================
    // RENDER TIMELINE
    // =====================================================
    function renderTimeline(inv) {
        const events = [
            { field: 'created_at', label: 'Created', class: 'info' },
            { field: 'submitted_at', label: 'Submitted', class: 'info' },
            { field: 'reviewed_at', label: 'Review Started', class: 'warning' },
            { field: 'approved_at', label: 'Approved', class: 'success' },
            { field: 'rejected_at', label: 'Rejected', class: 'danger' },
            { field: 'paid_at', label: 'Paid', class: 'success' }
        ];

        let html = '';
        events.forEach(event => {
            if (inv[event.field]) {
                html += `
                    <div class="timeline-item ${event.class}">
                        <div class="fw-medium">${event.label}</div>
                        <small class="text-muted">${formatDateTime(inv[event.field])}</small>
                    </div>
                `;
            }
        });

        if (!html) {
            html = '<p class="text-muted text-center small">No timeline data</p>';
        }

        $('#timelineContainer').html(html);
    }

    // =====================================================
    // ACTIONS
    // =====================================================
    function startReview() {
        if (!confirm('Start reviewing this invoice?')) return;

        axios.post(`${API_BASE}/${INVOICE_ID}/start-review`)
            .then(res => {
                if (res.data.success) {
                    Toast.success('Invoice is now under review');
                    loadInvoice();
                }
            })
            .catch(err => Toast.error(err.response?.data?.message || 'Failed to start review'));
    }

    function approveInvoice() {
        if (!confirm('Approve this invoice?')) return;

        axios.post(`${API_BASE}/${INVOICE_ID}/approve`)
            .then(res => {
                if (res.data.success) {
                    Toast.success('Invoice approved');
                    loadInvoice();
                }
            })
            .catch(err => Toast.error(err.response?.data?.message || 'Failed to approve'));
    }

    function rejectInvoice() {
        const reason = $('#rejectReason').val().trim();
        if (!reason) {
            Toast.warning('Please enter a rejection reason');
            return;
        }

        const btn = $('#confirmRejectBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...');

        axios.post(`${API_BASE}/${INVOICE_ID}/reject`, { rejection_reason: reason })
            .then(res => {
                if (res.data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                    Toast.success('Invoice rejected');
                    loadInvoice();
                }
            })
            .catch(err => Toast.error(err.response?.data?.message || 'Failed to reject'))
            .finally(() => {
                btn.prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Reject Invoice');
            });
    }

    function markAsPaid() {
        const btn = $('#confirmPaidBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');

        axios.post(`${API_BASE}/${INVOICE_ID}/mark-paid`, {
            payment_reference: $('#paymentRef').val(),
            payment_notes: $('#paymentNotes').val()
        })
        .then(res => {
            if (res.data.success) {
                bootstrap.Modal.getInstance(document.getElementById('markPaidModal')).hide();
                Toast.success('Invoice marked as paid');
                loadInvoice();
            }
        })
        .catch(err => Toast.error(err.response?.data?.message || 'Failed to mark as paid'))
        .finally(() => {
            btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i>Mark as Paid');
        });
    }

    // =====================================================
    // HELPERS
    // =====================================================
    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-draft">Draft</span>',
            'submitted': '<span class="badge badge-submitted">Submitted</span>',
            'under_review': '<span class="badge badge-under-review">Under Review</span>',
            'approved': '<span class="badge badge-approved">Approved</span>',
            'rejected': '<span class="badge badge-rejected">Rejected</span>',
            'resubmitted': '<span class="badge badge-resubmitted">Resubmitted</span>',
            'paid': '<span class="badge badge-paid">Paid</span>'
        };
        return badges[status] || `<span class="badge badge-draft">${status}</span>`;
    }

    function formatNumber(num) {
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

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush