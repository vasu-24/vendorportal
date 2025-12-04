{{-- Step 1: Company Information + Contact Details --}}

<div class="mb-3">
    <h6 class="fw-semibold mb-1">Company Information & Contact Details</h6>
    <small class="text-muted">Step 1 of 4</small>
</div>

<!-- Company Information -->
<div class="form-section mb-3">
    <div class="section-title">Company Information</div>
    
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label form-label-sm">Legal Entity Name {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="legal_entity_name" class="form-control form-control-sm" placeholder="Enter legal entity name" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Business Type {{-- <span class="text-danger">*</span> --}}</label>
            <select name="business_type" class="form-select form-select-sm" {{-- required --}}>
                <option value="">Select business type</option>
                <option value="Proprietorship">Proprietorship</option>
                <option value="Partnership">Partnership</option>
                <option value="LLP">LLP</option>
                <option value="Pvt Ltd">Private Limited</option>
                <option value="Public Ltd">Public Limited</option>
                <option value="Others">Others</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Date of Incorporation {{-- <span class="text-danger">*</span> --}}</label>
            <input type="date" name="incorporation_date" class="form-control form-control-sm" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Website</label>
            <input type="url" name="website" class="form-control form-control-sm" placeholder="https://example.com">
        </div>
        
        <div class="col-12">
            <label class="form-label form-label-sm">Registered Office Address {{-- <span class="text-danger">*</span> --}}</label>
            <textarea name="registered_address" class="form-control form-control-sm" rows="2" placeholder="Enter registered office address" {{-- required --}}></textarea>
        </div>
        
        <div class="col-12">
            <label class="form-label form-label-sm">Corporate Office Address <small class="text-muted">(if different)</small></label>
            <textarea name="corporate_address" class="form-control form-control-sm" rows="2" placeholder="Leave blank if same as above"></textarea>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Parent Company <small class="text-muted">(if any)</small></label>
            <input type="text" name="parent_company" class="form-control form-control-sm" placeholder="Enter parent company">
        </div>
    </div>
</div>

<!-- Contact Details -->
<div class="form-section mb-3">
    <div class="section-title">Contact Details</div>
    
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label form-label-sm">Primary Contact Person {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="contact_person" class="form-control form-control-sm" placeholder="Enter contact person" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Designation {{-- <span class="text-danger">*</span> --}}</label>
            <input type="text" name="designation" class="form-control form-control-sm" placeholder="e.g. Manager" {{-- required --}}>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Mobile Number {{-- <span class="text-danger">*</span> --}}</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text">+91</span>
                <input type="tel" name="mobile" class="form-control form-control-sm" placeholder="10 digit number" pattern="[0-9]{10}" maxlength="10" {{-- required --}}>
            </div>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Email ID {{-- <span class="text-danger">*</span> --}}</label>
            <input type="email" name="contact_email" class="form-control form-control-sm" placeholder="contact@company.com" {{-- required --}}>
        </div>
    </div>
</div>