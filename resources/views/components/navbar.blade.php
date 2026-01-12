@php
    $organisation = \App\Models\Organisation::first();
@endphp

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top"
     style="border-bottom: 1.5px solid var(--border-grey); padding-top: 0.35rem; padding-bottom: 0.35rem; min-height: 56px;">
  <div class="container-fluid px-4">

    <!-- Brand -->
    <a class="navbar-brand fw-bold d-flex align-items-center"
       href="{{ route('dashboard') }}"
       style="color: var(--primary-blue); padding:0; margin:0;"
       @if($organisation && $organisation->company_name)
           data-bs-toggle="tooltip" 
           data-bs-placement="bottom" 
           title="{{ $organisation->company_name }}"
       @endif
    >

        {{-- Dynamic Logo from Organisation --}}
        @if($organisation && $organisation->logo)
            <img src="{{ asset('storage/' . $organisation->logo) }}"
                 alt="{{ $organisation->short_name ?? 'Logo' }}"
                 style="height:28px; width:auto; margin-right:8px; margin-bottom:2px;">
        @else
            <img src="{{ asset('image/logo.png') }}"
                 alt="Vendor Portal"
                 style="height:28px; width:auto; margin-right:8px; margin-bottom:2px;">
        @endif

        {{-- Full Name - Truncated cleanly (hover shows full name) --}}
        <span class="text-truncate"
              style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:inline-block;">
            {{ $organisation->company_name ?? 'Vendor Portal' }}
        </span>
    </a>

    <!-- Mobile Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#mainNavbar" aria-controls="mainNavbar"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="mainNavbar">

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 pe-2">

        <!-- Dashboard -->
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
             href="{{ route('dashboard') }}">
             <i class="bi bi-speedometer2 me-1"></i>Dashboard
          </a>
        </li>

        <!-- Vendors -->
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}"
             href="{{ route('vendors.index') }}">
              <i class="bi bi-people me-1"></i> Vendors
          </a>
        </li>

        <!-- Invoices -->
        <li class="nav-item">
          <a class="nav-link {{ request()->is('invoices*') ? 'active' : '' }}"
             href="{{ route('invoices.index') }}">
             <i class="bi bi-receipt me-1"></i>Invoices
          </a>
        </li>

        <!-- Travel Invoices -->
        <li class="nav-item">
          <a class="nav-link {{ request()->is('admin/travel-invoices*') ? 'active' : '' }}"
             href="{{ route('admin.travel-invoices.index') }}">
             <i class="bi bi-airplane me-1"></i>Travel Invoices
          </a>
        </li>

        <!-- Contracts -->
        <li class="nav-item">
          <a class="nav-link {{ request()->is('contracts*') ? 'active' : '' }}"
             href="{{ route('contracts.index') }}">
             <i class="bi bi-file-earmark-check me-1"></i>Contracts
          </a>
        </li>

        <!-- Bank Payments -->
        {{-- <li class="nav-item">
          <a class="nav-link {{ request()->is('bank-payments*') ? 'active' : '' }}"
             href="#">
             <i class="bi bi-bank me-1"></i>Bank Payments
          </a>
        </li> --}}

        <!-- Settings -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->is('settings*') || request()->is('master*') || request()->is('admin*') ? 'active' : '' }}"
             href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-gear me-1"></i>Settings
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="settingsDropdown">

            <!-- Master Section -->
            <li><h6 class="dropdown-header">Master</h6></li>

            <li>
              <a class="dropdown-item {{ request()->routeIs('master.template') ? 'active' : '' }}"
                 href="{{ route('master.template') }}" style="padding-left: 2rem;">
                 <i class="bi bi-file-earmark-text me-2"></i>Template
              </a>
            </li>

            <li>
              <a class="dropdown-item {{ request()->routeIs('master.organisation') ? 'active' : '' }}"
                 href="{{ route('master.organisation') }}" style="padding-left: 2rem;">
                 <i class="bi bi-building me-2"></i>Organisation
              </a>
            </li>

            <li>
              <a class="dropdown-item {{ request()->routeIs('categories.index') ? 'active' : '' }}"
                 href="{{ route('categories.index') }}" style="padding-left: 2rem;">
                 <i class="bi bi-tags me-2"></i>Categories
              </a>
            </li>

            <!-- Manager Tags Master -->
            <li>
              <a class="dropdown-item {{ request()->routeIs('master.manager-tags') ? 'active' : '' }}"
                 href="{{ route('master.manager-tags') }}" style="padding-left: 2rem;">
                 <i class="bi bi-person-badge me-2"></i>Manager Tags
              </a>
            </li>

            <li>
              <a class="dropdown-item {{ request()->routeIs('master.invoice-flow') ? 'active' : '' }}"
                 href="{{ route('master.invoice-flow') }}" style="padding-left: 2rem;">
                 <i class="bi bi-diagram-3 me-2"></i>Invoice Flow
              </a>
            </li>

            <!-- Travel Employees Master -->
            <li>
              <a class="dropdown-item {{ request()->routeIs('admin.travel-employees.*') || request()->is('admin/master/travel-employees*') ? 'active' : '' }}"
                 href="{{ route('admin.travel-employees.index') }}" style="padding-left: 2rem;">
                 <i class="bi bi-person-vcard me-2"></i>Travel Employees
              </a>
            </li>

            <li><hr class="dropdown-divider"></li>
            
            <!-- Administration Section -->
            <li><h6 class="dropdown-header">Administration</h6></li>
            
            <li>
              <a class="dropdown-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                 href="{{ route('admin.users.index') }}" style="padding-left: 2rem;">
                 <i class="bi bi-people me-2"></i>Users
              </a>
            </li>
            
            <li>
              <a class="dropdown-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                 href="{{ route('admin.roles.index') }}" style="padding-left: 2rem;">
                 <i class="bi bi-shield-lock me-2"></i>Roles & Permissions
              </a>
            </li>
            
            <li><hr class="dropdown-divider"></li>
            
            <!-- General Settings -->
            <li>
              <a class="dropdown-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
                 href="{{ route('admin.settings') }}">
                 <i class="bi bi-sliders me-2"></i>Settings
              </a>
            </li>

          </ul>
        </li>

        <!-- User -->
        @auth
        <li class="nav-item dropdown ms-3">
          <a class="nav-link dropdown-toggle fw-semibold"
             href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
             <i class="bi bi-person-circle me-1"></i>
             {{ ucfirst(strtolower(Auth::user()->name)) }}
          </a>

          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
              <a class="dropdown-item" href="#">
                <i class="bi bi-person me-2"></i>Profile
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item">
                  <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
              </form>
            </li>
          </ul>
        </li>
        @endauth

      </ul>

    </div>
  </div>
</nav>