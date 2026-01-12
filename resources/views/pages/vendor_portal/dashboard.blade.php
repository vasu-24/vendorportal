@extends('layouts.vendor')

@section('title', 'Dashboard - Vendor Portal')

@section('content')
<div class="container-fluid py-4">
    
    @php
        $vendor = Auth::guard('vendor')->user();
        $companyInfo = $vendor->companyInfo;
        $contact = $vendor->contact;
    @endphp

    <!-- Welcome Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1" style="color: var(--primary-blue);">
                                Welcome back, {{ $vendor->vendor_name }}!
                            </h5>
                            <p class="text-muted mb-0 small">
                                <i class="bi bi-calendar me-1"></i>{{ now()->format('l, d F Y') }}
                            </p>
                        </div>
                        <div>
                            <span class="badge bg-success px-3 py-2">
                                <i class="bi bi-check-circle me-1"></i>Approved
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards - Compact -->
    <div class="row mb-3">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 d-flex align-items-center justify-content-center me-3" 
                             style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1);">
                            <i class="bi bi-receipt-cutoff fs-4" style="color: var(--accent-blue);"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: var(--text-dark);" id="totalInvoices">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </h3>
                            <small class="text-muted">Total Invoices</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 d-flex align-items-center justify-content-center me-3" 
                             style="width: 48px; height: 48px; background: rgba(34, 197, 94, 0.1);">
                            <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: var(--text-dark);" id="approvedInvoices">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </h3>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 d-flex align-items-center justify-content-center me-3" 
                             style="width: 48px; height: 48px; background: rgba(234, 179, 8, 0.1);">
                            <i class="bi bi-clock-history fs-4 text-warning"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: var(--text-dark);" id="pendingInvoices">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </h3>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-3 d-flex align-items-center justify-content-center me-3" 
                             style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.1);">
                            <i class="bi bi-x-circle-fill fs-4 text-danger"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: var(--text-dark);" id="rejectedInvoices">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </h3>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions - More Options -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: var(--primary-blue);">
                        <i class="bi bi-lightning-charge-fill me-2"></i>Quick Actions
                    </h6>
                    <div class="row g-2">
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('vendor.invoices.create') }}" class="btn btn-primary w-100">
                                <i class="bi bi-plus-circle me-1"></i>Create Invoice
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('vendor.invoices.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-receipt me-1"></i>All Invoices
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('vendor.invoices.index', ['status' => 'pending']) }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-clock me-1"></i>Pending
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('vendor.documents') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-cloud-upload me-1"></i>Documents
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('vendor.contracts.index') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-file-text me-1"></i>Contracts
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('vendor.profile') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-person-gear me-1"></i>Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row - Compact -->
    <div class="row mb-3">
        <!-- Invoice Status Chart -->
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: var(--primary-blue);">
                        <i class="bi bi-pie-chart-fill me-2"></i>Invoice Status
                    </h6>
                    <div class="d-flex justify-content-center align-items-center" style="height: 220px;" id="statusChartContainer">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Activity Chart -->
        <div class="col-lg-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-3" style="color: var(--primary-blue);">
                        <i class="bi bi-graph-up me-2"></i>Monthly Submissions
                    </h6>
                    <div style="height: 220px;" id="monthlyChartContainer">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Company & Contact Info - Compact -->
    <!-- Removed Company Information and Contact Information sections as requested -->

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
let statusChart = null;
let monthlyChart = null;

// Fetch and update stats
async function loadStats() {
    try {
        const response = await fetch('/api/vendor/dashboard/stats');
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            document.getElementById('totalInvoices').textContent = stats.total_invoices;
            document.getElementById('approvedInvoices').textContent = stats.approved_invoices;
            document.getElementById('pendingInvoices').textContent = stats.pending_invoices;
            document.getElementById('rejectedInvoices').textContent = stats.rejected_invoices;
            
            // Load status chart
            loadStatusChart(stats);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
        document.getElementById('totalInvoices').textContent = '0';
        document.getElementById('approvedInvoices').textContent = '0';
        document.getElementById('pendingInvoices').textContent = '0';
        document.getElementById('rejectedInvoices').textContent = '0';
    }
}

// Load status chart
function loadStatusChart(stats) {
    const container = document.getElementById('statusChartContainer');
    container.innerHTML = '<canvas id="statusChart"></canvas>';
    
    const ctx = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [stats.approved_invoices, stats.pending_invoices, stats.rejected_invoices],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(234, 179, 8, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(234, 179, 8, 1)',
                    'rgba(239, 68, 68, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 10,
                        font: { size: 12 },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 10,
                    cornerRadius: 6
                }
            },
            cutout: '65%'
        }
    });
}

// Load monthly chart
async function loadMonthlyChart() {
    try {
        const response = await fetch('/api/vendor/dashboard/monthly-data');
        const result = await response.json();
        
        if (result.success) {
            const container = document.getElementById('monthlyChartContainer');
            container.innerHTML = '<canvas id="monthlyChart"></canvas>';
            
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            monthlyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: result.data.labels,
                    datasets: [{
                        label: 'Invoices',
                        data: result.data.values,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 10,
                            cornerRadius: 6,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { 
                                stepSize: 1, 
                                color: '#6b7280',
                                font: { size: 11 }
                            },
                            grid: { color: 'rgba(0, 0, 0, 0.05)', drawBorder: false }
                        },
                        x: {
                            ticks: { 
                                color: '#6b7280',
                                font: { size: 11 }
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading monthly data:', error);
    }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadMonthlyChart();
});
</script>

@endsection