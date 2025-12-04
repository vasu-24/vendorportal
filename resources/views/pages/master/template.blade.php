@extends('layouts.app')
@section('title', 'Templates')

@section('content')
<div class="container-fluid">
  
  <!-- Success Message -->
  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <!-- Page Header -->
  <div class="row mb-4 align-items-center">
    <div class="col">
      <h2 class="fw-bold mb-1" style="color: var(--primary-blue);">
        <i class="bi bi-file-earmark-text me-2"></i>Templates
      </h2>
      <p class="text-muted mb-0">Manage email templates for vendor communication</p>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
        <i class="bi bi-plus-circle me-1"></i> Create Template
      </button>
    </div>
  </div>

  <!-- Template Cards -->
  <div class="row g-3">
    
    @forelse($templates as $template)
    <div class="col-md-6 col-lg-4">
      <div class="card template-card shadow-sm h-100">
        <div class="card-body p-3">
          
          <!-- Header with Title and Badge -->
          <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="card-title fw-bold mb-0" style="color: var(--primary-blue);">
              <i class="bi bi-file-earmark-text me-2"></i>{{ $template->name }}
            </h6>
            <span class="badge {{ $template->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
              {{ ucfirst($template->status) }}
            </span>
          </div>
          
          <!-- Subject -->
          <div class="mb-2">
            <strong class="small">Subject:</strong>
            <span class="text-muted small d-block">{{ Str::limit($template->subject, 50) }}</span>
          </div>
          
          <!-- Body Preview -->
          <p class="text-muted small mb-3" style="min-height: 60px; line-height: 1.5;">
            {{ Str::limit($template->body, 120) }}
          </p>
          
          <!-- Date -->
          <div class="mb-3">
            <small class="text-muted">
              <i class="bi bi-clock me-1"></i>{{ $template->updated_at->format('d M Y') }}
            </small>
          </div>
          
          <!-- Action Buttons -->
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary flex-fill" onclick="previewTemplate({{ $template->id }})">
              <i class="bi bi-eye me-1"></i>Preview
            </button>
            <button class="btn btn-sm btn-outline-secondary flex-fill" onclick="editTemplate({{ $template->id }})">
              <i class="bi bi-pencil me-1"></i>Edit
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate({{ $template->id }})">
              <i class="bi bi-trash"></i>
            </button>
          </div>
          
        </div>
      </div>
    </div>
    @empty
    <div class="col-12">
      <div class="text-center py-5">
        <i class="bi bi-file-earmark-text" style="font-size: 4rem; color: #ccc;"></i>
        <p class="text-muted mt-3">No templates found. Create your first template!</p>
      </div>
    </div>
    @endforelse

  </div>

</div>

<!-- Create Template Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('master.template.store') }}" method="POST">
        @csrf
        <div class="modal-header" style="background: var(--primary-blue); color: white;">
          <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Template</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          
          <div class="mb-3">
            <label class="form-label fw-bold">Template Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g., Vendor Invitation" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Subject Line <span class="text-danger">*</span></label>
            <input type="text" name="subject" class="form-control" placeholder="e.g., Welcome to Our Vendor Portal" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Email Body <span class="text-danger">*</span></label>
            <textarea name="body" class="form-control" rows="10" placeholder="Write your email template here..." required></textarea>
            <small class="text-muted">Use placeholders: {vendor_name}, {vendor_email}, {portal_url}, {current_date}</small>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Category</label>
              <select name="category" class="form-select">
                <option value="welcome">Welcome</option>
                <option value="notification">Notification</option>
                <option value="request">Request</option>
                <option value="reminder">Reminder</option>
                <option value="invoice">Invoice</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Status</label>
              <select name="status" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Save Template
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Template Modal -->
<div class="modal fade" id="editTemplateModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="editTemplateForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header" style="background: var(--primary-blue); color: white;">
          <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Template</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          
          <input type="hidden" id="edit_template_id">
          
          <div class="mb-3">
            <label class="form-label fw-bold">Template Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="edit_name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Subject Line <span class="text-danger">*</span></label>
            <input type="text" name="subject" id="edit_subject" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Email Body <span class="text-danger">*</span></label>
            <textarea name="body" id="edit_body" class="form-control" rows="10" required></textarea>
            <small class="text-muted">Use placeholders: {vendor_name}, {vendor_email}, {portal_url}, {current_date}</small>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Category</label>
              <select name="category" id="edit_category" class="form-select">
                <option value="welcome">Welcome</option>
                <option value="notification">Notification</option>
                <option value="request">Request</option>
                <option value="reminder">Reminder</option>
                <option value="invoice">Invoice</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Status</label>
              <select name="status" id="edit_status" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Update Template
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background: var(--primary-blue); color: white;">
        <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Template Preview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="border rounded p-4" style="background: #f8f9fa;">
          <div class="mb-3">
            <strong>Template Name:</strong> 
            <span id="preview_name"></span>
          </div>
          <div class="mb-3">
            <strong>Subject:</strong> 
            <span id="preview_subject"></span>
          </div>
          <hr>
          <div id="preview_body" style="white-space: pre-line;"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('head')
