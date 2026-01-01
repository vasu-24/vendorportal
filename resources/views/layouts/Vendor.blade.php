<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Portal')</title>

    <x-toast />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* =====================================================
           CSS VARIABLES
           ===================================================== */
        :root {
            --primary-blue: #174081;
            --accent-blue: #3B82F6;
            --white: #FFFFFF;
            --bg-light: #F8FAFC;
            --bg-hover: #F1F5F9;
            --border-grey: #E2E8F0;
            --text-dark: #1E293B;
            --text-grey: #64748B;
            --text-light: #94A3B8;
            --sidebar-width: 260px;
            --sidebar-collapsed: 72px;
            --navbar-height: 56px;
            --transition: 0.2s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-light);
            min-height: 100vh;
            color: var(--text-dark);
        }

        /* =====================================================
           SIDEBAR - MODERN LIGHT STYLE
           ===================================================== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--white);
            border-right: 1px solid var(--border-grey);
            z-index: 1000;
            transition: width var(--transition);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border-grey);
            min-height: 60px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            min-width: 36px;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .brand-text {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-dark);
            white-space: nowrap;
            opacity: 1;
            transition: opacity var(--transition);
        }

        .sidebar.collapsed .brand-text {
            opacity: 0;
        }

        /* Collapse Button */
        .collapse-btn {
            width: 28px;
            height: 28px;
            min-width: 28px;
            border: none;
            background: var(--bg-light);
            border-radius: 6px;
            color: var(--text-grey);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition);
        }

        .collapse-btn:hover {
            background: var(--bg-hover);
            color: var(--text-dark);
        }

        .collapse-btn i {
            transition: transform 0.3s ease;
        }

        .sidebar.collapsed .collapse-btn i {
            transform: rotate(180deg);
        }

        .sidebar.collapsed .collapse-btn {
            margin: 0 auto;
        }

        /* Sidebar Body */
        .sidebar-body {
            flex: 1;
            padding: 12px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Nav Section */
        .nav-section {
            margin-bottom: 20px;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 12px;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            opacity: 1;
            transition: opacity var(--transition);
        }

        .sidebar.collapsed .nav-section-title {
            opacity: 0;
            height: 0;
            padding: 0;
            margin: 0;
        }

        /* Nav Items */
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 2px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: var(--text-grey);
            text-decoration: none;
            border-radius: 8px;
            transition: all var(--transition);
            position: relative;
            overflow: hidden;
        }

        .nav-link:hover {
            background: var(--bg-hover);
            color: var(--text-dark);
        }

        .nav-link.active {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: var(--accent-blue);
            border-radius: 0 3px 3px 0;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            min-width: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .nav-text {
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            opacity: 1;
            transition: opacity var(--transition);
        }

        .sidebar.collapsed .nav-text {
            opacity: 0;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }

        .sidebar.collapsed .nav-link::before {
            display: none;
        }

        /* Nav Badge */
        .nav-badge {
            margin-left: auto;
            padding: 2px 8px;
            background: var(--accent-blue);
            color: white;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
            opacity: 1;
            transition: opacity var(--transition);
        }

        .sidebar.collapsed .nav-badge {
            opacity: 0;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--border-grey);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background: var(--bg-light);
            border-radius: 10px;
            cursor: pointer;
            transition: all var(--transition);
            overflow: hidden;
        }

        .user-card:hover {
            background: var(--bg-hover);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            min-width: 36px;
            background: var(--accent-blue);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            font-weight: 600;
        }

        .user-info {
            flex: 1;
            overflow: hidden;
            opacity: 1;
            transition: opacity var(--transition);
        }

        .sidebar.collapsed .user-info {
            opacity: 0;
            width: 0;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-email {
            font-size: 11px;
            color: var(--text-grey);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar.collapsed .user-card {
            padding: 10px;
            justify-content: center;
        }

        /* =====================================================
           MAIN CONTENT
           ===================================================== */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left var(--transition);
            display: flex;
            flex-direction: column;
        }

        .main-wrapper.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        /* Top Navbar */
        .top-navbar {
            height: var(--navbar-height);
            background: var(--white);
            border-bottom: 1px solid var(--border-grey);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .mobile-toggle {
            display: none;
            width: 36px;
            height: 36px;
            border: none;
            background: var(--bg-light);
            border-radius: 8px;
            font-size: 18px;
            color: var(--text-grey);
            cursor: pointer;
        }

        .mobile-toggle:hover {
            background: var(--bg-hover);
            color: var(--text-dark);
        }

        .page-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: transparent;
            border-radius: 8px;
            color: var(--text-grey);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            position: relative;
            transition: all var(--transition);
        }

        .nav-btn:hover {
            background: var(--bg-hover);
            color: var(--text-dark);
        }

        .nav-btn .badge-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #EF4444;
            border-radius: 50%;
            border: 2px solid var(--white);
        }

        /* =====================================================
           PAGE CONTENT
           ===================================================== */
        .page-content {
            flex: 1;
            padding: 24px;
        }

        /* =====================================================
           FOOTER
           ===================================================== */
        .main-footer {
            padding: 16px 24px;
            background: var(--white);
            border-top: 1px solid var(--border-grey);
            text-align: center;
            font-size: 12px;
            color: var(--text-grey);
        }

        .main-footer a {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 500;
        }

        /* =====================================================
           CARDS
           ===================================================== */
        .card {
            background: var(--white);
            border: 1px solid var(--border-grey);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-grey);
            padding: 16px 20px;
        }

        .card-body {
            padding: 20px;
        }

        /* =====================================================
           BUTTONS
           ===================================================== */
        .btn-primary {
            background-color: var(--accent-blue);
            border: none;
            font-weight: 500;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #2563EB;
        }

        .btn-outline-primary {
            color: var(--accent-blue);
            border-color: var(--border-grey);
            border-radius: 8px;
        }

        .btn-outline-primary:hover {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }

        /* =====================================================
           DROPDOWN
           ===================================================== */
        .dropdown-menu {
            border: 1px solid var(--border-grey);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 8px;
            min-width: 200px;
        }

        .dropdown-item {
            padding: 10px 12px;
            font-size: 13px;
            border-radius: 8px;
            color: var(--text-dark);
        }

        .dropdown-item:hover {
            background: var(--bg-hover);
        }

        .dropdown-item i {
            width: 20px;
            color: var(--text-grey);
        }

        .dropdown-divider {
            margin: 8px 0;
            border-color: var(--border-grey);
        }

        /* =====================================================
           TOOLTIP (Collapsed State)
           ===================================================== */
        .sidebar.collapsed .nav-link {
            position: relative;
        }

        .sidebar.collapsed .nav-link:hover::after {
            content: attr(data-title);
            position: absolute;
            left: calc(100% + 10px);
            top: 50%;
            transform: translateY(-50%);
            background: var(--text-dark);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .sidebar.collapsed .nav-link:hover::before {
            content: '';
            position: absolute;
            left: calc(100% + 6px);
            top: 50%;
            transform: translateY(-50%);
            border: 5px solid transparent;
            border-right-color: var(--text-dark);
        }

        /* =====================================================
           OVERLAY (Mobile)
           ===================================================== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(2px);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }

        /* =====================================================
           RESPONSIVE
           ===================================================== */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
                box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar.show .brand-text,
            .sidebar.show .nav-text,
            .sidebar.show .nav-section-title,
            .sidebar.show .nav-badge,
            .sidebar.show .user-info {
                opacity: 1 !important;
            }

            .collapse-btn {
                display: none;
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .page-content {
                padding: 16px;
            }

            .top-navbar {
                padding: 0 16px;
            }

            .page-title {
                font-size: 14px;
            }
        }

        /* =====================================================
           SCROLLBAR
           ===================================================== */
        .sidebar-body::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-body::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-body::-webkit-scrollbar-thumb {
            background: var(--border-grey);
            border-radius: 4px;
        }

        .sidebar-body::-webkit-scrollbar-thumb:hover {
            background: var(--text-light);
        }




        /* Dropdown Arrow */
.nav-arrow {
    font-size: 12px;
    transition: transform 0.2s;
}
.nav-link[aria-expanded="true"] .nav-arrow {
    transform: rotate(180deg);
}
.sidebar.collapsed .nav-arrow {
    display: none;
}
    </style>
    @stack('styles')
</head>
<body>

    <!-- Overlay -->
    <div class="sidebar-overlay" id="overlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        
        <!-- Header -->
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-logo">
                    <i class="bi bi-building"></i>
                </div>
                <span class="brand-text">Vendor Portal</span>
            </div>
            <button class="collapse-btn" id="collapseBtn" title="Collapse">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="sidebar-body">
            
            <!-- Main Navigation -->
           <!-- Main Navigation -->
<div class="nav-section">
    <div class="nav-section-title">Main Menu</div>
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="{{ route('vendor.dashboard') }}" 
               class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}"
               data-title="Dashboard">
                <span class="nav-icon"><i class="bi bi-grid-1x2"></i></span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
      
     
        <!-- ✅ ADD THIS - Contracts -->
        <li class="nav-item">
            <a href="{{ route('vendor.contracts.index') }}" 
               class="nav-link {{ request()->routeIs('vendor.contracts*') ? 'active' : '' }}"
               data-title="Contracts">
                <span class="nav-icon"><i class="bi bi-file-earmark-check"></i></span>
                <span class="nav-text">Contracts</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="{{ route('vendor.invoices.index') }}"
               class="nav-link {{ request()->routeIs('vendor.invoices*') ? 'active' : '' }}"
               data-title="Invoices">
                <span class="nav-icon"><i class="bi bi-receipt"></i></span>
                <span class="nav-text">Invoices</span>
                <span class="nav-badge">3</span>
            </a>
        </li>


