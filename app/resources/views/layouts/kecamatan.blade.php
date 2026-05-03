<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Monitoring') - {{ appProfile()->region_name }}</title>
    @if(appProfile()->logo_path)
        <link rel="icon" href="{{ asset('storage/' . appProfile()->logo_path) }}" type="image/png">
    @endif

    <!-- Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/min/dashboard.min.css?v=2.1') }}">
    <link rel="stylesheet" href="{{ asset('css/buttons-fix.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout-fix.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-fix.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-premium.css') }}">
    <style>
        .ticker-move-internal {
            display: inline-block;
            white-space: nowrap;
            padding-right: 100%;
            animation: ticker 30s linear infinite;
        }
        .hover\:pause-animation:hover { animation-play-state: paused; }
        @keyframes ticker {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        /* Background & Sidebar Putih - User Request */
        body, .app-container, .main-content, .page-content, .header, .premium-welcome, .welcome-banner {
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #334155 !important;
        }
        .premium-welcome h1, .premium-welcome h2, .premium-welcome p, 
        .premium-welcome span, .premium-welcome .text-slate-400,
        .premium-welcome .text-slate-200, .premium-welcome .text-white {
            color: #1e293b !important;
        }
        .sidebar {
            background-color: #ffffff !important;
            border-right: 1px solid #e2e8f0 !important;
        }
        .sidebar-header, .sidebar-nav, .sidebar-footer {
            background-color: #ffffff !important;
        }
        .logo-title, .logo-subtitle, .nav-text, .nav-section-title, .user-name, .logo-icon i {
            color: #1e293b !important;
        }
        .logo-icon {
            background: transparent !important;
            background-color: transparent !important;
            box-shadow: none !important;
        }
        .nav-icon i {
            color: #64748b !important;
        }
        .nav-link:hover .nav-text, .nav-link:hover .nav-icon i {
            color: #0f172a !important;
        }
        .nav-section-title {
            opacity: 0.7;
            font-weight: 800;
        }
        .user-card {
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
        }
        .user-name {
            color: #1e293b !important;
            font-weight: 700;
        }
        /* Active Link adjustment */
        .nav-link.active {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            border-radius: 12px;
        }
        .nav-link.active .nav-icon i, .nav-link.active .nav-text {
            color: #1e293b !important;
        }
        /* Hide decorative background blobs */
        .premium-welcome::before, 
        .premium-welcome .bg-info, 
        .premium-welcome .bg-primary,
        .premium-welcome .position-absolute.top-0.right-0.w-100.h-100.opacity-10,
        .premium-welcome .position-absolute.end-0.bottom-0.opacity-5 {
            display: none !important;
        }
        /* Adjust border */
        .premium-welcome, .welcome-banner {
            border: 1px solid #e2e8f0 !important;
        }
        .alert-emerald {
            background-color: var(--success-50);
            border: 1px solid rgba(22, 163, 74, 0.1) !important;
        }
        .icon-box-emerald {
            background-color: var(--success-500);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(34, 197, 94, 0.2);
        }
        .text-emerald-900 { color: #064e3b; }
        .text-emerald-700 { color: #047857; }
        .alert-danger {
            background-color: var(--danger-50);
            border: 1px solid rgba(239, 68, 68, 0.1) !important;
        }
        .icon-box-danger {
            background-color: var(--danger-500);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }
        /* Custom Scrollbar Lebih Tebal & Permanen - User Request */
        ::-webkit-scrollbar {
            width: 12px !important;
            height: 12px !important;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9 !important;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #94a3b8 !important; /* Abu-abu yang lebih jelas */
            border-radius: 10px;
            border: 2px solid #f1f5f9; /* Spacing effect */
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b !important; /* Sedikit lebih gelap saat di hover */
        }
        /* Untuk Firefox */
        * {
            scrollbar-width: auto !important;
            scrollbar-color: #94a3b8 #f1f5f9 !important;
        }

        /* Submenu Text & Contrast Fix (White Theme) */
        .nav-submenu {
            background-color: #f8fafc !important; /* Background terang untuk submenu */
            border-radius: 10px;
            margin: 5px 12px !important;
            padding-left: 0 !important;
            border: 1px solid #e2e8f0;
        }
        .nav-sublink {
            color: #475569 !important; /* Tulisan abu gelap agar kontras di BG putih */
            padding: 10px 15px 10px 48px !important;
            font-weight: 500;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-sublink:hover {
            color: #0f172a !important;
            background-color: #f1f5f9 !important;
            transform: translateX(3px);
        }
        .nav-sublink.active {
            color: var(--sidebar-active) !important;
            background-color: #f0f7ea !important;
            font-weight: 700;
        }
        .nav-sublink i {
            color: #94a3b8 !important;
            margin-right: 10px;
        }
        .nav-sublink.active i {
            color: var(--sidebar-active) !important;
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="app-container">
        <!-- Sidebar Khusus Kecamatan (Review & Kontrol) -->
        @include('layouts.partials.sidebar.kecamatan')

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            @include('layouts.partials.header')

            <!-- Page Content -->
            <div class="page-content">
                @if(isset($internalAnnouncements) && $internalAnnouncements->count() > 0)
                    <div class="px-4 mt-3">
                        @foreach($internalAnnouncements as $ann)
                            @if($ann->display_mode == 'ticker')
                                <div class="bg-blue-50/50 border border-blue-100 rounded-3 overflow-hidden py-1 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="px-3 border-end border-blue-100 text-[10px] fw-bold text-blue-500 uppercase tracking-tighter">
                                            INFO KECAMATAN
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden whitespace-nowrap">
                                            <div class="ticker-move-internal hover:pause-animation">
                                                <span class="text-slate-600 small fw-medium px-3">
                                                    {{ $ann->content }} &nbsp;&bull;&nbsp; {{ $ann->title }}
                                                </span>
                                                <span class="text-slate-600 small fw-medium px-3">
                                                    {{ $ann->content }} &nbsp;&bull;&nbsp; {{ $ann->title }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert {{ $ann->priority == 'important' ? 'alert-danger border-0 shadow-sm' : 'alert-primary border-0 shadow-sm' }} d-flex align-items-center py-2 px-3 rounded-4 mb-3">
                                    <i class="fas {{ $ann->priority == 'important' ? 'fa-exclamation-circle' : 'fa-info-circle' }} me-2"></i>
                                    <div class="flex-grow-1">
                                        <strong class="small">{{ $ann->title }}:</strong> 
                                        <span class="small">{{ $ann->content }}</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success mt-4 mx-4">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/dashboard.js?v=2.1') }}"></script>
    @stack('scripts')

    {{-- Modal Section - Rendered outside of stacked context --}}
    @yield('modal')
</body>

</html>