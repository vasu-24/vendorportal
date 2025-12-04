<!-- =====================================================
     REJECT MODAL
 Reject Modal -->



<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <!-- Header - Light Color Instead of Red -->
            <div class="modal-header" style="background: linear-gradient(135deg, #fb7185 0%, #f472b6 100%); border: none;">
                <h5 class="modal-title text-white">
                    <i class="bi bi-exclamation-triangle me-2"></i>Reject Vendor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <!-- Body - Clean & Professional -->
            <div class="modal-body p-4">
                
                <div class="alert alert-warning border-0 mb-4" style="background: #fef3c7;">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> The vendor will be notified via email with the rejection reason and can resubmit their application after making corrections.
                </div>
                
                <form id="rejectForm">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" 
                                  id="rejectReason" 
                                  rows="4" 
                                  placeholder="Please provide a clear reason for rejection so the vendor can make necessary corrections..."
                                  required></textarea>
                        <small class="text-muted">This reason will be sent to the vendor via email.</small>
                    </div>
                </form>
                
            </div>
            
            <!-- Footer -->
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" onclick="confirmReject()">
                    <i class="bi bi-send me-1"></i>Confirm Rejection
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