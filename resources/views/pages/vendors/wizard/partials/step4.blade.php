{{-- Step 4: KYC Documents Upload + Review & Submit --}}

<!-- KYC Documents Upload Section -->
<div class="form-section">
    <div class="form-section-title">
        <i class="bi bi-folder-check"></i>KYC Documents Upload
    </div>
    
    <div class="row g-3">
        <!-- PAN Card -->
        <div class="col-md-6">
            <label class="form-label fw-medium">PAN Card</label>
            <div class="file-upload-box">
                <input type="file" name="doc_pan_card" class="file-upload-input" 
                       id="docPanCard" accept=".pdf,.jpg,.jpeg,.png" hidden>
                <label for="docPanCard" class="file-upload-label mb-0" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload me-1"></i>Click to upload
                </label>
                <div class="small text-muted mt-1">PDF, JPG, PNG (Max 2MB)</div>
            </div>
        </div>
        
        <!-- GST Certificate -->
        <div class="col-md-6">
            <label class="form-label fw-medium">GST Certificate</label>
            <div class="file-upload-box">
                <input type="file" name="doc_gst_certificate" class="file-upload-input" 
                       id="docGstCertificate" accept=".pdf,.jpg,.jpeg,.png" hidden>
                <label for="docGstCertificate" class="file-upload-label mb-0" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload me-1"></i>Click to upload
                </label>
                <div class="small text-muted mt-1">PDF, JPG, PNG (Max 2MB)</div>
            </div>
        </div>
        
        <!-- Certificate of Incorporation -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Certificate of Incorporation</label>
            <div class="file-upload-box">
                <input type="file" name="doc_incorporation_certificate" class="file-upload-input" 
                       id="docIncorporation" accept=".pdf,.jpg,.jpeg,.png" hidden>
                <label for="docIncorporation" class="file-upload-label mb-0" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload me-1"></i>Click to upload
                </label>
                <div class="small text-muted mt-1">PDF, JPG, PNG (Max 2MB)</div>
            </div>
        </div>
        
        <!-- MOA/AOA or Partnership Deed -->
        <div class="col-md-6">
            <label class="form-label fw-medium">MOA/AOA or Partnership Deed</label>
            <div class="file-upload-box">
                <input type="file" name="doc_moa_aoa" class="file-upload-input" 
                       id="docMoaAoa" accept=".pdf,.jpg,.jpeg,.png" hidden>
                <label for="docMoaAoa" class="file-upload-label mb-0" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload me-1"></i>Click to upload
                </label>
                <div class="small text-muted mt-1">PDF, JPG, PNG (Max 2MB)</div>
            </div>
        </div>
        
        <!-- MSME Certificate -->
        <div class="col-md-6">
            <label class="form-label fw-medium">MSME Certificate <small class="text-muted">(if any)</small></label>
            <div class="file-upload-box">
                <input type="file" name="doc_msme_certificate" class="file-upload-input" 
                       id="docMsme" accept=".pdf,.jpg,.jpeg,.png" hidden>
                <label for="docMsme" class="file-upload-label mb-0" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload me-1"></i>Click to upload
                </label>
                <div class="small text-muted mt-1">PDF, JPG, PNG (Max 2MB)</div>
            </div>
        </div>
        
        <!-- Other Documents -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Other Documents <small class="text-muted">(if any)</small></label>
            <div class="file-upload-box">
                <input type="file" name="doc_other" class="file-upload-input" 
                       id="docOther" accept=".pdf,.jpg,.jpeg,.png" hidden>
                <label for="docOther" class="file-upload-label mb-0" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload me-1"></i>Click to upload
                </label>
                <div class="small text-muted mt-1">PDF, JPG, PNG (Max 2MB)</div>
            </div>
        </div>
    </div>
</div>

