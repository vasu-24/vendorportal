@extends('layouts.app')
@section('title', 'Add Vendor')

@section('content')
<div class="container-fluid">
  
  <!-- Page Header -->
  <div class="row mb-4">
    <div class="col">
      <h2 class="fw-bold" style="color: var(--primary-blue);">
        <i class="bi bi-person-plus me-2"></i>Add New Vendor
      </h2>
      <p class="text-muted">Create a new vendor profile</p>
    </div>
  </div>

  <!-- Create Vendor Form -->
  <div class="row">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          
          <form action="{{ route('vendors.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
              <label class="form-label fw-bold">Vendor Name <span class="text-danger">*</span></label>
              <input type="text" name="vendor_name" class="form-control" 
                     placeholder="Enter vendor company name" 
                     value="{{ old('vendor_name') }}" required>
              @error('vendor_name')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-4">
              <label class="form-label fw-bold">Vendor Email <span class="text-danger">*</span></label>
              <input type="email" name="vendor_email" class="form-control" 
                     placeholder="vendor@example.com" 
                     value="{{ old('vendor_email') }}" required>
              @error('vendor_email')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>Create Vendor
              </button>
              <a href="{{ route('vendors.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle me-1"></i>Cancel
              </a>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>

</div>
@endsection