<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Vendor Portal')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Default favicon for new project --}}
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('image/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('image/logo.png') }}">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    @stack('head')
</head>

<body class="d-flex flex-column min-vh-100">
{{-- 
    🔵 Navbar --}}

   
    @include('components.navbar')

      <x-toast />

    {{-- 🔵 Page Content --}}
    <main class="container-fluid py-4 flex-grow-1">
        @yield('content')
    </main>

    {{-- 🔵 Footer --}}
    <footer class="text-center py-3 small mt-auto" 
            style="color: var(--primary-blue); background: #f1f5fa; border-top: 1.5px solid var(--border-grey);">
        <a href="https://kredo.in" target="_blank"
        style="color: var(--primary-blue); font-weight:600; text-decoration:none;">
            &copy; {{ date('Y') }} Vendor Portal — Powered by Kredo
        </a>
    </footer>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Global searchable dropdown init -->
    <script src="{{ asset('js/searchable-dropdown.js') }}"></script>

    @stack('scripts')
    @yield('scripts')

</body>
</html>
