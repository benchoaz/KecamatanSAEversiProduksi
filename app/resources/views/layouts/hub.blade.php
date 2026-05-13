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
        /* Minimalist Dashboard Styling (Focus Mode) */
        :root {
            --bg-color: #ffffff;
            --text-main: #000000;
            --text-muted: #555555;
            --border-color: #dddddd;
            --accent-color: #000000;
        }

        body {
            background-color: var(--bg-color) !important;
            color: var(--text-main) !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 14px;
        }

        .sidebar {
            background-color: var(--bg-color) !important;
            border-right: 1px solid var(--border-color) !important;
            width: 250px;
        }

        .sidebar .nav-link {
            color: var(--text-main) !important;
            border-bottom: 1px solid transparent;
            border-radius: 0 !important;
            padding: 12px 20px !important;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #f0f0f0 !important;
            font-weight: bold;
        }

        .main-content {
            background-color: var(--bg-color) !important;
        }

        .card {
            border: 1px solid var(--border-color) !important;
            border-radius: 4px !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        .card-header {
            background-color: #fafafa !important;
            border-bottom: 1px solid var(--border-color) !important;
            font-weight: bold;
            color: #000 !important;
        }

        h1, h2, h3, h4, h5, h6 {
            color: #000 !important;
            font-weight: 700 !important;
        }

        .table {
            color: #000 !important;
        }

        .table thead th {
            background-color: #f5f5f5 !important;
            color: #000 !important;
            border-bottom: 2px solid var(--border-color) !important;
            text-transform: none !important;
            letter-spacing: 0 !important;
        }

        .badge {
            border: 1px solid #000;
            background: transparent !important;
            color: #000 !important;
            border-radius: 2px !important;
        }

        .btn-primary {
            background-color: #000 !important;
            border-color: #000 !important;
            color: #fff !important;
            border-radius: 4px !important;
        }

        .btn-outline-primary {
            color: #000 !important;
            border-color: #000 !important;
        }

        .form-control {
            border-radius: 2px !important;
            border: 1px solid var(--border-color) !important;
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
