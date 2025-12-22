@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<style>
    /* Zoho Status Badge */
    .zoho-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .zoho-connected { background: #d1fae5; color: #065f46; }
    .zoho-disconnected { background: #fee2e2; color: #991b1b; }

    .zoho-status .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .zoho-connected .dot { background: #10b981; }
    .zoho-disconnected .dot { background: #ef4444; }

    /* Summary Cards - Compact */
    .summary-card {
        background: var(--white);
        border: 1.5px solid var(--border-grey);
        border-radius: 10px;
        padding: 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(23,64,129,0.1);
    }

    .summary-icon {
        font-size: 1.25rem;
        color: var(--primary-blue);
    }

    .summary-count {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary-blue);
        line-height: 1.2;
    }

    .summary-label {
        font-size: 0.7rem;
        color: var(--text-grey);
        font-weight: 500;
    }

    /* Section Title - Compact */
    .section-title {
        font-weight: 600;
        color: var(--primary-blue);
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Focus Items - Compact */
    .focus-item {
        display: flex;
        align-items: center;
        padding: 8px 10px;
        border-radius: 8px;
        background: var(--bg-light);
        margin-bottom: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .focus-item:hover {
        background: #e8eef5;
        transform: translateX(3px);
    }

    .focus-item.priority-high { border-left-color: #ef4444; }
    .focus-item.priority-medium { border-left-color: #f59e0b; }
    .focus-item.priority-low { border-left-color: var(--accent-blue); }

    .focus-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        font-size: 0.8rem;
    }

    .focus-item.priority-high .focus-icon { background: #fee2e2; color: #dc2626; }
    .focus-item.priority-medium .focus-icon { background: #fef3c7; color: #d97706; }
    .focus-item.priority-low .focus-icon { background: #dbeafe; color: var(--accent-blue); }

    .focus-content { flex: 1; }
    .focus-content-title { font-weight: 600; font-size: 0.8rem; color: var(--text-dark); }
    .focus-content-sub { font-size: 0.65rem; color: var(--text-grey); }

    .focus-action {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--accent-blue);
        text-decoration: none;
        padding: 3px 8px;
        border-radius: 4px;
        background: #dbeafe;
    }

    .focus-action:hover {
        background: var(--accent-blue);
        color: white;
    }

    /* Activity Feed - Compact */
    .activity-item {
        display: flex;
        align-items: flex-start;
        padding: 6px 0;
        border-bottom: 1px solid var(--border-grey);
    }

    .activity-item:last-child { border-bottom: none; }

    .activity-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
        margin-top: 4px;
        flex-shrink: 0;
    }

    .activity-dot.approved { background: #10b981; }
    .activity-dot.pending { background: #f59e0b; }
    .activity-dot.invoice { background: var(--accent-blue); }
    .activity-dot.sync { background: #8b5cf6; }
    .activity-dot.rejected { background: #ef4444; }

    .activity-text { font-size: 0.75rem; color: var(--text-dark); }
    .activity-time { font-size: 0.65rem; color: var(--text-grey); }

    /* Quick Actions - Compact Grid */
    .quick-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .quick-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 10px;
        border-radius: 8px;
        border: 1.5px solid var(--border-grey);
        background: white;
        color: var(--primary-blue);
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        font-size: 0.75rem;
    }

    .quick-btn:hover {
        background: var(--primary-blue);
        color: white;
        border-color: var(--primary-blue);
    }

    .quick-btn.primary {
        background: var(--accent-blue);
        color: white;
        border-color: var(--accent-blue);
        grid-column: span 2;
    }

    .quick-btn.primary:hover {
        background: #2563EB;
    }

    /* Stats - Compact */
    .stat-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid var(--border-grey);
        font-size: 0.75rem;
    }

    .stat-row:last-child { border-bottom: none; }
    .stat-label { color: var(--text-grey); }
    .stat-value { font-weight: 600; color: var(--primary-blue); }
    .stat-value.danger { color: #dc2626; }
    .stat-value.success { color: #059669; }

    /* All Good State */
    .all-good {
        text-align: center;
        padding: 1rem;
        color: var(--text-grey);
    }

    .all-good i {
        font-size: 2rem;
        color: #10b981;
    }

    /* Loading */
    .loading-placeholder {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        border-radius: 6px;
        height: 35px;
        margin-bottom: 6px;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .spin { animation: spin 1s linear infinite; }

    /* Card Compact */
    .card-compact {
        padding: 0.75rem !important;
    }
</style>

<div class="container-fluid">
    
    <!-- Header Row -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h5 class="fw-bold mb-0" style="color: var(--primary-blue);">
                Welcome, {{ ucfirst(strtolower(Auth::user()->name ?? 'User')) }} 👋
            </h5>
            <small class="text-muted" style="font-size: 0.75rem;">Here's what's happening today</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="zoho-status" id="zohoStatus">
                <span class="dot"></span>
                <span id="zohoStatusText">Checking...</span>
            </div>
            <button class="btn btn-primary btn-sm" onclick="syncAll()" id="syncAllBtn" style="font-size: 0.75rem; padding: 4px 12px;">
                <i class="bi bi-arrow-repeat me-1"></i> Sync
            </button>
        </div>
    </div>

    <!-- Summary Cards Row -->
    <div class="row g-2 mb-3">
        <div class="col">
            <div class="summary-card" onclick="location.href='{{ route('vendors.index') }}'">
                <div class="summary-icon"><i class="bi bi-people"></i></div>
                <div class="summary-count" id="countVendors">0</div>
                <div class="summary-label">Vendors</div>
            </div>
        </div>
        <div class="col">
            <div class="summary-card" onclick="location.href='{{ route('vendors.approval.queue') }}'">
                <div class="summary-icon"><i class="bi bi-hourglass-split"></i></div>
                <div class="summary-count" id="countPending">0</div>
                <div class="summary-label">Pending</div>
            </div>
        </div>
        <div class="col">
            <div class="summary-card" onclick="location.href='{{ route('vendors.index') }}'">
                <div class="summary-icon"><i class="bi bi-check-circle"></i></div>
                <div class="summary-count" id="countApproved">0</div>
                <div class="summary-label">Approved</div>
            </div>
        </div>
        <div class="col">
            <div class="summary-card" onclick="location.href='{{ route('vendors.index') }}'">
                <div class="summary-icon"><i class="bi bi-x-circle"></i></div>
                <div class="summary-count" id="countRejected">0</div>
                <div class="summary-label">Rejected</div>
            </div>
        </div>
        <div class="col">
            <div class="summary-card" onclick="location.href='{{ route('invoices.index') }}'">
                <div class="summary-icon"><i class="bi bi-receipt"></i></div>
                <div class="summary-count" id="countInvoices">0</div>
                <div class="summary-label">Invoices</div>
            </div>
        </div>
        <div class="col">
            <div class="summary-card" onclick="location.href='{{ route('contracts.index') }}'">
                <div class="summary-icon"><i class="bi bi-file-earmark-text"></i></div>
                <div class="summary-count" id="countContracts">0</div>
                <div class="summary-label">Contracts</div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-3">
        
        <!-- Left Column - Needs Attention -->
        <div class="col-md-4">
            <div class="card card-compact h-100">
                <div class="section-title">
                    <i class="bi bi-bell"></i> Needs Attention
                </div>
                
                <div id="focusItemsContainer">
                    <div class="loading-placeholder"></div>
                    <div class="loading-placeholder"></div>
                    <div class="loading-placeholder"></div>
                </div>
                
                <div class="all-good" id="allGoodState" style="display: none;">
                    <i class="bi bi-check-circle-fill d-block"></i>
                    <div class="fw-semibold" style="font-size: 0.85rem;">All caught up!</div>
                    <small style="font-size: 0.7rem;">No pending items 🎉</small>
                </div>
            </div>
        </div>
        
        <!-- Middle Column - Recent Activity -->
        <div class="col-md-4">
            <div class="card card-compact h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="section-title mb-0">
                        <i class="bi bi-activity"></i> Recent Activity
                    </div>
                    <a href="{{ route('vendors.index') }}" class="text-decoration-none" style="font-size: 0.7rem;">View All →</a>
                </div>
                
                <div id="activityContainer">
                    <div class="loading-placeholder" style="height: 30px;"></div>
                    <div class="loading-placeholder" style="height: 30px;"></div>
                    <div class="loading-placeholder" style="height: 30px;"></div>
                    <div class="loading-placeholder" style="height: 30px;"></div>
                    <div class="loading-placeholder" style="height: 30px;"></div>
                </div>
            </div>
        </div>
        
        <!-- Right Column - Quick Actions & Stats -->
        <div class="col-md-4">
            <div class="card card-compact mb-2">
                <div class="section-title">
                    <i class="bi bi-lightning"></i> Quick Actions
                </div>
                
                <div class="quick-grid">
                    <a href="{{ route('vendors.create') }}" class="quick-btn primary">
                        <i class="bi bi-plus-circle"></i> Add Vendor
                    </a>
                    <a href="{{ route('vendors.index') }}" class="quick-btn">
                        <i class="bi bi-upload"></i> Import
                    </a>
                    <a href="{{ route('vendors.index') }}" class="quick-btn">
                        <i class="bi bi-people"></i> Vendors
                    </a>
                    <a href="{{ route('vendors.approval.queue') }}" class="quick-btn">
                        <i class="bi bi-check2-square"></i> Approvals
                    </a>
                    <a href="{{ route('invoices.index') }}" class="quick-btn">
                        <i class="bi bi-receipt"></i> Invoices
                    </a>
                    <a href="{{ route('contracts.index') }}" class="quick-btn">
                        <i class="bi bi-file-text"></i> Contracts
                    </a>
                    <a href="{{ route('settings.zoho') }}" class="quick-btn">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                    <a href="{{ route('master.template') }}" class="quick-btn">
                        <i class="bi bi-envelope"></i> Templates
                    </a>
                </div>
            </div>
            
            <div class="card card-compact">
                <div class="section-title">
                    <i class="bi bi-graph-up-arrow"></i> This Month
                </div>
                
                <div class="stat-row">
                    <span class="stat-label">New Vendors</span>
                    <span class="stat-value" id="statNewVendors">+0</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Zoho Synced</span>
                    <span class="stat-value" id="statZohoSynced">0/0</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Unpaid Invoices</span>
                    <span class="stat-value danger" id="statUnpaid">₹0</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Avg. Approval</span>
                    <span class="stat-value success" id="statAvgApproval">0 days</span>
                </div>
            </div>
        </div>
        
    </div>
    
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    checkZohoStatus();
});

async function loadDashboardData() {
    try {
        const summaryRes = await axios.get('/api/dashboard/summary');
        if (summaryRes.data.success) {
            const d = summaryRes.data.data;
            $('#countVendors').text(d.total_vendors || 0);
            $('#countPending').text(d.pending_approvals || 0);
            $('#countApproved').text(d.approved_vendors || 0);
            $('#countRejected').text(d.rejected_vendors || 0);
            $('#countInvoices').text(d.total_invoices || 0);
            $('#countContracts').text(d.total_contracts || 0);
            $('#statNewVendors').text('+' + (d.new_vendors_this_month || 0));
            $('#statUnpaid').text(formatCurrency(d.unpaid_amount || 0));
            $('#statAvgApproval').text((d.avg_approval_days || 0) + ' days');
        }
        
        const focusRes = await axios.get('/api/dashboard/focus-items');
        if (focusRes.data.success) {
            renderFocusItems(focusRes.data.data);
        }
        
        const activityRes = await axios.get('/api/dashboard/recent-activity');
        if (activityRes.data.success) {
            renderActivityFeed(activityRes.data.data);
        }
        
        const syncRes = await axios.get('/api/dashboard/sync-status');
        if (syncRes.data.success) {
            const s = syncRes.data.data;
            $('#statZohoSynced').text(`${s.vendors.synced}/${s.vendors.total}`);
        }
        
    } catch (error) {
        console.error('Dashboard load error:', error);
    }
}

function renderFocusItems(items) {
    const container = $('#focusItemsContainer');
    
    if (!items || items.length === 0) {
        container.hide();
        $('#allGoodState').show();
        return;
    }
    
    let html = '';
    items.forEach(item => {
        const priorityClass = item.priority === 'high' ? 'priority-high' : 
                             (item.priority === 'medium' ? 'priority-medium' : 'priority-low');
        
        html += `
            <div class="focus-item ${priorityClass}" onclick="window.location.href='${item.action_url || '#'}'">
                <div class="focus-icon"><i class="bi ${item.icon}"></i></div>
                <div class="focus-content">
                    <div class="focus-content-title">${item.title}</div>
                    <div class="focus-content-sub">${item.subtitle}</div>
                </div>
                <a href="${item.action_url || '#'}" class="focus-action">${item.action_text || 'View'} →</a>
            </div>
        `;
    });
    
    container.html(html);
}

function renderActivityFeed(activities) {
    const container = $('#activityContainer');
    
    if (!activities || activities.length === 0) {
        container.html('<div class="text-center text-muted small py-3">No recent activity</div>');
        return;
    }
    
    let html = '';
    activities.forEach(activity => {
        html += `
            <div class="activity-item">
                <div class="activity-dot ${activity.type}"></div>
                <div>
                    <div class="activity-text">${activity.message}</div>
                    <div class="activity-time">${activity.time_ago}</div>
                </div>
            </div>
        `;
    });
    
    container.html(html);
}

async function checkZohoStatus() {
    try {
        const res = await axios.get('/settings/zoho/status');
        if (res.data.connected) {
            $('#zohoStatus').removeClass('zoho-disconnected').addClass('zoho-connected');
            $('#zohoStatusText').text('Zoho Connected');
        } else {
            $('#zohoStatus').removeClass('zoho-connected').addClass('zoho-disconnected');
            $('#zohoStatusText').text('Disconnected');
        }
    } catch (error) {
        $('#zohoStatus').removeClass('zoho-connected').addClass('zoho-disconnected');
        $('#zohoStatusText').text('Disconnected');
    }
}

async function syncAll() {
    const btn = $('#syncAllBtn');
    btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat spin me-1"></i>');
    Toast.info('Syncing...', 'Please wait');
    
    try {
        const res = await axios.post('/api/dashboard/sync-all');
        if (res.data.success) {
            Toast.success('Done!', res.data.message || 'Synced');
            loadDashboardData();
        } else {
            Toast.error('Failed', res.data.message);
        }
    } catch (error) {
        Toast.error('Failed', error.response?.data?.message || 'Try again');
    } finally {
        btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i> Sync');
    }
}

function formatCurrency(amount) {
    if (amount >= 10000000) return '₹' + (amount / 10000000).toFixed(1) + 'Cr';
    if (amount >= 100000) return '₹' + (amount / 100000).toFixed(1) + 'L';
    if (amount >= 1000) return '₹' + (amount / 1000).toFixed(1) + 'K';
    return '₹' + amount;
}
</script>
@endpush