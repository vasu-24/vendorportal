<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top"
     style="border-bottom: 1.5px solid var(--border-grey); padding-top: 0.35rem; padding-bottom: 0.35rem; min-height: 56px;">
  <div class="container-fluid px-4">

    <!-- Brand -->
    <a class="navbar-brand fw-bold d-flex align-items-center"
       href="{{ route('dashboard') }}"
       style="color: var(--primary-blue); padding:0; margin:0;">

        <img src="{{ asset('image/logo.png') }}"
             alt="Vendor Portal"
             style="height:28px; width:auto; margin-right:8px; margin-bottom:2px;">

        <span class="text-truncate"
              style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            Vendor Portal
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
             Dashboard
          </a>
        </li>

        <!-- Vendors (Dropdown) -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->is('vendors*') ? 'active' : '' }}"
             href="#" id="vendorsDropdown" role="button" data-bs-toggle="dropdown">
             Vendors
          </a>
          <ul class="dropdown-menu" aria-labelledby="vendorsDropdown">
            <li>
              <a class="dropdown-item {{ request()->routeIs('vendors.index') ? 'active' : '' }}"
                 href="{{ route('vendors.index') }}">
                 <i class="bi bi-list-ul me-2"></i>All Vendors
              </a>
            </li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('vendors.create') ? 'active' : '' }}"
                 href="{{ route('vendors.create') }}">
                 <i class="bi bi-person-plus me-2"></i>Add Vendor
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item {{ request()->routeIs('vendors.approval.*') ? 'active' : '' }}"
                 href="{{ route('vendors.approval.queue') }}">
                 <i class="bi bi-check-circle me-2"></i>Approval Queue
              </a>
            </li>
          </ul>
        </li>

        <!-- Invoices -->
        <li class="nav-item">
          <a class="nav-link {{ request()->is('invoices*') ? 'active' : '' }}"
             href="#">
             Invoices
          </a>
        </li>

        <!-- Travel Invoices -->
        <li class="nav-item">
          <a class="nav-link {{ request()->is('travel-invoices*') ? 'active' : '' }}"
             href="#">
             Travel Invoices
          </a>
        </li>

        <!-- Contracts -->
        <li class="nav-item">
          <a class="nav-link {{ request()->is('contracts*') ? 'active' : '' }}"
             href="#">
             Contracts
          </a>
        </li>

        <!-- Bank Payments -->
        <li class="nav-item">
          <a class="nav-link {{ request()->is('bank-payments*') ? 'active' : '' }}"
             href="#">
             Bank Payments
          </a>
        </li>

        <!-- Settings -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ request()->is('settings*') || request()->is('master*') ? 'active' : '' }}"
             href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown">
            Settings
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="settingsDropdown">
            
            <li><h6 class="dropdown-header">Master</h6></li>
            
            <li><a class="dropdown-item {{ request()->routeIs('master.template') ? 'active' : '' }}"
                   href="{{ route('master.template') }}" style="padding-left: 2rem;">
                   <i class="bi bi-file-earmark-text me-2"></i>Template
            </a></li>
            
            <li><hr class="dropdown-divider"></li>
            
            <li><a class="dropdown-item {{ request()->routeIs('settings.general') ? 'active' : '' }}"
                   href="{{ route('settings.general') }}">
                   <i class="bi bi-gear me-2"></i>General Settings
            </a></li>
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
            <li><a class="dropdown-item" href="#">
                <i class="bi bi-person me-2"></i>Profile
            </a></li>
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
