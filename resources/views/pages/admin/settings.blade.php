@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<style>
    .settings-container { max-width: 1200px; margin: 0 auto; }
    
    .settings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .settings-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .settings-title i { color: #6b7280; }
    
    /* Tabs */
    .settings-tabs {
        display: flex;
        gap: 4px;
        background: #f3f4f6;
        padding: 4px;
        border-radius: 10px;
        margin-bottom: 24px;
    }
    .settings-tab {
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        background: transparent;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .settings-tab:hover { color: #374151; background: #e5e7eb; }
    .settings-tab.active {
        background: white;
        color: #2563eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .settings-tab i { font-size: 16px; }
    
    /* Tab Content */
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    /* Cards */
    .settings-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .settings-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .settings-card-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    .settings-card-header i {
        font-size: 20px;
        color: #2563eb;
    }
    .settings-card-body { padding: 20px; }
    
    /* Form */
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    .form-row.single { grid-template-columns: 1fr; }
    .form-row.triple { grid-template-columns: repeat(3, 1fr); }
    
    .form-group { margin-bottom: 0; }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }
    .form-group label .required { color: #ef4444; }
    .form-group .hint {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 4px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    .form-control:disabled {
        background: #f9fafb;
        color: #6b7280;
    }
    
    /* Toggle Switch */
    .toggle-group {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 12px;
    }
    .toggle-group:last-child { margin-bottom: 0; }
    .toggle-label {
        font-size: 14px;
        color: #374151;
    }
    .toggle-label small {
        display: block;
        font-size: 12px;
        color: #9ca3af;
        margin-top: 2px;
    }
    
    .toggle-switch {
        position: relative;
        width: 48px;
        height: 26px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #d1d5db;
        transition: 0.3s;
        border-radius: 26px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider { background-color: #2563eb; }
    input:checked + .toggle-slider:before { transform: translateX(22px); }
    
    /* Zoho Status */
    .zoho-status {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .zoho-status.connected { background: #dcfce7; color: #166534; }
    .zoho-status.disconnected { background: #fee2e2; color: #991b1b; }
    .zoho-status i { font-size: 24px; }
    .zoho-status-text h4 { margin: 0; font-size: 14px; font-weight: 600; }
    .zoho-status-text p { margin: 0; font-size: 12px; opacity: 0.8; }
    
    /* TDS Warning Preview */
    .tds-preview {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #f59e0b;
        border-radius: 10px;
        padding: 16px;
        margin-top: 20px;
    }
    .tds-preview-title {
        font-size: 12px;
        font-weight: 600;
        color: #92400e;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .tds-preview-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .tds-preview-content i { font-size: 32px; color: #d97706; }
    .tds-preview-text h4 { margin: 0; font-size: 15px; font-weight: 600; color: #92400e; }
    .tds-preview-text p { margin: 4px 0 0; font-size: 13px; color: #a16207; }
    
    /* Buttons */
    .btn-save {
        padding: 12px 24px;
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-save:hover { background: #1d4ed8; }
    .btn-save:disabled { background: #9ca3af; cursor: not-allowed; }
    
    .btn-outline {
        padding: 10px 20px;
        background: white;
        color: #374151;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-outline:hover { background: #f9fafb; border-color: #9ca3af; }
    
    .btn-danger {
        padding: 10px 20px;
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
    }
    .btn-danger:hover { background: #fecaca; }
    
    .btn-success {
        padding: 10px 20px;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
    }
    .btn-success:hover { background: #bbf7d0; }
    
    /* Actions Footer */
    .settings-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 20px;
        border-top: 1px solid #f3f4f6;
        margin-top: 20px;
    }
    
    /* Logo Upload */
    .logo-upload {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    .logo-preview {
        width: 100px;
        height: 100px;
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9fafb;
        overflow: hidden;
    }
    .logo-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .logo-preview i { font-size: 32px; color: #9ca3af; }
    .logo-upload-btn {
        padding: 10px 16px;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
    }
    .logo-upload-btn:hover { background: #e5e7eb; }
    
    /* Test Connection */
    .test-result {
        padding: 12px 16px;
        border-radius: 8px;
        margin-top: 12px;
        font-size: 13px;
        display: none;
    }
    .test-result.success { background: #dcfce7; color: #166534; display: block; }
    .test-result.error { background: #fee2e2; color: #991b1b; display: block; }
    
    /* Info Box */
    .info-box {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 13px;
        color: #1e40af;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 20px;
    }
    .info-box i { font-size: 18px; flex-shrink: 0; margin-top: 2px; }
</style>

<div class="container-fluid py-4">
    <div class="settings-container">
        
        <!-- Header -->
        <div class="settings-header">
            <h1 class="settings-title">
                <i class="bi bi-gear"></i>
                Settings
            </h1>
            <button class="btn-save" onclick="saveAllSettings()">
                <i class="bi bi-check-lg"></i>
                Save All Settings
            </button>
        </div>
        
        <!-- Tabs -->
        <div class="settings-tabs">
            <button class="settings-tab active" onclick="switchTab('zoho')">
                <i class="bi bi-cloud"></i>
                Zoho
            </button>
            <button class="settings-tab" onclick="switchTab('email')">
                <i class="bi bi-envelope"></i>
                Email
            </button>
            <button class="settings-tab" onclick="switchTab('tds')">
                <i class="bi bi-calendar-check"></i>
                TDS Config
            </button>
        </div>
        
        <!-- ============================================= -->
        <!-- TAB 1: ZOHO SETTINGS (Saves to .env) -->
        <!-- ============================================= -->
        <div id="tab-zoho" class="tab-content active">
            <div class="settings-card">
                <div class="settings-card-header">
                    <i class="bi bi-cloud"></i>
                    <h3>Zoho Books Integration</h3>
                </div>
                <div class="settings-card-body">
                    
                    <!-- Connection Status -->
                    <div class="zoho-status disconnected" id="zohoStatus">
                        <i class="bi bi-x-circle-fill"></i>
                        <div class="zoho-status-text">
                            <h4>Not Connected</h4>
                            <p>Click "Connect to Zoho" to authorize</p>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Client ID <span class="required">*</span></label>
                            <input type="text" class="form-control" id="zoho_client_id" value="{{ config('zoho.client_id') }}" placeholder="1000.XXXXXX">
                        </div>
                        <div class="form-group">
                            <label>Client Secret <span class="required">*</span></label>
                            <input type="password" class="form-control" id="zoho_client_secret" value="{{ config('zoho.client_secret') }}" placeholder="Enter new secret to change">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Redirect URI (for Zoho API Console)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ url('/zoho/callback') }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ url('/zoho/callback') }}')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <p class="hint">Copy this URL and add it in Zoho API Console</p>
                        </div>
                        <div class="form-group">
                            <label>Organization ID</label>
                            <input type="text" class="form-control" id="zoho_organization_id" value="" readonly>
                            <p class="hint">Auto-fetched after connection</p>
                        </div>
                    </div>
                    
                    <div class="settings-actions">
                        <button type="button" class="btn-danger" onclick="disconnectZoho()" id="btnDisconnect" style="display:none">
                            <i class="bi bi-x-circle me-1"></i> Disconnect
                        </button>
                        <button type="button" class="btn-success" onclick="connectZoho()" id="btnConnect">
                            <i class="bi bi-link-45deg me-1"></i> Connect to Zoho
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ============================================= -->
        <!-- TAB 2: EMAIL SETTINGS (Saves to .env) -->
        <!-- ============================================= -->
        <div id="tab-email" class="tab-content">
            <div class="settings-card">
                <div class="settings-card-header">
                    <i class="bi bi-envelope"></i>
                    <h3>Email Configuration (SMTP)</h3>
                </div>
                <div class="settings-card-body">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>SMTP Host <span class="required">*</span></label>
                            <input type="text" class="form-control" id="smtp_host" value="{{ config('mail.mailers.smtp.host') }}" placeholder="smtp.gmail.com">
                        </div>
                        <div class="form-group">
                            <label>SMTP Port <span class="required">*</span></label>
                            <input type="number" class="form-control" id="smtp_port" value="{{ config('mail.mailers.smtp.port') }}" placeholder="587">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" id="smtp_username" value="{{ config('mail.mailers.smtp.username') }}" placeholder="your-email@gmail.com">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" id="smtp_password" placeholder="Enter new password to change">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Encryption</label>
                            <select class="form-control" id="smtp_encryption">
                                <option value="tls" {{ config('mail.mailers.smtp.encryption') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ config('mail.mailers.smtp.encryption') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ !config('mail.mailers.smtp.encryption') ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>From Name</label>
                            <input type="text" class="form-control" id="mail_from_name" value="{{ config('mail.from.name') }}" placeholder="Vendor Portal">
                        </div>
                    </div>
                    
                    <div class="form-row single">
                        <div class="form-group">
                            <label>From Email Address</label>
                            <input type="email" class="form-control" id="mail_from_address" value="{{ config('mail.from.address') }}" placeholder="noreply@company.com">
                        </div>
                    </div>
                    
                    <div id="emailTestResult" class="test-result"></div>
                    
                    <div class="settings-actions">
                        <button type="button" class="btn-outline" onclick="testEmailConnection()">
                            <i class="bi bi-send me-1"></i> Send Test Email
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ============================================= -->
        <!-- TAB 4: TDS CONFIGURATION -->
        <!-- ============================================= -->
        <div id="tab-tds" class="tab-content">
            <div class="settings-card">
                <div class="settings-card-header">
                    <i class="bi bi-calendar-check"></i>
                    <h3>TDS Payment Configuration</h3>
                </div>
                <div class="settings-card-body">
                    
                    <div class="info-box">
                        <i class="bi bi-info-circle"></i>
                        <div>
                            <strong>TDS Payment Rules:</strong><br>
                            • Regular months: TDS must be paid by <strong>7th of next month</strong><br>
                            • March (FY End): TDS must be paid by <strong>April 30th</strong>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Monthly TDS Due Date <span class="required">*</span></label>
                            <select class="form-control" id="tds_monthly_due_date">
                                @for($i = 1; $i <= 15; $i++)
                                    <option value="{{ $i }}" {{ $i == 7 ? 'selected' : '' }}>{{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }} of month</option>
                                @endfor
                            </select>
                            <p class="hint">TDS for previous month must be paid by this date</p>
                        </div>
                        <div class="form-group">
                            <label>March FY End Due Date <span class="required">*</span></label>
                            <select class="form-control" id="tds_march_due_date">
                                <option value="30">April 30th</option>
                                <option value="15">April 15th</option>
                                <option value="7">April 7th</option>
                            </select>
                            <p class="hint">TDS for March (FY End) must be paid by this date</p>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Warning Days Before Due <span class="required">*</span></label>
                            <select class="form-control" id="tds_warning_days">
                                <option value="1">1 day before</option>
                                <option value="2">2 days before</option>
                                <option value="3" selected>3 days before</option>
                                <option value="5">5 days before</option>
                                <option value="7">7 days before</option>
                            </select>
                            <p class="hint">Show warning this many days before due date</p>
                        </div>
                        <div class="form-group">
                            <label>Default TDS Rate (%)</label>
                            <input type="number" class="form-control" id="tds_default_rate" value="5" min="0" max="100" step="0.1">
                            <p class="hint">Default TDS percentage for new invoices</p>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3" style="font-size: 14px; font-weight: 600; color: #374151;">
                        <i class="bi bi-bell me-2"></i>Notification Settings
                    </h5>
                    
                    <div class="toggle-group">
                        <div class="toggle-label">
                            Show TDS Warning on Dashboard
                            <small>Display warning banner when TDS due date is approaching</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="tds_show_dashboard_warning" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-label">
                            Show Warning on Invoice Pages
                            <small>Display warning on invoice list and detail pages</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="tds_show_invoice_warning" checked>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="toggle-group">
                        <div class="toggle-label">
                            Send Email Reminder
                            <small>Send email to finance team before TDS due date</small>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="tds_send_email_reminder">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="form-row single mt-3" id="tdsEmailRecipients" style="display: none;">
                        <div class="form-group">
                            <label>Reminder Email Recipients</label>
                            <input type="text" class="form-control" id="tds_reminder_emails" placeholder="finance@company.com, accounts@company.com">
                            <p class="hint">Comma separated email addresses</p>
                        </div>
                    </div>
                    
                    <!-- TDS Warning Preview -->
                    <div class="tds-preview">
                        <div class="tds-preview-title">Preview: How warning will appear</div>
                        <div class="tds-preview-content">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div class="tds-preview-text">
                                <h4>⚠️ TDS Payment Due Soon!</h4>
                                <p id="tdsPreviewText">TDS for December 2025 is due by January 7th, 2026. 3 days remaining.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
const API_BASE = '/api/admin/settings';

// =====================================================
// TAB SWITCHING
// =====================================================
function switchTab(tabName) {
    // Remove active from all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Add active to clicked tab
    event.target.closest('.settings-tab').classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

// =====================================================
// LOAD SETTINGS
// =====================================================
function loadSettings() {
    axios.get(API_BASE)
        .then(res => {
            if (res.data.success) {
                populateForm(res.data.data);
            }
        })
        .catch(err => {
            console.error('Failed to load settings', err);
        });
}

function populateForm(settings) {
    // Zoho Organization ID
    $('#zoho_organization_id').val(settings.zoho_organization_id || 'Not connected');
    
    // Update Zoho status
    updateZohoStatus(settings.zoho_connected || false);
    
    // TDS (from database)
    $('#tds_monthly_due_date').val(settings.tds_monthly_due_date || 7);
    $('#tds_march_due_date').val(settings.tds_march_due_date || 30);
    $('#tds_warning_days').val(settings.tds_warning_days || 3);
    $('#tds_default_rate').val(settings.tds_default_rate || 5);
    $('#tds_show_dashboard_warning').prop('checked', settings.tds_show_dashboard_warning !== '0');
    $('#tds_show_invoice_warning').prop('checked', settings.tds_show_invoice_warning !== '0');
    $('#tds_send_email_reminder').prop('checked', settings.tds_send_email_reminder === '1');
    $('#tds_reminder_emails').val(settings.tds_reminder_emails || '');
    
    toggleTdsEmailRecipients();
}

// =====================================================
// SAVE SETTINGS
// =====================================================
function saveAllSettings() {
    const btn = $('.btn-save');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
    
    const data = {
        // Zoho (saves to .env)
        zoho_client_id: $('#zoho_client_id').val(),
        zoho_client_secret: $('#zoho_client_secret').val(),
        
        // Email (saves to .env)
        smtp_host: $('#smtp_host').val(),
        smtp_port: $('#smtp_port').val(),
        smtp_username: $('#smtp_username').val(),
        smtp_password: $('#smtp_password').val(),
        smtp_encryption: $('#smtp_encryption').val(),
        mail_from_name: $('#mail_from_name').val(),
        mail_from_address: $('#mail_from_address').val(),
        
        // TDS (saves to database)
        tds_monthly_due_date: $('#tds_monthly_due_date').val(),
        tds_march_due_date: $('#tds_march_due_date').val(),
        tds_warning_days: $('#tds_warning_days').val(),
        tds_default_rate: $('#tds_default_rate').val(),
        tds_show_dashboard_warning: $('#tds_show_dashboard_warning').is(':checked') ? 1 : 0,
        tds_show_invoice_warning: $('#tds_show_invoice_warning').is(':checked') ? 1 : 0,
        tds_send_email_reminder: $('#tds_send_email_reminder').is(':checked') ? 1 : 0,
        tds_reminder_emails: $('#tds_reminder_emails').val(),
    };
    
    axios.post(API_BASE, data)
        .then(res => {
            if (res.data.success) {
                Toast.success('Settings saved successfully!');
            } else {
                Toast.error(res.data.message || 'Failed to save settings');
            }
        })
        .catch(err => {
            Toast.error(err.response?.data?.message || 'Failed to save settings');
        })
        .finally(() => {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Save All Settings');
        });
}

// =====================================================
// ZOHO FUNCTIONS
// =====================================================
function updateZohoStatus(connected) {
    const status = $('#zohoStatus');
    if (connected) {
        status.removeClass('disconnected').addClass('connected');
        status.html(`
            <i class="bi bi-check-circle-fill"></i>
            <div class="zoho-status-text">
                <h4>Connected</h4>
                <p>Zoho Books integration is active</p>
            </div>
        `);
        $('#btnConnect').hide();
        $('#btnDisconnect').show();
    } else {
        status.removeClass('connected').addClass('disconnected');
        status.html(`
            <i class="bi bi-x-circle-fill"></i>
            <div class="zoho-status-text">
                <h4>Not Connected</h4>
                <p>Click "Connect to Zoho" to authorize</p>
            </div>
        `);
        $('#btnConnect').show();
        $('#btnDisconnect').hide();
    }
}

function connectZoho() {
    const clientId = $('#zoho_client_id').val();
    const clientSecret = $('#zoho_client_secret').val();
    
    if (!clientId || !clientSecret) {
        Toast.error('Please enter Client ID and Client Secret first');
        return;
    }
    
    // Save first, then redirect to OAuth
    saveAllSettings();
    
    setTimeout(() => {
        window.location.href = '/settings/zoho/connect';
    }, 1000);
}

function disconnectZoho() {
    if (!confirm('Are you sure you want to disconnect Zoho Books?')) return;
    
    axios.post('/settings/zoho/disconnect')
        .then(res => {
            if (res.data.success) {
                Toast.success('Zoho disconnected');
                updateZohoStatus(false);
            }
        })
        .catch(err => {
            Toast.error('Failed to disconnect');
        });
}

// =====================================================
// EMAIL TEST
// =====================================================
function testEmailConnection() {
    const result = $('#emailTestResult');
    result.removeClass('success error').hide();
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
    
  axios.post('/api/admin/settings/test-email', {
        host: $('#smtp_host').val(),
        port: $('#smtp_port').val(),
        username: $('#smtp_username').val(),
        password: $('#smtp_password').val(),
        encryption: $('#smtp_encryption').val(),
        from_name: $('#mail_from_name').val(),
        from_address: $('#mail_from_address').val(),
    })
        .then(res => {
            if (res.data.success) {
                result.addClass('success').html('<i class="bi bi-check-circle me-2"></i>' + res.data.message).show();
            } else {
                result.addClass('error').html('<i class="bi bi-x-circle me-2"></i>' + res.data.message).show();
            }
        })
        .catch(err => {
            result.addClass('error').html('<i class="bi bi-x-circle me-2"></i>' + (err.response?.data?.message || 'Test failed')).show();
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-1"></i> Send Test Email';
        });
}

// =====================================================
// TDS FUNCTIONS
// =====================================================
function toggleTdsEmailRecipients() {
    if ($('#tds_send_email_reminder').is(':checked')) {
        $('#tdsEmailRecipients').slideDown();
    } else {
        $('#tdsEmailRecipients').slideUp();
    }
}

$('#tds_send_email_reminder').change(toggleTdsEmailRecipients);

// Update TDS preview
function updateTdsPreview() {
    const dueDate = $('#tds_monthly_due_date').val();
    const warningDays = $('#tds_warning_days').val();
    
    const now = new Date();
    const month = now.toLocaleString('default', { month: 'long' });
    const year = now.getFullYear();
    const nextMonth = new Date(now.getFullYear(), now.getMonth() + 1, 1);
    const nextMonthName = nextMonth.toLocaleString('default', { month: 'long' });
    
    $('#tdsPreviewText').text(
        `TDS for ${month} ${year} is due by ${nextMonthName} ${dueDate}th, ${nextMonth.getFullYear()}. ${warningDays} days remaining.`
    );
}

$('#tds_monthly_due_date, #tds_warning_days').change(updateTdsPreview);

// =====================================================
// COPY TO CLIPBOARD
// =====================================================
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        Toast.success('Copied to clipboard!');
    }).catch(() => {
        // Fallback
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        Toast.success('Copied to clipboard!');
    });
}

// =====================================================
// INIT
// =====================================================
$(document).ready(function() {
    loadSettings();
    updateTdsPreview();
    
    // Check for tab query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab && ['zoho', 'email', 'tds'].includes(tab)) {
        switchTabByName(tab);
    }
});

// Switch tab by name (for URL parameter)
function switchTabByName(tabName) {
    document.querySelectorAll('.settings-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Find and activate the correct tab button
    document.querySelectorAll('.settings-tab').forEach(tab => {
        if (tab.getAttribute('onclick')?.includes(tabName)) {
            tab.classList.add('active');
        }
    });
    
    document.getElementById('tab-' + tabName).classList.add('active');
}
</script>
@endpush