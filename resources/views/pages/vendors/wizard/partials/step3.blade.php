{{-- Step 3: Tax & Regulatory + Business Profile --}}

<div class="mb-3">
    <h6 class="fw-semibold mb-1">Tax & Business Information</h6>
    <small class="text-muted">Step 3 of 4</small>
</div>

<!-- Tax & Regulatory -->
<div class="form-section mb-3">
    <div class="section-title">Tax & Regulatory Declarations</div>
    
    <div class="row g-2">
        <div class="col-md-6">
            <label class="form-label form-label-sm">Tax Residency {{-- <span class="text-danger">*</span> --}}</label>
            <select name="tax_residency" class="form-select form-select-sm" {{-- required --}}>
                <option value="">Select</option>
                <option value="India">India</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">GST Reverse Charge Mechanism? {{-- <span class="text-danger">*</span> --}}</label>
            <select name="gst_reverse_charge" class="form-select form-select-sm" {{-- required --}}>
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">SEZ Status {{-- <span class="text-danger">*</span> --}}</label>
            <select name="sez_status" class="form-select form-select-sm" {{-- required --}}>
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">TDS Exemption Certificate <small class="text-muted">(if any)</small></label>
            <input type="file" name="tds_exemption_certificate" class="form-control form-control-sm" id="tdsExemption" accept=".pdf,.jpg,.jpeg,.png">
            <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
        </div>
    </div>
</div>

<!-- Business Profile -->
<div class="form-section mb-3">
    <div class="section-title">Business Profile</div>
    
    <div class="row g-2">
        <div class="col-12">
            <label class="form-label form-label-sm">Core Business Activities / Products / Services {{-- <span class="text-danger">*</span> --}}</label>
            <textarea name="core_activities" class="form-control form-control-sm" rows="2" placeholder="Describe your business activities..." {{-- required --}}></textarea>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Number of Employees {{-- <span class="text-danger">*</span> --}}</label>
            <select name="employee_count" class="form-select form-select-sm" {{-- required --}}>
                <option value="">Select</option>
                <option value="1-10">1 - 10</option>
                <option value="11-50">11 - 50</option>
                <option value="51-100">51 - 100</option>
                <option value="101-500">101 - 500</option>
                <option value="501-1000">501 - 1000</option>
                <option value="1000+">1000+</option>
            </select>
        </div>
        
        <div class="col-md-6">
            <label class="form-label form-label-sm">Credit Period Offered {{-- <span class="text-danger">*</span> --}}</label>
            <select name="credit_period" class="form-select form-select-sm" {{-- required --}}>
                <option value="">Select</option>
                <option value="Immediate">Immediate</option>
                <option value="15 Days">15 Days</option>
                <option value="30 Days">30 Days</option>
                <option value="45 Days">45 Days</option>
                <option value="60 Days">60 Days</option>
                <option value="90 Days">90 Days</option>
            </select>
        </div>
        
        <div class="col-12">
            <label class="form-label form-label-sm">Annual Turnover (Last 3 Financial Years) {{-- <span class="text-danger">*</span> --}}</label>
            <div class="row g-2">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">FY 22-23</span>
                        <input type="text" name="turnover_fy1" class="form-control form-control-sm" placeholder="₹ Amount" {{-- required --}}>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">FY 23-24</span>
                        <input type="text" name="turnover_fy2" class="form-control form-control-sm" placeholder="₹ Amount" {{-- required --}}>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">FY 24-25</span>
                        <input type="text" name="turnover_fy3" class="form-control form-control-sm" placeholder="₹ Amount" {{-- required --}}>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>