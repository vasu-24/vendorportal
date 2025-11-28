{{-- Step 3: Tax & Regulatory + Business Profile --}}

<!-- Tax & Regulatory Section -->
<div class="form-section">
    <div class="form-section-title">
        <i class="bi bi-receipt"></i>Tax & Regulatory Declarations
    </div>
    
    <div class="row g-3">
        <!-- Tax Residency -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Tax Residency</label>
            <select name="tax_residency" class="form-select">
                <option value="">-- Select --</option>
                <option value="India">India</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <!-- GST Reverse Charge -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Covered under GST Reverse Charge Mechanism?</label>
            <select name="gst_reverse_charge" class="form-select">
                <option value="">-- Select --</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        
        <!-- SEZ Status -->
        <div class="col-md-6">
            <label class="form-label fw-medium">SEZ Status</label>
            <select name="sez_status" class="form-select">
                <option value="">-- Select --</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        
        <!-- TDS Exemption Certificate Upload -->
        <div class="col-md-6">
            <label class="form-label fw-medium">TDS Exemption Certificate <small class="text-muted">(if any)</small></label>
            <div class="file-upload-box">
                <input type="file" name="tds_exemption_certificate" class="file-upload-input" 
                       id="tdsExemption" accept=".pdf,.jpg,.jpeg,.png" hidden>
                <label for="tdsExemption" class="file-upload-label mb-0" style="cursor: pointer;">
                    <i class="bi bi-cloud-upload me-1"></i>Click to upload
                </label>
                <div class="small text-muted mt-1">PDF, JPG, PNG (Max 2MB)</div>
            </div>
        </div>
    </div>
</div>

<!-- Business Profile Section -->
<div class="form-section">
    <div class="form-section-title">
        <i class="bi bi-briefcase"></i>Business Profile
    </div>
    
    <div class="row g-3">
        <!-- Core Business Activities -->
        <div class="col-12">
            <label class="form-label fw-medium">Core Business Activities / Products / Services</label>
            <textarea name="core_activities" class="form-control" rows="3" 
                      placeholder="Describe your core business activities, products, or services..."></textarea>
        </div>
        
        <!-- Number of Employees -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Number of Employees</label>
            <select name="employee_count" class="form-select">
                <option value="">-- Select --</option>
                <option value="1-10">1 - 10</option>
                <option value="11-50">11 - 50</option>
                <option value="51-100">51 - 100</option>
                <option value="101-500">101 - 500</option>
                <option value="501-1000">501 - 1000</option>
                <option value="1000+">1000+</option>
            </select>
        </div>
        
        <!-- Credit Period Offered -->
        <div class="col-md-6">
            <label class="form-label fw-medium">Credit Period Offered</label>
            <select name="credit_period" class="form-select">
                <option value="">-- Select --</option>
                <option value="Immediate">Immediate</option>
                <option value="15 Days">15 Days</option>
                <option value="30 Days">30 Days</option>
                <option value="45 Days">45 Days</option>
                <option value="60 Days">60 Days</option>
                <option value="90 Days">90 Days</option>
            </select>
        </div>
        
        <!-- Annual Turnover -->
        <div class="col-12">
            <label class="form-label fw-medium">Annual Turnover (Last 3 Financial Years)</label>
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">FY 2022-23</span>
                        <input type="text" name="turnover_fy1" class="form-control" 
                               placeholder="₹ Amount">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">FY 2023-24</span>
                        <input type="text" name="turnover_fy2" class="form-control" 
                               placeholder="₹ Amount">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text">FY 2024-25</span>
                        <input type="text" name="turnover_fy3" class="form-control" 
                               placeholder="₹ Amount">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step Info -->
<div class="alert alert-info d-flex align-items-center" role="alert">
    <i class="bi bi-info-circle me-2 fs-5"></i>
    <div>
        <strong>Step 3 of 4:</strong> Provide your tax declarations and business profile information.
    </div>
</div>