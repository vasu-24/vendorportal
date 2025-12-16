@extends('layouts.app')
@section('title', 'Internal Dashboard')

@section('content')
@php
  $userName = ucfirst(strtolower(Auth::user()->name ?? 'User'));
@endphp

<div class="row mb-4">
  <div class="col">
    <h2 class="fw-bold" style="color: var(--primary-blue);">
      Welcome, <span>{{ $userName }}</span>
    </h2>
    <div class="text-muted mb-2">Your vendor portal summary at a glance</div>
  </div>
</div>

{{-- ===== SUMMARY CARDS ===== --}}
<div class="row g-3 mb-4" id="summaryCards">

  <div class="col-md-3">
    <div class="card p-3 text-center">
      <div style="font-size: 2rem; color: var(--primary-blue);"><i class="bi bi-people"></i></div>
      <div class="fw-bold fs-5">Vendors</div>
      <div class="text-muted fs-6" id="countVendors">0</div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card p-3 text-center">
      <div style="font-size: 2rem; color: var(--primary-blue);"><i class="bi bi-file-earmark-text"></i></div>
      <div class="fw-bold fs-5">Pending Approvals</div>
      <div class="text-muted fs-6" id="countPending">0</div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card p-3 text-center">
      <div style="font-size: 2rem; color: var(--primary-blue);"><i class="bi bi-check2-circle"></i></div>
      <div class="fw-bold fs-5">Approved Vendors</div>
      <div class="text-muted fs-6" id="countApproved">0</div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card p-3 text-center">
      <div style="font-size: 2rem; color: var(--primary-blue);"><i class="bi bi-x-circle"></i></div>
      <div class="fw-bold fs-5">Rejected Vendors</div>
      <div class="text-muted fs-6" id="countRejected">0</div>
    </div>
  </div>

</div>

{{-- ===== RECENT ONBOARDINGS + QUICK LINKS ===== --}}
<div class="row g-4">

  <div class="col-md-6">
    <div class="card p-3 h-100">
      <div class="fw-bold mb-2" style="color: var(--primary-blue);">Recent Vendor Registrations</div>

      <table class="table table-borderless mb-0 align-middle" id="tblRecentVendors">
        <thead>
          <tr>
            <th style="color: var(--text-grey);">Date</th>
            <th style="color: var(--text-grey);">Vendor</th>
            <th style="color: var(--text-grey);">Email</th>
            <th style="color: var(--text-grey);">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-3 h-100">
      <div class="fw-bold mb-2" style="color: var(--primary-blue);">Quick Actions</div>

      <div class="d-grid gap-2">
        <a href="{{ route('vendors.create') }}" class="btn btn-primary">+ Add Vendor</a>
        <a href="{{ route('vendors.index') }}" class="btn btn-outline-primary">View All Vendors</a>
        <a href="{{ route('vendors.approvals') }}" class="btn btn-outline-primary">Approval Queue</a>
      </div>
    </div>
  </div>

</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
  try {

    // ===== SUMMARY API =====
    const resSummary = await axios.get('/api/vendor-dashboard/summary');
    const s = resSummary.data;

    document.getElementById('countVendors').textContent  = s.vendors;
    document.getElementById('countPending').textContent  = s.pending;
    document.getElementById('countApproved').textContent = s.approved;
    document.getElementById('countRejected').textContent = s.rejected;

    // ===== RECENT VENDORS API =====
    const resRecent = await axios.get('/api/vendor-dashboard/recent');

    const tbody = document.querySelector('#tblRecentVendors tbody');
    if (!resRecent.data.length) {
      tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No recent vendors</td></tr>';
      return;
    }

    tbody.innerHTML = resRecent.data.map(v => `
      <tr>
        <td>${v.created_at || ''}</td>
        <td>${v.vendor_name || ''}</td>
        <td>${v.email || ''}</td>
        <td><span class="badge bg-${statusColor(v.status)}">${v.status}</span></td>
      </tr>
    `).join('');

  } catch (err) {
    console.error(err);
  }
});

function statusColor(s) {
  switch(s){
    case 'approved': return 'success';
    case 'pending':  return 'warning';
    case 'rejected': return 'danger';
    default: return 'secondary';
  }
}
</script>
@endpush
