<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HUB GATEWAY - KABUPATEN PROBOLINGGO</title>
    
    <!-- Fonts - Inter (Elegant SaaS) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Base Styles Re-adjustment -->
    <link rel="stylesheet" href="{{ asset('css/min/dashboard.min.css?v=2.1') }}">
    <link rel="stylesheet" href="{{ asset('css/layout-fix.css') }}">
    
    <style>
        /* ELEGANT LIGHT UI (Reference: Professional SaaS) */
        :root {
            --bg-body: #f9fafb;
            --bg-sidebar: #ffffff;
            --primary: #2563eb;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #f1f5f9;
        }

        body {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--bg-body) !important;
            color: var(--text-main) !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            letter-spacing: -0.01em;
        }

        h1, h2, h3, h4, h5, h6, .fw-bold {
            font-family: 'Inter', sans-serif !important;
            color: var(--text-main) !important;
            letter-spacing: -0.02em !important;
        }

        /* Sidebar: High-End White Look */
        .sidebar {
            background-color: var(--bg-sidebar) !important;
            border-right: 1px solid #e5e7eb !important;
            box-shadow: none !important;
        }

        .sidebar .nav-link {
            color: var(--text-muted) !important;
            font-weight: 500;
            font-size: 14px;
            border-radius: 8px;
            margin: 4px 12px;
            padding: 10px 16px !important;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover {
            background-color: #f3f4f6 !important;
            color: var(--text-main) !important;
        }

        .sidebar .nav-link.active {
            background-color: #eff6ff !important;
            color: var(--primary) !important;
            font-weight: 600;
        }

        .sidebar .nav-icon i {
            color: #9ca3af !important;
        }

        .sidebar .nav-link.active .nav-icon i {
            color: var(--primary) !important;
        }

        /* Card Refinement */
        .card {
            border: 1px solid #e5e7eb !important;
            border-radius: 12px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02) !important;
            background-color: #fff !important;
        }

        .card-header {
            background-color: #fff !important;
            border-bottom: 1px solid #e5e7eb !important;
            font-weight: 600;
            padding: 1.25rem !important;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            border-radius: 8px !important;
            font-weight: 600;
            padding: 8px 20px !important;
        }

        .nav-section-title {
            color: #9ca3af !important;
            font-weight: 700 !important;
            font-size: 11px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding-left: 28px !important;
            margin-top: 20px !important;
            margin-bottom: 10px !important;
        }

        .table thead th {
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            color: #6b7280 !important;
            background-color: #f9fafb !important;
            border-bottom: 1px solid #e5e7eb !important;
        }
    </style>
</head>

<body>
    <div class="app-container">
        @include('layouts.partials.sidebar.hub')

        <main class="main-content">
            @include('layouts.partials.header')

            <div class="page-content py-4 px-4">
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm rounded-4" style="background-color: #f0fdf4; color: #166534;">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/dashboard.js?v=2.1') }}"></script>
    @stack('scripts')
</body>
</html>