<style>
/* Template Card Styling */
.template-card {
    border: 1px solid var(--border-grey);
    border-radius: 10px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.template-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(23, 64, 129, 0.12);
}

/* Badge Styling */
.badge-active {
    background-color: #28a745;
    color: white;
    font-size: 0.7rem;
    padding: 0.35rem 0.6rem;
    border-radius: 4px;
}

.badge-inactive {
    background-color: #ffc107;
    color: #000;
    font-size: 0.7rem;
    padding: 0.35rem 0.6rem;
    border-radius: 4px;
}

/* Button Styling */
.btn-outline-primary {
    border-color: var(--primary-blue);
    color: var(--primary-blue);
    font-size: 0.85rem;
}

.btn-outline-primary:hover {
    background: var(--primary-blue);
    color: white;
}

.btn-outline-secondary {
    border-color: #6c757d;
    color: #6c757d;
    font-size: 0.85rem;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

.btn-outline-danger {
    border-color: #dc3545;
    color: #dc3545;
    font-size: 0.85rem;
    padding: 0.375rem 0.5rem;
}

.btn-outline-danger:hover {
    background: #dc3545;
    color: white;
}
</style>
@endpush

@push('scripts')
<script>
// Preview Template
function previewTemplate(id) {
  axios.get(`/master/template/${id}`)
    .then(response => {
      const template = response.data;
      document.getElementById('preview_name').textContent = template.name;
      document.getElementById('preview_subject').textContent = template.subject;
      document.getElementById('preview_body').textContent = template.body;
      
      const modal = new bootstrap.Modal(document.getElementById('previewModal'));
      modal.show();
    })
    .catch(error => {
      alert('Error loading template');
      console.error(error);
    });
}

// Edit Template
function editTemplate(id) {
  axios.get(`/master/template/${id}`)
    .then(response => {
      const template = response.data;
      
      document.getElementById('edit_template_id').value = template.id;
      document.getElementById('edit_name').value = template.name;
      document.getElementById('edit_subject').value = template.subject;
      document.getElementById('edit_body').value = template.body;
      document.getElementById('edit_category').value = template.category;
      document.getElementById('edit_status').value = template.status;
      
      // Update form action
      document.getElementById('editTemplateForm').action = `/master/template/${template.id}`;
      
      const modal = new bootstrap.Modal(document.getElementById('editTemplateModal'));
      modal.show();
    })
    .catch(error => {
      alert('Error loading template');
      console.error(error);
    });
}

// Delete Template
function deleteTemplate(id) {
  if (confirm('Are you sure you want to delete this template?')) {
    axios.delete(`/master/template/${id}`)
      .then(response => {
        alert(response.data.message);
        location.reload();
      })
      .catch(error => {
        alert('Error deleting template');
        console.error(error);
      });
  }
}
</script>
@endpush