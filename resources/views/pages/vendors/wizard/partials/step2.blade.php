{{-- Step 2: Statutory & Compliance + Banking Details --}}

<!-- Statutory & Compliance Section -->
<div class="form-section">
    <div class="form-section-title">
        <i class="bi bi-file-earmark-text"></i>Statutory & Compliance Information
    </div>
    
    <div class="row g-3">
        <!-- PAN Number -->
        <div class="col-md-6">
            <label class="form-label fw-medium">PAN Number</label>
            <input type="text" name="pan_number" class="form-control" 
                   placeholder="e.g. ABCDE1234F" 
                   maxlength="10" style="text-transform: uppercase;">
        </div>
        
        <!-- TAN Number -->
        <div class="col-md-6">
            <label class="form-label fw-medium">TAN Number</label>
            <input type="text" name="tan_number" class="form-control" 
                   placeholder="e.g. DELE12345F" 
                   maxlength="10" style="text-transform: uppercase;">
        </div>
        
        <!-- GSTIN -->
        <div class="col-md-6">
            <label class="form-label fw-medium">GSTIN</label>
            <input type="text" name="gstin" class="form-control" 
                   placeholder="e.g. 22ABCDE1234F1Z5" 
                   maxlength="15" style="text-transform: uppercase;">
        </div>
        
        <!-- CIN -->
        <div class="col-md-6">
            <label class="form-label fw-medium">CIN <small class="text-muted">(for companies)</small></label>
            <input type="text" name="cin" class="form-control" 
                   placeholder="e.g. U12345MH2000PTC123456" 
                   maxlength="21" style="text-transform: uppercase;">
        </div>
        
        <!-- MSME Registration -->
        <div class="col-md-6">
            <label class="form-label fw-medium">MSME Registered?</label>
            <select name="msme_registered" class="form-select" id="msmeSelect">
                <option value="">-- Select --</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        
        <!-- Udyam Certificate Upload -->
        <div class="col-md-6" id="udyamUploadBox" style="display: none;">
            <label class="form-label fw-medium">Udyam Certificate</label>
            <div class="file-upload-box">
                <input type="file" name="udyam_certificate" class="file-upload-input" 
                       id="udyamCertificate" accept=".pdf,.jpg,.jpeg,.png" hidden>
                <label for="udyamCertificate" class="file-upload-label mb-0" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload me-1"></i>Click to upload
                </label>
                <div class="small text-muted mt-1">PDF, JPG, PNG (Max 2MB)</div>
            </div>
        </div>
    </div>
</div>

<!-- Banking Details Section -->
<div class="form-section">
    <div class="form-section-title">
        <i class="bi bi-bank"></i>Banking Details
    </div>
    
    <div class="row g-3">
        <!-- Bank Name -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" 
                   placeholder="e.g. State Bank of India">
        </div>
        
        <!-- Branch Address -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Branch Address</label>
            <input type="text" name="branch_address" class="form-control" 
                   placeholder="Enter branch address">
        </div>
        
        <!-- Account Holder Name -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Account Holder Name</label>
            <input type="text" name="account_holder_name" class="form-control" 
                   placeholder="Name as per bank records">
        </div>
        
        <!-- Account Number -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Account Number</label>
            <input type="text" name="account_number" class="form-control" 
                   placeholder="Enter account number" maxlength="18">
        </div>
        
        <!-- IFSC Code -->
        <div class="col-md-6">
            <label class="form-label fw-medium">IFSC Code</label>
            <input type="text" name="ifsc_code" class="form-control" 
                   placeholder="e.g. SBIN0001234" 
                   maxlength="11" style="text-transform: uppercase;">
        </div>
        
        <!-- Account Type -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Type of Account</label>
            <select name="account_type" class="form-select">
                <option value="">-- Select Account Type --</option>
                <option value="Current">Current Account</option>
                <option value="Savings">Savings Account</option>
            </select>
        </div>
        
        <!-- Cancelled Cheque Upload -->
        <div class="col-12">
            <label class="form-label fw-medium">Cancelled Cheque</label>
            <div class="file-upload-box">
                <input type="file" name="cancelled_cheque" class="file-upload-input" 
                       id="cancelledCheque" accept=".pdf,.jpg,.jpeg,.png" hidden>
                <label for="cancelledCheque" class="file-upload-label mb-0" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload me-1"></i>Click to upload or drag & drop
                </label>
                <div class="small text-muted mt-1">PDF, JPG, PNG (Max 2MB)</div>
            </div>
        </div>
    </div>
</div>

<!-- Step Info -->
<div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="bi bi-info-circle me-2 fs-5"></i>
    <div>
        <strong>Step 2 of 4:</strong> Please provide your statutory and banking information for payment processing.
    </div>
</div>

<!-- MSME Toggle Script -->
<script>
document.getElementById('msmeSelect').addEventListener('change', function() {
    const udyamBox = document.getElementById('udyamUploadBox');
    if (this.value === 'Yes') {
        udyamBox.style.display = 'block';
    } else {
        udyamBox.style.display = 'none';
    }
});
</script>