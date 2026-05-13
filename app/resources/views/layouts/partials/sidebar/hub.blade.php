<aside class="sidebar" id="sidebar">
    <div class="sidebar-header py-4 px-4">
        <div class="logo-wrapper d-flex align-items-center">
            <div class="logo-text">
                <div class="logo-title fs-5 fw-bold" style="color: var(--primary); letter-spacing: -1px;">PROBOLINGGO</div>
                <div class="logo-subtitle text-muted" style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Gateway Hub</div>
            </div>
        </div>
    </div>

    <div class="sidebar-nav mt-3">
        <div class="nav-section-title text-uppercase mb-3 px-4">Menu Utama</div>
        
        <a href="{{ route('hub.dashboard') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('hub.dashboard') ? 'active' : '' }}">
            <div class="nav-icon me-3">
                <i class="fas fa-chart-pie"></i>
            </div>
            <span class="nav-text">Overview</span>
        </a>

        <a href="{{ route('hub.districts.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('hub.districts.*') ? 'active' : '' }}">
            <div class="nav-icon me-3">
                <i class="fas fa-server"></i>
            </div>
            <span class="nav-text">Control Center</span>
        </a>

        <a href="#" class="nav-link d-flex align-items-center opacity-50">
            <div class="nav-icon me-3">
                <i class="fas fa-inbox"></i>
            </div>
            <span class="nav-text">Inbox Terpadu</span>
        </a>

        <div class="nav-section-title text-uppercase mb-3 px-4 mt-4">Sistem & Integrasi</div>

        <a href="{{ route('hub.whatsapp.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('hub.whatsapp.*') ? 'active' : '' }}">
            <div class="nav-icon me-3">
                <i class="fab fa-whatsapp"></i>
            </div>
            <span class="nav-text">WhatsApp Hub</span>
        </a>

        <a href="{{ route('hub.api.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('hub.api.*') ? 'active' : '' }}">
            <div class="nav-icon me-3">
                <i class="fas fa-link"></i>
            </div>
            <span class="nav-text">Aplikasi Eksternal</span>
        </a>

        <a href="{{ route('hub.workflow.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('hub.workflow.*') ? 'active' : '' }}">
            <div class="nav-icon me-3">
                <i class="fas fa-project-diagram"></i>
            </div>
            <span class="nav-text">Workflow (n8n)</span>
        </a>
    </div>

    <div class="sidebar-footer p-4 mt-auto">
        <div class="user-card d-flex align-items-center bg-light p-2 rounded-3">
            <div class="avatar-circle me-3 bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 50%; font-size: 12px; font-weight: bold;">
                {{ strtoupper(substr(auth()->user()->nama_lengkap, 0, 2)) }}
            </div>
            <div class="user-details overflow-hidden">
                <div class="user-name fw-bold text-truncate" style="font-size: 12px;">{{ auth()->user()->nama_lengkap }}</div>
            </div>
        </div>
        <form action="{{ route('hub.logout') }}" method="POST" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-outline-dark btn-sm w-100 rounded-3" style="font-size: 11px; font-weight: 600;">Keluar</button>
        </form>
    </div>
</aside>