<!-- ✈️ TRAVEL INVOICES - NEW MENU ITEM -->
<li class="nav-item">
    <a href="{{ route('vendor.travel-invoices.index') }}" 
       class="nav-link {{ request()->routeIs('vendor.travel-invoices*') ? 'active' : '' }}"
       data-title="Travel Invoices">
        <span class="nav-icon"><i class="bi bi-airplane"></i></span>
        <span class="nav-text">Travel Invoices</span>
    </a>
</li>


    </ul>
</div>




      <!-- Settings Section -->
<div class="nav-section">
    <div class="nav-section-title">Settings</div>
    <ul class="nav-menu">
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#settingsMenu" role="button" 
               aria-expanded="{{ request()->routeIs('vendor.change-password') ? 'true' : 'false' }}"
               data-title="Settings">
                <span class="nav-icon"><i class="bi bi-gear"></i></span>
                <span class="nav-text">Settings</span>
                <i class="bi bi-chevron-down ms-auto nav-arrow"></i>
            </a>
            <div class="collapse {{ request()->routeIs('vendor.change-password') ? 'show' : '' }}" id="settingsMenu">
                <ul class="nav-menu ps-4">
                    <li class="nav-item">
                        <a href="{{ route('vendor.change-password') }}" 
                           class="nav-link {{ request()->routeIs('vendor.change-password') ? 'active' : '' }}"
                           data-title="Change Password">
                            <span class="nav-icon"><i class="bi bi-key"></i></span>
                            <span class="nav-text">Change Password</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
       
    </ul>
