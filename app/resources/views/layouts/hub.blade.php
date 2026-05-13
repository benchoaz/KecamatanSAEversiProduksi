<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HUB GATEWAY - KABUPATEN PROBOLINGGO</title>
    
    <!-- Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/min/dashboard.min.css?v=2.1') }}">
    <link rel="stylesheet" href="{{ asset('css/layout-fix.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-premium.css') }}">
    
    <style>
        /* Custom Styling for Hub Gateway */
        body {
            background-color: #ffffff !important;
            color: #000000 !important;
        }
        .main-content, .page-content, .container-fluid {
            background-color: #ffffff !important;
        }
        .card {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: none !important; /* Remove shadows for cleaner look */
            color: #000000 !important;
        }
        h1, h2, h3, h4, h5, h6, p, span, div, td, th {
            color: #000000 !important;
        }
        .text-muted, .text-gray-800, .text-gray-600 {
            color: #334155 !important; /* Slightly softer black for secondary text */
        }
        .sidebar {
            background-color: #ffffff !important;
            border-right: 1px solid #e2e8f0 !important;
            color: #000000 !important;
        }
        .sidebar .nav-link {
            color: #475569 !important;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #000000 !important;
            background: #f1f5f9 !important;
            border-radius: 12px;
        }
        .sidebar .nav-icon i {
            color: #64748b !important;
        }
        .sidebar .nav-link.active .nav-icon i {
            color: #2563eb !important;
        }
        .logo-title {
            color: #000000 !important;
            font-weight: 800 !important;
            letter-spacing: 1px;
        }
        .logo-subtitle {
            color: #64748b !important;
        }
        .user-card {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
        }
        .user-name {
            color: #000000 !important;
        }
        .user-role {
            color: #64748b !important;
        }
    </style>
</head>

<body>
    <div class="app-container">
        <!-- Sidebar Khusus Kabupaten -->
        @include('layouts.partials.sidebar.hub')

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            @include('layouts.partials.header')

            <!-- Page Content -->
            <div class="page-content py-4 px-4">
                @if(session('success'))
                    <div class="alert alert-success shadow-sm rounded-4">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/dashboard.js?v=2.1') }}"></script>
    @stack('scripts')
</body>
</html>