<!-- Review Section -->
<div class="form-section">
    <div class="form-section-title">
        <i class="bi bi-clipboard-check"></i>Review & Declaration
    </div>
    
    <!-- Review Summary -->
    <div class="p-3 rounded mb-4" style="background: var(--bg-light);">
        <h6 class="fw-bold mb-3" style="color: var(--primary-blue);">
            <i class="bi bi-info-circle me-1"></i>Before You Submit
        </h6>
        <p class="text-muted mb-2">Please ensure the following before submitting your registration:</p>
        <ul class="text-muted mb-0" style="padding-left: 1.2rem;">
            <li>All company and contact information is accurate</li>
            <li>Bank details are correct for payment processing</li>
            <li>All uploaded documents are clear and readable</li>
            <li>Statutory information matches your official records</li>
        </ul>
    </div>
    
    <!-- Declaration Checkboxes -->
    <div class="border rounded p-3" style="background: #fffbeb;">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="declaration_accurate" 
                   id="declarationAccurate" value="1">
            <label class="form-check-label" for="declarationAccurate">
                I hereby declare that all the information provided above is true, complete, and accurate to the best of my knowledge.
            </label>
        </div>
        
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="declaration_authorized" 
                   id="declarationAuthorized" value="1">
            <label class="form-check-label" for="declarationAuthorized">
                I am authorized to submit this registration on behalf of the company/organization.
            </label>
        </div>
        
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="declaration_terms" 
                   id="declarationTerms" value="1">
            <label class="form-check-label" for="declarationTerms">
                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>.
            </label>
        </div>
    </div>
    
    <!-- Digital Signature -->
    <div class="mt-4">
        <label class="form-label fw-medium">Digital Signature <small class="text-muted">(Type your full name)</small></label>
        <input type="text" name="digital_signature" class="form-control" 
               placeholder="Type your full name as digital signature" 
               style="font-family: 'Brush Script MT', cursive; font-size: 1.5rem;">
    </div>
    
    <!-- Submission Date -->
    <div class="mt-3">
        <label class="form-label fw-medium">Submission Date</label>
        <input type="text" class="form-control" value="{{ date('d M Y, h:i A') }}" readonly 
               style="background: var(--bg-light);">
    </div>
</div>

<!-- Step Info -->
<div class="alert alert-success d-flex align-items-center" role="alert">
    <i class="bi bi-check-circle me-2 fs-5"></i>
    <div>
        <strong>Final Step!</strong> Upload your KYC documents and review your information before submitting.
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-blue); color: white;">
                <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Terms and Conditions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Introduction</h6>
                <p>These terms and conditions govern your registration as a vendor on our portal. By submitting your registration, you agree to be bound by these terms.</p>
                
                <h6>2. Vendor Obligations</h6>
                <p>As a registered vendor, you agree to provide accurate and complete information, maintain up-to-date records, comply with all applicable laws and regulations, and deliver products/services as per agreed terms.</p>
                
                <h6>3. Data Protection</h6>
                <p>We are committed to protecting your personal and business data. All information collected will be used solely for vendor management purposes and will not be shared with third parties without your consent.</p>
                
                <h6>4. Payment Terms</h6>
                <p>Payment terms will be as agreed upon in individual purchase orders or contracts. Bank details provided must be accurate for timely payment processing.</p>
                
                <h6>5. Termination</h6>
                <p>Either party may terminate the vendor relationship with appropriate notice. Upon termination, all pending obligations must be fulfilled.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Privacy Modal -->
<div class="modal fade" id="privacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--primary-blue); color: white;">
                <h5 class="modal-title"><i class="bi bi-shield-check me-2"></i>Privacy Policy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Information We Collect</h6>
                <p>We collect company information, contact details, statutory information, banking details, and KYC documents as part of the vendor registration process.</p>
                
                <h6>How We Use Your Information</h6>
                <p>Your information is used for vendor verification and onboarding, payment processing, communication regarding orders and contracts, and compliance with legal requirements.</p>
                
                <h6>Data Security</h6>
                <p>We implement appropriate technical and organizational measures to protect your data against unauthorized access, alteration, disclosure, or destruction.</p>
                
                <h6>Data Retention</h6>
                <p>Your data will be retained for the duration of our business relationship and as required by applicable laws and regulations.</p>
                
   
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>