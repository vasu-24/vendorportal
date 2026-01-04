@extends('layouts.app')

@section('title', 'Invoice Approval Flow')

@section('content')
<style>
    .flow-page {
        padding: 20px;
        background: #f8f9fa;
        min-height: calc(100vh - 70px);
    }
    
    .page-header {
        margin-bottom: 20px;
    }
    
    .page-header h1 {
        font-size: 22px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    
    .page-header p {
        color: #666;
        margin: 5px 0 0 0;
        font-size: 13px;
    }
    
    /* Tab Container */
    .tab-container {
        display: flex;
        gap: 20px;
        height: calc(100vh - 160px);
    }
    
    /* Vertical Tabs */
    .vertical-tabs {
        width: 220px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .tab-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 18px;
        background: #fff;
        border: 2px solid #eee;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .tab-btn:hover {
        border-color: #ccc;
        background: #fafafa;
    }
    
    .tab-btn.active {
        border-color: #0d6efd;
        background: #e7f1ff;
    }
    
    .tab-btn.active.normal { border-color: #0d6efd; background: #e7f1ff; }
    .tab-btn.active.travel { border-color: #198754; background: #e6f4ea; }
    .tab-btn.active.adhoc { border-color: #fd7e14; background: #fff4e6; }
    
    .tab-btn .icon-box {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #fff;
    }
    
    .tab-btn .icon-box.normal { background: #0d6efd; }
    .tab-btn .icon-box.travel { background: #198754; }
    .tab-btn .icon-box.adhoc { background: #fd7e14; }
    
    .tab-btn .tab-text {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .tab-btn .tab-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }
    
    .tab-btn .tab-subtitle {
        font-size: 11px;
        color: #666;
    }
    
    /* Tab Content */
    .tab-content-area {
        flex: 1;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .tab-pane {
        display: none;
        height: 100%;
        overflow-y: auto;
    }
    
    .tab-pane.active {
        display: block;
    }
    
    .content-header {
        padding: 18px 25px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .content-header .title-section {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .content-header .icon-box {
        width: 42px;
        height: 42px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #fff;
    }
    
    .content-header .icon-box.normal { background: #0d6efd; }
    .content-header .icon-box.travel { background: #198754; }
    .content-header .icon-box.adhoc { background: #fd7e14; }
    
    .content-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    
    .content-body {
        padding: 25px;
        flex: 1;
    }
    
    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .info-item .label {
        font-size: 11px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-item .value {
        font-size: 13px;
        font-weight: 600;
        color: #333;
    }
    
    .info-item .value.yes { color: #198754; }
    .info-item .value.no { color: #dc3545; }
    
    /* Flow Section */
    .flow-section-title {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .flow-steps {
        display: flex;
        align-items: flex-start;
        gap: 0;
        flex-wrap: wrap;
        padding: 15px 0;
        justify-content: center;
    }
    
    .flow-step {
        display: flex;
        align-items: center;
        gap: 0;
    }
    
    .step-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        min-width: 80px;
    }
    
    .step-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #fff;
        position: relative;
    }
    
    .step-icon.vendor { background: #6c757d; }
    .step-icon.admin { background: #0d6efd; }
    .step-icon.rm { background: #6f42c1; }
    .step-icon.vp { background: #20c997; }
    .step-icon.ceo { background: #fd7e14; }
    .step-icon.finance { background: #198754; }
    .step-icon.zoho { background: #dc3545; }
    .step-icon.paid { background: #333; }
    
    .step-label {
        font-size: 11px;
        font-weight: 600;
        color: #333;
        text-align: center;
    }
    
    .step-sublabel {
        font-size: 9px;
        color: #888;
        text-align: center;
        max-width: 70px;
    }
    
    .step-arrow {
        color: #ccc;
        font-size: 18px;
        margin: 0 3px;
        margin-bottom: 20px;
    }
    
    /* Conditional Step */
    .step-box.conditional { position: relative; }
    
    .step-box.conditional .step-icon {
        border: 2px dashed #fd7e14;
        background: #fff3e0;
        color: #fd7e14;
    }
    
    .conditional-badge {
        position: absolute;
        top: -6px;
        right: -3px;
        background: #fd7e14;
        color: #fff;
        font-size: 8px;
        padding: 2px 5px;
        border-radius: 8px;
        white-space: nowrap;
    }
    
    /* Notes */
    .notes-section {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    
    .note-box {
        flex: 1;
        border-radius: 6px;
        padding: 12px 15px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    
    .note-box.warning {
        background: #fff8e6;
        border: 1px solid #ffe69c;
    }
    
    .note-box.warning i { color: #fd7e14; font-size: 16px; }
    .note-box.warning .text { font-size: 12px; color: #664d03; }
    .note-box.warning .text strong { color: #fd7e14; }
    
    .note-box.info {
        background: #e8f4fd;
        border: 1px solid #b6d4fe;
    }
    
    .note-box.info i { color: #0d6efd; font-size: 16px; }
    .note-box.info .text { font-size: 12px; color: #084298; }
    
    /* Summary Section */
    .summary-section {
        margin-top: 15px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .summary-section h4 {
        font-size: 13px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    
    .summary-item {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 12px 15px;
        text-align: center;
    }
    
    .summary-item .s-label {
        font-size: 10px;
        color: #888;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    
    .summary-item .s-value {
        font-size: 13px;
        font-weight: 600;
        color: #333;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .tab-container {
            flex-direction: column;
            height: auto;
        }
        .vertical-tabs {
            width: 100%;
            flex-direction: row;
            overflow-x: auto;
        }
        .tab-btn {
            min-width: 180px;
        }
        .info-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .notes-section {
            flex-direction: column;
        }
    }
</style>

<div class="flow-page">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="bi bi-diagram-3 me-2"></i>Invoice Approval Flow</h1>
        <p>Understanding the approval process for all invoice types</p>
    </div>
    
    <!-- Tab Container -->
    <div class="tab-container">
        <!-- Vertical Tabs -->
        <div class="vertical-tabs">
            <div class="tab-btn active normal" onclick="showTab('normal')">
                <div class="icon-box normal"><i class="bi bi-file-earmark-text"></i></div>
                <div class="tab-text">
                    <span class="tab-title">Normal Invoice</span>
                    <span class="tab-subtitle">Contract Based</span>
                </div>
            </div>
            <div class="tab-btn travel" onclick="showTab('travel')">
                <div class="icon-box travel"><i class="bi bi-airplane"></i></div>
                <div class="tab-text">
                    <span class="tab-title">Travel Invoice</span>
                    <span class="tab-subtitle">Employee Based</span>
                </div>
            </div>
            <div class="tab-btn adhoc" onclick="showTab('adhoc')">
                <div class="icon-box adhoc"><i class="bi bi-lightning"></i></div>
                <div class="tab-text">
                    <span class="tab-title">ADHOC Invoice</span>
                    <span class="tab-subtitle">SOW Based</span>
                </div>
            </div>
        </div>
        
        <!-- Tab Content -->
        <div class="tab-content-area">
            <!-- NORMAL INVOICE -->
            <div class="tab-pane active" id="normal-tab">
                <div class="content-header">
                    <div class="title-section">
                        <div class="icon-box normal"><i class="bi bi-file-earmark-text"></i></div>
                        <h3>Normal Invoice</h3>
                    </div>
                    <span class="badge bg-primary">Contract Based</span>
                </div>
                <div class="content-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Source</span>
                            <span class="value">Normal Contract</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Timesheet</span>
                            <span class="value yes"><i class="bi bi-check-circle me-1"></i>Optional</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Tag Source</span>
                            <span class="value">Contract Items</span>
                        </div>
                        <div class="info-item">
                            <span class="label">CEO Trigger</span>
                            <span class="value">Exceeds Contract Value</span>
                        </div>
                    </div>
                    
                    <div class="flow-section-title"><i class="bi bi-arrow-right-circle"></i> Approval Flow</div>
                    
                    <div class="flow-steps">
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon vendor"><i class="bi bi-building"></i></div>
                                <span class="step-label">Vendor</span>
                                <span class="step-sublabel">Submit</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon admin"><i class="bi bi-play-circle"></i></div>
                                <span class="step-label">Admin</span>
                                <span class="step-sublabel">Start Review</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon rm"><i class="bi bi-person-badge"></i></div>
                                <span class="step-label">RM</span>
                                <span class="step-sublabel">Approve</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon vp"><i class="bi bi-person-check"></i></div>
                                <span class="step-label">COO</span>
                                <span class="step-sublabel">Approve</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box conditional">
                                <span class="conditional-badge">If Exceeds</span>
                                <div class="step-icon ceo"><i class="bi bi-star"></i></div>
                                <span class="step-label">CEO</span>
                                <span class="step-sublabel">Approve</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon finance"><i class="bi bi-cash-stack"></i></div>
                                <span class="step-label">Finance</span>
                                <span class="step-sublabel">Final</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon zoho"><i class="bi bi-cloud-upload"></i></div>
                                <span class="step-label">Zoho</span>
                                <span class="step-sublabel">Sync</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon paid"><i class="bi bi-check2-all"></i></div>
                                <span class="step-label">Paid</span>
                                <span class="step-sublabel">Done</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="notes-section">
                        <div class="note-box warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <div class="text"><strong>CEO Approval:</strong> Required when total invoiced exceeds <strong>Contract Value</strong></div>
                        </div>
                        <div class="note-box info">
                            <i class="bi bi-info-circle"></i>
                            <div class="text"><strong>Multi-RM:</strong> If items have different tags, each RM approves their items</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- TRAVEL INVOICE -->
            <div class="tab-pane" id="travel-tab">
                <div class="content-header">
                    <div class="title-section">
                        <div class="icon-box travel"><i class="bi bi-airplane"></i></div>
                        <h3>Travel Invoice</h3>
                    </div>
                    <span class="badge bg-success">Employee Based</span>
                </div>
                <div class="content-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Source</span>
                            <span class="value">Travel Employee</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Contract</span>
                            <span class="value no"><i class="bi bi-x-circle me-1"></i>Not Required</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Tag Source</span>
                            <span class="value">Employee → Project</span>
                        </div>
                        <div class="info-item">
                            <span class="label">CEO Trigger</span>
                            <span class="value">COO No Response (7 Days)</span>
                        </div>
                    </div>
                    
                    <div class="flow-section-title"><i class="bi bi-arrow-right-circle"></i> Approval Flow</div>
                    
                    <div class="flow-steps">
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon vendor"><i class="bi bi-building"></i></div>
                                <span class="step-label">Vendor</span>
                                <span class="step-sublabel">Submit Batch</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon admin"><i class="bi bi-play-circle"></i></div>
                                <span class="step-label">Admin</span>
                                <span class="step-sublabel">Start Review</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon rm"><i class="bi bi-person-badge"></i></div>
                                <span class="step-label">RM</span>
                                <span class="step-sublabel">Approve</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon vp"><i class="bi bi-person-check"></i></div>
                                <span class="step-label">COO</span>
                                <span class="step-sublabel">Approve</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box conditional">
                                <span class="conditional-badge">7 Days</span>
                                <div class="step-icon ceo"><i class="bi bi-star"></i></div>
                                <span class="step-label">CEO</span>
                                <span class="step-sublabel">Approve</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon finance"><i class="bi bi-cash-stack"></i></div>
                                <span class="step-label">Finance</span>
                                <span class="step-sublabel">Final</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon zoho"><i class="bi bi-cloud-upload"></i></div>
                                <span class="step-label">Zoho</span>
                                <span class="step-sublabel">Sync</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon paid"><i class="bi bi-check2-all"></i></div>
                                <span class="step-label">Paid</span>
                                <span class="step-sublabel">Done</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="notes-section">
                        <div class="note-box warning">
                            <i class="bi bi-clock-history"></i>
                            <div class="text"><strong>CEO Approval:</strong> Required when COO does not respond within <strong>7 days</strong></div>
                        </div>
                        <div class="note-box info">
                            <i class="bi bi-info-circle"></i>
                            <div class="text"><strong>Batch Support:</strong> Multiple invoices can be submitted in one batch</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ADHOC INVOICE -->
            <div class="tab-pane" id="adhoc-tab">
                <div class="content-header">
                    <div class="title-section">
                        <div class="icon-box adhoc"><i class="bi bi-lightning"></i></div>
                        <h3>ADHOC Invoice</h3>
                    </div>
                    <span class="badge bg-warning text-dark">SOW Based</span>
                </div>
                <div class="content-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Source</span>
                            <span class="value">ADHOC Contract</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Timesheet</span>
                            <span class="value no"><i class="bi bi-x-circle me-1"></i>Not Required</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Tag Source</span>
                            <span class="value">Contract Items</span>
                        </div>
                        <div class="info-item">
                            <span class="label">CEO Trigger</span>
                            <span class="value">Exceeds SOW Value</span>
                        </div>
                    </div>
                    
                    <div class="flow-section-title"><i class="bi bi-arrow-right-circle"></i> Approval Flow</div>
                    
                    <div class="flow-steps">
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon vendor"><i class="bi bi-building"></i></div>
                                <span class="step-label">Vendor</span>
                                <span class="step-sublabel">Submit</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon admin"><i class="bi bi-play-circle"></i></div>
                                <span class="step-label">Admin</span>
                                <span class="step-sublabel">Start Review</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon rm"><i class="bi bi-person-badge"></i></div>
                                <span class="step-label">RM</span>
                                <span class="step-sublabel">Approve</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon vp"><i class="bi bi-person-check"></i></div>
                                <span class="step-label">COO</span>
                                <span class="step-sublabel">Approve</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box conditional">
                                <span class="conditional-badge">If Exceeds</span>
                                <div class="step-icon ceo"><i class="bi bi-star"></i></div>
                                <span class="step-label">CEO</span>
                                <span class="step-sublabel">Approve</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon finance"><i class="bi bi-cash-stack"></i></div>
                                <span class="step-label">Finance</span>
                                <span class="step-sublabel">Final</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon zoho"><i class="bi bi-cloud-upload"></i></div>
                                <span class="step-label">Zoho</span>
                                <span class="step-sublabel">Sync</span>
                            </div>
                            <i class="bi bi-arrow-right step-arrow"></i>
                        </div>
                        <div class="flow-step">
                            <div class="step-box">
                                <div class="step-icon paid"><i class="bi bi-check2-all"></i></div>
                                <span class="step-label">Paid</span>
                                <span class="step-sublabel">Done</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="notes-section">
                        <div class="note-box warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <div class="text"><strong>CEO Approval:</strong> Required when total invoiced exceeds <strong>SOW Value</strong></div>
                        </div>
                        <div class="note-box info">
                            <i class="bi bi-info-circle"></i>
                            <div class="text"><strong>Multi-RM:</strong> If items have different tags, each RM approves their items</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Remove active from all tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Remove active from all panes
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    
    // Add active to clicked tab
    document.querySelector('.tab-btn.' + tabName).classList.add('active');
    
    // Show corresponding pane
    document.getElementById(tabName + '-tab').classList.add('active');
}
</script>
@endsection