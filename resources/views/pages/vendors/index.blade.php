@extends('layouts.app')
@section('title', 'All Vendors')

@section('content')
<div class="container-fluid">
  
  <!-- Success/Error Messages -->
  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col">
      <h2 class="fw-bold" style="color: var(--primary-blue);">
        <i class="bi bi-people me-2"></i>All Vendors
      </h2>
      <p class="text-muted">Manage vendors and send invitation emails</p>
    </div>
    <div class="col-auto">
      <a href="{{ route('vendors.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Vendor
      </a>
    </div>
  </div>

  <!-- Vendors Table -->
  <div class="card shadow-sm">
    <div class="card-body">
      
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead style="background: #f8f9fa;">
            <tr>
              <th>#</th>
              <th>Vendor Name</th>
              <th>Email</th>
              <th>Select Template</th>
              <th>Status</th>
              <th>Email Sent</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($vendors as $vendor)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                <strong>{{ $vendor->vendor_name }}</strong>
              </td>
              <td>
                <i class="bi bi-envelope me-1"></i>{{ $vendor->vendor_email }}
              </td>
              <td>
                <select class="form-select form-select-sm template-select" 
                        data-vendor-id="{{ $vendor->id }}"
                        {{ $vendor->status !== 'pending' ? 'disabled' : '' }}>
                  <option value="">-- Choose Template --</option>
                  @foreach($templates as $template)
                    <option value="{{ $template->id }}" 
                            {{ $vendor->template_id == $template->id ? 'selected' : '' }}>
                      {{ $template->name }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted template-status-{{ $vendor->id }}">
                  @if($vendor->template_id)
                    <i class="bi bi-check-circle text-success"></i> Template selected
                  @else
                    <i class="bi bi-exclamation-circle text-warning"></i> Select template first
                  @endif
                </small>
              </td>
              <td>
                @if($vendor->status === 'pending')
                  <span class="badge bg-warning text-dark">
                    <i class="bi bi-clock me-1"></i>Pending
                  </span>
                @elseif($vendor->status === 'accepted')
                  <span class="badge bg-success">
                    <i class="bi bi-check-circle me-1"></i>Accepted
                  </span>
                @else
                  <span class="badge bg-danger">
                    <i class="bi bi-x-circle me-1"></i>Rejected
                  </span>
                @endif
              </td>
              <td>
                @if($vendor->email_sent_at)
                  <small class="text-muted">
                    <i class="bi bi-check2 text-success me-1"></i>
                    {{ $vendor->email_sent_at->format('d M Y, h:i A') }}
                  </small>
                @else
                  <small class="text-muted">
                    <i class="bi bi-x text-danger me-1"></i>Not Sent
                  </small>
                @endif
              </td>
              <td>
                <button type="button"
                        class="btn btn-sm btn-primary send-mail-btn-{{ $vendor->id }}"
                        data-bs-toggle="modal"
                        data-bs-target="#sendMailModal{{ $vendor->id }}"
                        {{ !$vendor->template_id || $vendor->status !== 'pending' ? 'disabled' : '' }}>
                  <i class="bi bi-send me-1"></i>Send Mail
                </button>
              </td>
            </tr>

            <!-- Send Mail Modal for Each Vendor -->
            <div class="modal fade" id="sendMailModal{{ $vendor->id }}" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <form action="{{ route('vendors.sendEmail', $vendor->id) }}" method="POST" class="send-email-form">
                    @csrf
                    <div class="modal-header" style="background: var(--primary-blue); color: white;">
                      <h5 class="modal-title">
                        <i class="bi bi-send me-2"></i>Send Email to {{ $vendor->vendor_name }}
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      
                      <div class="alert alert-info">
                        <strong>📎 Attached Template:</strong> 
                        {{ $vendor->template ? $vendor->template->name : 'No template selected' }}
                      </div>

                      <div class="mb-3">
                        <label class="form-label fw-bold">Email Message <span class="text-danger">*</span></label>
                        <textarea name="email_message" class="form-control" rows="10" required 
                                  placeholder="Write your custom email message here...">Dear {{ $vendor->vendor_name }},

We are reaching out to you with an important document attached.

Please review the attached template and respond using the Accept or Reject buttons.

Best Regards,
Vendor Management Team</textarea>
                        <small class="text-muted">
                          <strong>Available placeholders:</strong> {vendor_name}, {vendor_email}, {current_date}, {current_time}, {portal_url}
                        </small>
                      </div>

                      <div class="alert alert-warning mb-0">
                        <strong>📄 Note:</strong> The template "{{ $vendor->template ? $vendor->template->name : '' }}" will be attached as a PDF file.
                      </div>

                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                      </button>
                      <button type="submit" class="btn btn-primary submit-btn">
                        <i class="bi bi-send me-1 send-icon"></i>
                        <span class="btn-text">Send Email Now</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2 spinner-loading"></span>
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No vendors found. <a href="{{ route('vendors.create') }}">Add your first vendor</a>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  
  // Auto-dismiss ALL alerts after 10 seconds
  const dismissAlerts = () => {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
      setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }, 10000);
    });
  };
  
  dismissAlerts();
  
  // Handle template selection change
  document.querySelectorAll('.template-select').forEach(select => {
    select.addEventListener('change', function() {
      const vendorId = this.getAttribute('data-vendor-id');
      const templateId = this.value;
      
      if (!templateId) {
        alert('Please select a template');
        return;
      }

      this.disabled = true;
      
      axios.post(`/vendors/${vendorId}/update-template`, {
        template_id: templateId
      })
      .then(response => {
        document.querySelector(`.send-mail-btn-${vendorId}`).disabled = false;
        
        document.querySelector(`.template-status-${vendorId}`).innerHTML = 
          '<i class="bi bi-check-circle text-success"></i> Template selected';
        
        const newAlert = document.createElement('div');
        newAlert.className = 'alert alert-success alert-dismissible fade show';
        newAlert.innerHTML = `
          <i class="bi bi-check-circle me-2"></i>${response.data.message}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.querySelector('.container-fluid').prepend(newAlert);
        
        setTimeout(() => {
          const bsAlert = new bootstrap.Alert(newAlert);
          bsAlert.close();
        }, 10000);
        
        this.disabled = false;
      })
      .catch(error => {
        alert('Error updating template');
        console.error(error);
        this.disabled = false;
      });
    });
  });
  
  // Handle Send Email Form Submit
  document.querySelectorAll('.send-email-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitBtn = this.querySelector('.submit-btn');
      const icon = submitBtn.querySelector('.send-icon');
      const btnText = submitBtn.querySelector('.btn-text');
      const spinner = submitBtn.querySelector('.spinner-loading');
      
      // Show loading state
      submitBtn.disabled = true;
      
      if (icon) icon.style.display = 'none';
      if (btnText) btnText.textContent = 'Sending...';
      if (spinner) spinner.classList.remove('d-none');
      
      // Form will submit normally
    });
  });
  
});
</script>
@endpush