{{-- Step 1: Company Information + Contact Details --}}

<!-- Company Information Section -->
<div class="form-section">
    <div class="form-section-title">
        <i class="bi bi-building"></i>Company Information
    </div>
    
    <div class="row g-3">
        <!-- Legal Entity Name -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Legal Entity Name</label>
            <input type="text" name="legal_entity_name" class="form-control" 
                   placeholder="Enter legal entity name">
        </div>
        
        <!-- Business Type -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Business Type</label>
            <select name="business_type" class="form-select">
                <option value="">-- Select Business Type --</option>
                <option value="Proprietorship">Proprietorship</option>
                <option value="Partnership">Partnership</option>
                <option value="LLP">LLP</option>
                <option value="Pvt Ltd">Private Limited</option>
                <option value="Public Ltd">Public Limited</option>
                <option value="Others">Others</option>
            </select>
        </div>
        
        <!-- Date of Incorporation -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Date of Incorporation</label>
            <input type="date" name="incorporation_date" class="form-control">
        </div>
        
        <!-- Website -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Website</label>
            <input type="url" name="website" class="form-control" 
                   placeholder="https://www.example.com">
        </div>
        
        <!-- Registered Office Address -->
        <div class="col-12">
            <label class="form-label fw-medium">Registered Office Address</label>
            <textarea name="registered_address" class="form-control" rows="2" 
                      placeholder="Enter complete registered office address"></textarea>
        </div>
        
        <!-- Corporate Office Address -->
        <div class="col-12">
            <label class="form-label fw-medium">Corporate Office Address <small class="text-muted">(if different)</small></label>
            <textarea name="corporate_address" class="form-control" rows="2" 
                      placeholder="Enter corporate office address (leave blank if same as above)"></textarea>
        </div>
        
        <!-- Parent Company -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Parent Company <small class="text-muted">(if applicable)</small></label>
            <input type="text" name="parent_company" class="form-control" 
                   placeholder="Enter parent company name">
        </div>
    </div>
</div>

<!-- Contact Details Section -->
<div class="form-section">
    <div class="form-section-title">
        <i class="bi bi-person-lines-fill"></i>Contact Details
    </div>
    
    <div class="row g-3">
        <!-- Primary Contact Person -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Primary Contact Person</label>
            <input type="text" name="contact_person" class="form-control" 
                   placeholder="Enter contact person name">
        </div>
        
        <!-- Designation -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Designation</label>
            <input type="text" name="designation" class="form-control" 
                   placeholder="e.g. Manager, Director">
        </div>
        
        <!-- Mobile Number -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Mobile Number</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-phone"></i> +91</span>
                <input type="tel" name="mobile" class="form-control" 
                       placeholder="10 digit mobile number" 
                       pattern="[0-9]{10}" maxlength="10">
            </div>
        </div>
        
        <!-- Email ID -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Email ID</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="contact_email" class="form-control" 
                       placeholder="contact@company.com">
            </div>
        </div>
    </div>
</div>

<!-- Step Info -->
<div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="bi bi-info-circle me-2 fs-5"></i>
    <div>
        <strong>Step 1 of 4:</strong> Please fill in your company and contact information.
    </div>
</div>