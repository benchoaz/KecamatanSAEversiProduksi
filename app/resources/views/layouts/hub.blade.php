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
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body, .app-container, .main-content {
            font-family: 'Inter', sans-serif !important;
            background-color: var(--bg-body) !important;
            color: var(--text-main) !important;
            background: var(--bg-body) !important;
        }

        /* Sidebar: High-End White Look */
        .sidebar {
            background-color: var(--bg-sidebar) !important;
            background: #ffffff !important;
            border-right: 1px solid var(--border-color) !important;
            box-shadow: none !important;
            color: var(--text-main) !important;
        }

        .sidebar .nav-link {
            color: var(--text-muted) !important;
            font-weight: 600;
            font-size: 13px;
            border-radius: 12px;
            margin: 4px 16px;
            padding: 12px 16px !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar .nav-link:hover {
            background-color: #f1f5f9 !important;
            color: var(--text-main) !important;
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            background-color: #eff6ff !important;
            color: var(--primary) !important;
            box-shadow: 0 4px 12px -2px rgba(37, 99, 235, 0.1);
        }

        .sidebar .nav-icon i {
            color: #94a3b8 !important;
            font-size: 16px;
        }

        .sidebar .nav-link.active .nav-icon i {
            color: var(--primary) !important;
        }

        /* Card Refinement */
        .card {
            border: 1px solid var(--border-color) !important;
            border-radius: 20px !important;
            box-shadow: 0 4px 20px -10px rgba(0,0,0,0.05) !important;
            background-color: #ffffff !important;
            overflow: hidden;
        }

        .card-header {
            background-color: #ffffff !important;
            border-bottom: 1px solid var(--border-color) !important;
            font-weight: 800;
            padding: 1.5rem !important;
            color: var(--text-main) !important;
        }

        /* Header / Navbar */
        .header {
            background-color: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color) !important;
        }

        .nav-section-title {
            color: #94a3b8 !important;
            font-weight: 800 !important;
            font-size: 10px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.1em !important;
            padding-left: 32px !important;
            margin-top: 24px !important;
            margin-bottom: 8px !important;
        }

        /* Inputs & Forms */
        .form-control {
            background-color: #ffffff !important;
            border: 1.5px solid var(--border-color) !important;
            border-radius: 12px !important;
            padding: 12px 16px !important;
            font-size: 14px !important;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1) !important;
        }

        /* Modal / Overlay */
        .modal-content {
            border-radius: 24px !important;
            border: none !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important;
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

    <style>
        .modal-backdrop {
            background-color: rgba(255, 255, 255, 0.6) !important;
            backdrop-filter: blur(8px);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/dashboard.js?v=2.1') }}"></script>
    @stack('modals')
    @stack('scripts')
</body>
</html>