</div>



                  
                </ul>
            </div>

        </div>

        <!-- Footer -->
        <div class="sidebar-footer">
            <div class="dropdown">
                <div class="user-card" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::guard('vendor')->user()->vendor_name, 0, 2)) }}
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ Auth::guard('vendor')->user()->vendor_name }}</div>
                        <div class="user-email">{{ Auth::guard('vendor')->user()->vendor_email }}</div>
                    </div>
                </div>
                <div class="dropdown-menu dropdown-menu-end mb-2">
                    <a class="dropdown-item" href="{{ route('vendor.profile') }}">
                        <i class="bi bi-person me-2"></i>Profile
                    </a>
                    <a class="dropdown-item" href="{{ route('vendor.settings') }}">
                        <i class="bi bi-gear me-2"></i>Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('vendor.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-left me-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper" id="mainWrapper">
        
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center gap-3">
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="navbar-actions">
                <button class="nav-btn" title="Search">
                    <i class="bi bi-search"></i>
                </button>
                <div class="dropdown">
                    <button class="nav-btn" data-bs-toggle="dropdown" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="badge-dot"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Notifications</h6>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-check-circle text-success me-2"></i>Invoice approved
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="bi bi-info-circle text-primary me-2"></i>New message received
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="#">View all notifications</a>
                    </div>
                </div>
                <button class="nav-btn" title="Settings">
                    <i class="bi bi-gear"></i>
                </button>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            © {{ date('Y') }} Vendor Portal — Powered by <a href="#">Kredo</a>
        </footer>
    </div>

<!-- jQuery (MUST be first!) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        // Elements
        const sidebar = document.getElementById('sidebar');
        const mainWrapper = document.getElementById('mainWrapper');
        const overlay = document.getElementById('overlay');
        const collapseBtn = document.getElementById('collapseBtn');
        const mobileToggle = document.getElementById('mobileToggle');

        // Desktop Collapse
        collapseBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainWrapper.classList.toggle('expanded');
            localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
        });

        // Mobile Toggle
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        // Overlay Click
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        // Load saved state (desktop only)
        if (window.innerWidth > 992 && localStorage.getItem('sidebar_collapsed') === 'true') {
            sidebar.classList.add('collapsed');
            mainWrapper.classList.add('expanded');
        }

        // Keyboard shortcut: Ctrl+B to toggle
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                if (window.innerWidth > 992) {
                    collapseBtn.click();
                }
            }
        });

        // CSRF for Axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
    </script>
    
    @stack('scripts')
</body>
</html>