<!-- =====================================================
     REJECT MODAL
     ===================================================== -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="bi bi-x-circle me-2"></i>Reject Vendor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning d-flex align-items-center mb-3">
                    <i class="bi bi-exclamation-triangle me-2 fs-5"></i>
                    <small>This action will reject the vendor registration. The vendor will be notified.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Rejection Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rejectionReason" rows="4" 
                              placeholder="Please provide a detailed reason for rejection..."></textarea>
                    <div class="invalid-feedback" id="rejectionReasonError">Please provide a rejection reason.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmRejectBtn" onclick="rejectVendor()">
                    <i class="bi bi-x-circle me-1"></i>Reject Vendor
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================
     REVISION REQUEST MODAL
     ===================================================== -->
<div class="modal fade" id="revisionModal" tabindex="-1" aria-labelledby="revisionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="revisionModalLabel">
                    <i class="bi bi-arrow-repeat me-2"></i>Request Revision
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info d-flex align-items-center mb-3">
                    <i class="bi bi-info-circle me-2 fs-5"></i>
                    <small>The vendor will be notified to update their registration details.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Revision Notes <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="revisionNotes" rows="4" 
                              placeholder="Please specify what needs to be corrected or updated..."></textarea>
                    <div class="invalid-feedback" id="revisionNotesError">Please provide revision notes.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-info" id="confirmRevisionBtn" onclick="requestRevision()">
                    <i class="bi bi-arrow-repeat me-1"></i>Request Revision
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================
     APPROVE CONFIRMATION MODAL
     ===================================================== -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="bi bi-check-circle me-2"></i>Approve Vendor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-3">
                    <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Confirm Approval</h5>
                    <p class="text-muted">Are you sure you want to approve this vendor?</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-medium">Notes (Optional)</label>
                    <textarea class="form-control" id="approvalNotes" rows="2" 
                              placeholder="Add any notes for approval..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn" onclick="confirmApproval()">
                    <i class="bi bi-check-circle me-1"></i>Yes, Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================
     DOCUMENT PREVIEW MODAL
     ===================================================== -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">
                    <i class="bi bi-file-earmark me-2"></i>Document Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="documentPreviewContainer">
                    <!-- Document will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary" id="downloadDocBtn" target="_blank">
                    <i class="bi bi-download me-1"></i>Download
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>