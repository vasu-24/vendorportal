@extends('layouts.app')
@section('title', 'Organisation Master')

@section('content')
<div class="container-fluid py-3">

    {{-- Page title + Create button --}}
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="fw-bold mb-1" style="color: var(--primary-blue);">
                <i class="bi bi-building me-2"></i>Organisation Master
            </h2>
            <p class="text-muted mb-0">Manage organisation details used in contracts</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrganisationModal">
                <i class="bi bi-plus-circle me-1"></i> Create Organisation
            </button>
        </div>
    </div>

    {{-- Organisation List --}}
    <div class="card shadow-sm">
        <div class="card-header">
            Organisation List
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Logo</th>
                            <th>Company Name</th>
                            <th>Short Name</th>
                            <th>CIN</th>
                            <th>Address</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organisations as $index => $org)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($org->logo)
                                        <img src="{{ asset('storage/' . $org->logo) }}" 
                                             alt="Logo" 
                                             style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px; border: 1px solid #dee2e6;">
                                    @else
                                        <div style="width: 40px; height: 40px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $org->company_name }}</td>
                                <td>
                                    @if($org->short_name)
                                        <span class="badge" style="background: rgba(23, 64, 129, 0.1); color: var(--primary-blue);">
                                            {{ $org->short_name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $org->cin ?? '-' }}</td>
                                <td style="white-space: pre-wrap; max-width: 250px;">{{ $org->address ?? '-' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="editOrganisation({{ $org->id }})"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteOrganisation({{ $org->id }}, '{{ $org->company_name }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3">
                                    <i class="bi bi-building text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0 mt-2">No organisations found. Click <strong>Create Organisation</strong> to add one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Create Organisation Modal --}}
<div class="modal fade" id="createOrganisationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createOrganisationForm" action="{{ route('master.organisation.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header" style="background: var(--primary-blue); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Create Organisation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- Logo Upload --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Company Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            <div id="createLogoPreview" 
                                 style="width: 80px; height: 80px; border: 2px dashed #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f8f9fa;">
                                <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" 
                                       id="create_logo" 
                                       name="logo" 
                                       class="form-control" 
                                       accept="image/*"
                                       onchange="previewLogo(this, 'createLogoPreview')">
                                <small class="text-muted">Accepted: JPG, PNG, GIF (Max 2MB)</small>
                            </div>
                        </div>
                    </div>

                    {{-- Company Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="company_name">Company Name <span class="text-danger">*</span></label>
                        <input type="text"
                               id="company_name"
                               name="company_name"
                               class="form-control"
                               value="{{ old('company_name') }}"
                               placeholder="e.g., Foundation for Interoperability in Digital Economy"
                               required>
                    </div>

                    {{-- Short Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="short_name">Short Name</label>
                        <input type="text"
                               id="short_name"
                               name="short_name"
                               class="form-control"
                               value="{{ old('short_name') }}"
                               placeholder="e.g., FIDE"
                               maxlength="20">
                        <small class="text-muted">Abbreviation or short form of company name</small>
                    </div>

                    {{-- CIN --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="cin">CIN</label>
                        <input type="text"
                               id="cin"
                               name="cin"
                               class="form-control"
                               value="{{ old('cin') }}"
                               placeholder="e.g., U72900KA2019NPL127900">
                    </div>

                    {{-- Address --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="address">Address</label>
                        <textarea id="address"
                                  name="address"
                                  class="form-control"
                                  rows="3"
                                  placeholder="e.g., No.85, Quorum, 7th Cross Road, Koramangala, 4th Block, Bangalore – 560034, India">{{ old('address') }}</textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Save Organisation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Organisation Modal --}}
<div class="modal fade" id="editOrganisationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editOrganisationForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header" style="background: var(--primary-blue); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil me-2"></i>Edit Organisation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- Logo Upload --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Company Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            <div id="editLogoPreview" 
                                 style="width: 80px; height: 80px; border: 2px dashed #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f8f9fa;">
                                <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" 
                                       id="edit_logo" 
                                       name="logo" 
                                       class="form-control" 
                                       accept="image/*"
                                       onchange="previewLogo(this, 'editLogoPreview')">
                                <small class="text-muted">Leave empty to keep current logo</small>
                            </div>
                        </div>
                    </div>

                    {{-- Company Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="edit_company_name">Company Name <span class="text-danger">*</span></label>
                        <input type="text"
                               id="edit_company_name"
                               name="company_name"
                               class="form-control"
                               required>
                    </div>

                    {{-- Short Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="edit_short_name">Short Name</label>
                        <input type="text"
                               id="edit_short_name"
                               name="short_name"
                               class="form-control"
                               maxlength="20">
                        <small class="text-muted">Abbreviation or short form of company name</small>
                    </div>

                    {{-- CIN --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="edit_cin">CIN</label>
                        <input type="text"
                               id="edit_cin"
                               name="cin"
                               class="form-control">
                    </div>

                    {{-- Address --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="edit_address">Address</label>
                        <textarea id="edit_address"
                                  name="address"
                                  class="form-control"
                                  rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Update Organisation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteOrganisationModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background: #dc3545; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-1">Are you sure you want to delete</p>
                <p class="fw-bold mb-0" id="deleteOrgName"></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteOrganisationForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Logo Preview Function
    function previewLogo(input, previewId) {
        const preview = document.getElementById(previewId);
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                Toast.error('File size must be less than 2MB');
                input.value = '';
                return;
            }
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                Toast.error('Please select an image file');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: contain;">`;
            };
            reader.readAsDataURL(file);
        }
    }

    // Edit Organisation
    function editOrganisation(id) {
        // Fetch organisation data
        fetch(`/master/organisation/${id}/edit`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const org = data.organisation;
                
                // Set form action
                document.getElementById('editOrganisationForm').action = `/master/organisation/${id}`;
                
                // Fill form fields
                document.getElementById('edit_company_name').value = org.company_name || '';
                document.getElementById('edit_short_name').value = org.short_name || '';
                document.getElementById('edit_cin').value = org.cin || '';
                document.getElementById('edit_address').value = org.address || '';
                
                // Set logo preview
                const preview = document.getElementById('editLogoPreview');
                if (org.logo) {
                    preview.innerHTML = `<img src="/storage/${org.logo}" style="width: 100%; height: 100%; object-fit: contain;">`;
                } else {
                    preview.innerHTML = '<i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>';
                }
                
                // Show modal
                new bootstrap.Modal(document.getElementById('editOrganisationModal')).show();
            } else {
                Toast.error('Failed to load organisation data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Toast.error('Something went wrong');
        });
    }

    // Delete Organisation
    function deleteOrganisation(id, name) {
        document.getElementById('deleteOrgName').textContent = name;
        document.getElementById('deleteOrganisationForm').action = `/master/organisation/${id}`;
        new bootstrap.Modal(document.getElementById('deleteOrganisationModal')).show();
    }

    // Show Toast for session messages
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Toast.success('{{ session('success') }}');
        @endif
        
        @if(session('error'))
            Toast.error('{{ session('error') }}');
        @endif
        
        @if($errors->any())
            Toast.error('Please check the form for errors');
            // Reopen create modal if there were validation errors
            new bootstrap.Modal(document.getElementById('createOrganisationModal')).show();
        @endif
    });
</script>
@endpush