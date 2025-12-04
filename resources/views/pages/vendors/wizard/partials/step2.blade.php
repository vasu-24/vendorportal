{{-- Step 2: Statutory & Compliance + Banking Details --}}

<div class="mb-3">
    <h6 class="fw-semibold mb-1">Statutory & Banking Information</h6>
    <small class="text-muted">Step 2 of 4</small>
</div>

<!-- Statutory & Compliance -->
<div class="form-section mb-3">
    <div class="section-title">Statutory & Compliance Information</div>
    
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label form-label-sm">PAN Number {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="pan_number" class="form-control form-control-sm" placeholder="ABCDE1234F" maxlength="10" style="text-transform: uppercase;" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">TAN Number</label>
            <input type="text" name="tan_number" class="form-control form-control-sm" placeholder="DELE12345F" maxlength="10" style="text-transform: uppercase;">
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">GSTIN {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="gstin" class="form-control form-control-sm" placeholder="22ABCDE1234F1Z5" maxlength="15" style="text-transform: uppercase;" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">CIN <small class="text-muted">(for companies)</small></label>
            <input type="text" name="cin" class="form-control form-control-sm" placeholder="U12345MH2000PTC123456" maxlength="21" style="text-transform: uppercase;">
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">MSME Registered?</label>
            <select name="msme_registered" class="form-select form-select-sm" id="msmeSelect">
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        
        <div class="col-md-6" id="udyamUploadBox" style="display: none;">
            <label class="form-label form-label-sm">Udyam Certificate</label>
            <input type="file" name="udyam_certificate" class="form-control form-control-sm" id="udyamCertificate" accept=".pdf,.jpg,.jpeg,.png">
            <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
        </div>
    </div>
</div>

<!-- Banking Details -->
<div class="form-section mb-3">
    <div class="section-title">Banking Details</div>
    
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label form-label-sm">Bank Name {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="bank_name" class="form-control form-control-sm" placeholder="State Bank of India" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Branch Address {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="branch_address" class="form-control form-control-sm" placeholder="Branch address" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Account Holder Name {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="account_holder_name" class="form-control form-control-sm" placeholder="As per bank records" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Account Number {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="account_number" class="form-control form-control-sm" placeholder="Account number" maxlength="18" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">IFSC Code {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="ifsc_code" class="form-control form-control-sm" placeholder="SBIN0001234" maxlength="11" style="text-transform: uppercase;" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Type of Account {{-- <span class="text-danger">*</span> --}}</label>
            <select name="account_type" class="form-select form-select-sm" {{-- required --}}>
                <option value="">Select account type</option>
                <option value="Current">Current Account</option>
                <option value="Savings">Savings Account</option>
            </select>
        </div>
        
        <div class="col-12">
            <label class="form-label form-label-sm">Cancelled Cheque {{-- <span class="text-danger">*</span> --}}</label>
            <input type="file" name="cancelled_cheque" class="form-control form-control-sm" id="cancelledCheque" accept=".pdf,.jpg,.jpeg,.png" {{-- required --}}>
            <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
        </div>
    </div>
</div>

<script>
document.getElementById('msmeSelect').addEventListener('change', function() {
    const udyamBox = document.getElementById('udyamUploadBox');
    udyamBox.style.display = this.value === 'Yes' ? 'block' : 'none';
});
</script>