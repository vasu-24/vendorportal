@extends('layouts.app')

@section('title', 'Zoho Integration')

@section('page-title', 'Zoho Integration')

@section('content')
<style>
    /* Integration Card */
    .integration-card {
        background: #fff;
        border: 1px solid #D8DEE9;
        border-radius: 12px;
        padding: 32px;
        max-width: 600px;
        margin: 0 auto;
        box-shadow: 0 2px 12px rgba(23, 64, 129, 0.07);
    }

    .integration-header {
        text-align: center;
        margin-bottom: 32px;
    }

    .zoho-logo {
        width: 80px;
        height: 80px;
        background: #3B82F6;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 32px;
        color: #fff;
    }

    .integration-title {
        font-size: 22px;
        font-weight: 600;
        color: #222222;
        margin: 0 0 8px;
    }

    .integration-desc {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 500;
        margin-top: 16px;
    }

    .status-badge.connected {
        background: #dcfce7;
        color: #15803d;
    }

    .status-badge.connected::before {
        content: '';
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
    }

    .status-badge.disconnected {
        background: #fee2e2;
        color: #dc2626;
    }

    .status-badge.disconnected::before {
        content: '';
        width: 8px;
        height: 8px;
        background: #ef4444;
        border-radius: 50%;
    }

    /* Connect Section */
    .connect-section {
        text-align: center;
        padding: 24px 0;
        border-top: 1px solid #D8DEE9;
    }

    .btn-connect {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 14px 32px;
        background: #3B82F6;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-connect:hover {
        background: #2563EB;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
    }

    .btn-disconnect {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-disconnect:hover {
        background: #fecaca;
    }

    .test-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 12px;
        color: #374151;
        cursor: pointer;
        margin-top: 12px;
        transition: all 0.2s ease;
    }

    .test-btn:hover {
        background: #e5e7eb;
    }

    /* Organization Section */
    .org-section {
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid #D8DEE9;
        text-align: left;
    }

    .org-label {
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
        margin-bottom: 8px;
        display: block;
    }

    .org-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #D8DEE9;
        border-radius: 8px;
        font-size: 14px;
        color: #222222;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .org-select:focus {
        outline: none;
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    /* Features List */
    .features-list {
        margin-top: 32px;
        padding: 20px;
        background: #f8fafc;
        border-radius: 10px;
    }

    .features-title {
        font-size: 14px;
        font-weight: 600;
        color: #222222;
        margin: 0 0 16px;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        font-size: 13px;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
    }

    .feature-item:last-child {
        border-bottom: none;
    }

    .feature-item i {
        font-size: 16px;
        color: #22c55e;
    }

    /* Alerts */
    .alert-box {
        padding: 14px 18px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-box.success {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .alert-box.error {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
</style>

<div class="integration-card">
    
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert-box success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-box error">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="integration-header">
        <div class="zoho-logo">
            <i class="bi bi-book"></i>
        </div>
        <h1 class="integration-title">Zoho Books Integration</h1>
        <p class="integration-desc">Connect your Zoho Books account to automatically sync vendors</p>
        
        @if($isConnected)
            <span class="status-badge connected">Connected</span>
        @else
            <span class="status-badge disconnected">Not Connected</span>
        @endif
    </div>

    {{-- Connect/Disconnect Section --}}
    <div class="connect-section">
        @if($isConnected)
            {{-- Organization Selector --}}
            @if(!empty($organizations))
                <div class="org-section" style="margin-top: 0; padding-top: 0; border-top: none;">
                    <label class="org-label">Select Organization</label>
                    <select class="org-select" id="orgSelect">
                        @foreach($organizations as $org)
                            <option value="{{ $org['organization_id'] }}" 
                                {{ $currentOrgId == $org['organization_id'] ? 'selected' : '' }}>
                                {{ $org['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="button" class="test-btn" id="testBtn">
                <i class="bi bi-wifi"></i> Test Connection
            </button>

            <div style="margin-top: 24px;">
                <form action="{{ route('zoho.disconnect') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-disconnect" onclick="return confirm('Are you sure you want to disconnect Zoho Books?')">
                        <i class="bi bi-x-circle"></i> Disconnect
                    </button>
                </form>
            </div>
        @else
            <a href="{{ route('zoho.connect') }}" class="btn-connect">
                <i class="bi bi-link-45deg"></i>
                Connect to Zoho Books
            </a>
        @endif
    </div>

    {{-- Features --}}
    <div class="features-list">
        <h3 class="features-title">What this integration does:</h3>
        <div class="feature-item">
            <i class="bi bi-check-circle-fill"></i>
            <span>Auto-create vendors in Zoho Books when approved</span>
        </div>
        <div class="feature-item">
            <i class="bi bi-check-circle-fill"></i>
            <span>Sync vendor details (Name, Email, GSTIN, PAN, etc.)</span>
        </div>
        <div class="feature-item">
            <i class="bi bi-check-circle-fill"></i>
            <span>Sync bank account details</span>
        </div>
        <div class="feature-item">
            <i class="bi bi-check-circle-fill"></i>
            <span>Sync billing address</span>
        </div>
        <div class="feature-item">
            <i class="bi bi-check-circle-fill"></i>
            <span>Auto-update vendor info when changed</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Organization Select Change
    const orgSelect = document.getElementById('orgSelect');
    if (orgSelect) {
        orgSelect.addEventListener('change', function() {
            const orgId = this.value;
            
            axios.post('{{ route('zoho.organization') }}', {
                organization_id: orgId
            })
            .then(response => {
                alert('Organization updated successfully!');
            })
            .catch(error => {
                alert('Failed to update organization');
            });
        });
    }

    // Test Connection
    const testBtn = document.getElementById('testBtn');
    if (testBtn) {
        testBtn.addEventListener('click', function() {
            const btn = this;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Testing...';
            btn.disabled = true;

            axios.get('{{ route('zoho.test') }}')
            .then(response => {
                alert('Connection successful!');
            })
            .catch(error => {
                alert('Connection failed: ' + (error.response?.data?.message || 'Unknown error'));
            })
            .finally(() => {
                btn.innerHTML = '<i class="bi bi-wifi"></i> Test Connection';
                btn.disabled = false;
            });
        });
    }
</script>
@endpush