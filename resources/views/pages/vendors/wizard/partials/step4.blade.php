{{-- Step 4: KYC Documents Upload --}}

<div class="mb-3">
    <h6 class="fw-semibold mb-1">KYC Documents & Submission</h6>
    <small class="text-muted">Step 4 of 4</small>
</div>

<!-- KYC Documents -->
<div class="form-section mb-3">
    <div class="section-title">KYC Documents Upload</div>
    
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label form-label-sm">PAN Card {{-- <span class="text-danger">*</span> --}}</label>
            <input type="file" name="doc_pan_card" class="form-control form-control-sm" id="docPanCard" accept=".pdf,.jpg,.jpeg,.png" {{-- required --}}>
            <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">GST Certificate {{-- <span class="text-danger">*</span> --}}</label>
            <input type="file" name="doc_gst_certificate" class="form-control form-control-sm" id="docGstCertificate" accept=".pdf,.jpg,.jpeg,.png" {{-- required --}}>
            <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Certificate of Incorporation {{-- <span class="text-danger">*</span> --}}</label>
            <input type="file" name="doc_incorporation_certificate" class="form-control form-control-sm" id="docIncorporation" accept=".pdf,.jpg,.jpeg,.png" {{-- required --}}>
            <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">MOA/AOA or Partnership Deed {{-- <span class="text-danger">*</span> --}}</label>
            <input type="file" name="doc_moa_aoa" class="form-control form-control-sm" id="docMoaAoa" accept=".pdf,.jpg,.jpeg,.png" {{-- required --}}>
            <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">MSME Certificate <small class="text-muted">(if any)</small></label>
            <input type="file" name="doc_msme_certificate" class="form-control form-control-sm" id="docMsme" accept=".pdf,.jpg,.jpeg,.png">
            <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Other Documents <small class="text-muted">(if any)</small></label>
            <input type="file" name="doc_other" class="form-control form-control-sm" id="docOther" accept=".pdf,.jpg,.jpeg,.png">
            <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
        </div>
    </div>
</div>

<!-- Review Section -->
<div class="form-section mb-3">
    <div class="section-title">Review & Submit</div>
    
    <div class="alert alert-light border p-3">
        <h6 class="fw-semibold mb-2" style="color: var(--primary-blue);">Before You Submit</h6>
        <ul class="small text-muted mb-0" style="padding-left: 1.2rem;">
            <li>All company and contact information is accurate</li>
            <li>Bank details are correct for payment processing</li>
            <li>All uploaded documents are clear and readable</li>
            <li>Statutory information matches your official records</li>
        </ul>
    </div>
    
    {{-- COMMENTED OUT - Declaration Checkboxes --}}
    {{-- 
    <div class="border rounded p-3 mb-3" style="background: #fffbeb;">
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="declaration_accurate" id="declarationAccurate" value="1">
            <label class="form-check-label small" for="declarationAccurate">
                I declare all information provided is true and accurate.
            </label>
        </div>
        
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="declaration_authorized" id="declarationAuthorized" value="1">
            <label class="form-check-label small" for="declarationAuthorized">
                I am authorized to submit this registration.
            </label>
        </div>
        
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="declaration_terms" id="declarationTerms" value="1">
            <label class="form-check-label small" for="declarationTerms">
                I agree to the Terms and Conditions and Privacy Policy.
            </label>
        </div>
    </div>
    --}}
    
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label form-label-sm">Digital Signature {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="digital_signature" class="form-control form-control-sm" placeholder="Type your full name" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Submission Date</label>
            <input type="text" class="form-control form-control-sm" value="{{ date('d M Y, h:i A') }}" readonly style="background: var(--bg-light);">
        </div>
    </div>
</div>

{{-- COMMENTED OUT - Terms & Privacy Modals --}}
{{-- 
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-blue); color: white;">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Content here...
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-blue); color: white;">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Content here...
            </div>
        </div>
    </div>
</div>
--}}