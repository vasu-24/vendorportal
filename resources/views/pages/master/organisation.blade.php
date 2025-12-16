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

    {{-- Flash message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>There were some problems with your input:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                            <th>Company Name</th>
                            <th>CIN</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organisations as $index => $org)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $org->company_name }}</td>
                                <td>{{ $org->cin }}</td>
                                <td style="white-space: pre-wrap;">{{ $org->address }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-3">
                                    No organisations found. Click <strong>Create Organisation</strong> to add one.
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
            <form action="{{ route('master.organisation.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: var(--primary-blue); color: white;">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Create Organisation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- Company Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold" for="company_name">Company Name <span class="text-danger">*</span></label>
                        <input type="text"
                               id="company_name"
                               name="company_name"
                               class="form-control"
                               value="{{ old('company_name') }}"
                               placeholder="e.g., Foundation for Interoperability in Digital Economy (FIDE)"
                               required>
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

@endsection

@push('scripts')
{{-- If there were validation errors, reopen the modal so user sees the form again --}}
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('createOrganisationModal');
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
</script>
@endif
@endpush
