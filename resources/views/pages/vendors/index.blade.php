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
                        class="btn btn-sm btn-primary send-mail-btn"
                        data-vendor-id="{{ $vendor->id }}"
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
                  <div class="modal-header" style="background: var(--primary-blue); color: white;">
                    <h5 class="modal-title">
                      <i class="bi bi-send me-2"></i>Confirm Send Email
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    
                    <!-- Vendor Info -->
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label fw-bold text-muted" style="font-size: 0.75rem;">VENDOR NAME</label>
                        <div class="p-2 bg-light rounded border">{{ $vendor->vendor_name }}</div>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label fw-bold text-muted" style="font-size: 0.75rem;">EMAIL ADDRESS</label>
                        <div class="p-2 bg-light rounded border">{{ $vendor->vendor_email }}</div>
                      </div>
                    </div>

                    <hr>

                    <!-- Template Name -->
                    <div class="alert alert-info mb-3 template-name-display">
                      <i class="bi bi-file-earmark-text me-2"></i>
                      <strong>Template:</strong> {{ $vendor->template ? $vendor->template->name : 'No template selected' }}
                    </div>

                    <!-- Subject (From Template Database) -->
                    <div class="mb-3">
                      <label class="form-label fw-bold text-muted" style="font-size: 0.75rem;">EMAIL SUBJECT</label>
                      <div class="p-2 bg-light rounded border template-subject-display">
                        {{ $vendor->template ? $vendor->template->subject : 'No subject' }}
                      </div>
                    </div>

                    <!-- Body (From Template Database - Read Only) -->
                    <div class="mb-3">
                      <label class="form-label fw-bold text-muted" style="font-size: 0.75rem;">EMAIL CONTENT</label>
                      <div class="p-3 bg-light rounded border template-body-display" style="max-height: 300px; overflow-y: auto; white-space: pre-line; line-height: 1.8;">{{ $vendor->template ? $vendor->template->body : 'No content available' }}</div>
                    </div>

                    <div class="alert alert-warning mb-0">
                      <i class="bi bi-info-circle me-2"></i>
                      <strong>Note:</strong> To edit this content, go to <a href="{{ route('master.template') }}" target="_blank" class="alert-link">Templates Master</a> page.
                    </div>

                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                      <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary send-email-btn" data-vendor-id="{{ $vendor->id }}" data-action="{{ route('vendors.sendEmail', $vendor->id) }}">
                      <i class="bi bi-send me-1"></i>Send Email Now
                    </button>
                  </div>
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
// Function to show inline alert
function showInlineAlert(message, type = 'success') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.innerHTML = `
    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  document.querySelector('.container-fluid').prepend(alertDiv);
  
  setTimeout(() => {
    try { new bootstrap.Alert(alertDiv).close(); } catch(e) {}
  }, 10000);
}

document.addEventListener('DOMContentLoaded', function() {
  
  // Auto-dismiss alerts
  setTimeout(() => {
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
      try { new bootstrap.Alert(alert).close(); } catch(e) {}
    });
  }, 10000);
  
  // 🔥 FIXED - Handle template selection with dynamic modal update
  document.querySelectorAll('.template-select').forEach(select => {
    select.addEventListener('change', function() {
      const vendorId = this.getAttribute('data-vendor-id');
      const templateId = this.value;
      
      if (!templateId) return;

      this.disabled = true;
      
      axios.post(`/vendors/${vendorId}/update-template`, {
        template_id: templateId
      })
      .then(response => {
        // Enable send button
        const sendBtn = document.querySelector(`button.send-mail-btn[data-vendor-id="${vendorId}"]`);
        if (sendBtn) sendBtn.disabled = false;
        
        // Update status text
        const status = document.querySelector(`.template-status-${vendorId}`);
        if (status) status.innerHTML = '<i class="bi bi-check-circle text-success"></i> Template selected';
        
        // 🔥🔥🔥 UPDATE MODAL CONTENT DYNAMICALLY
        if (response.data.template) {
          const modal = document.querySelector(`#sendMailModal${vendorId}`);
          
          if (modal) {
            // Update template name (blue alert box)
            const templateNameDiv = modal.querySelector('.template-name-display');
            if (templateNameDiv) {
              templateNameDiv.innerHTML = `
                <i class="bi bi-file-earmark-text me-2"></i>
                <strong>Template:</strong> ${response.data.template.name}
              `;
            }
            
            // Update subject
            const subjectDiv = modal.querySelector('.template-subject-display');
            if (subjectDiv) {
              subjectDiv.textContent = response.data.template.subject;
            }
            
            // Update body
            const bodyDiv = modal.querySelector('.template-body-display');
            if (bodyDiv) {
              bodyDiv.textContent = response.data.template.body;
            }
          }
        }
        
        showInlineAlert(response.data.message, 'success');
        this.disabled = false;
      })
      .catch(error => {
        showInlineAlert('Error updating template', 'danger');
        this.disabled = false;
      });
    });
  });
  
  // Handle Send Email Button Click
  document.querySelectorAll('.send-email-btn').forEach(button => {
    button.addEventListener('click', function() {
      const actionUrl = this.getAttribute('data-action');
      const originalHTML = this.innerHTML;
      
      this.disabled = true;
      this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
      
      axios.post(actionUrl)
        .then(response => {
          // Close modal
          const modalEl = this.closest('.modal');
          if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }
          
          showInlineAlert(response.data.message, 'success');
          
          setTimeout(() => {
            location.reload();
          }, 2000);
        })
        .catch(error => {
          this.disabled = false;
          this.innerHTML = originalHTML;
          showInlineAlert(error.response?.data?.message || 'Failed to send email', 'danger');
        });
    });
  });
  
});
</script>
@endpush