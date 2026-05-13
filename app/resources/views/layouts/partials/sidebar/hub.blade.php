<aside class="sidebar" id="sidebar">
    <div class="sidebar-header py-4 px-4 border-bottom">
        <div class="logo-text">
            <div class="logo-title fs-5" style="color: #000; font-weight: 800;">PROBOLINGGO</div>
            <div class="logo-subtitle text-xs text-muted" style="font-size: 10px; letter-spacing: 2px;">GATEWAY HUB</div>
        </div>
    </div>

    <div class="sidebar-nav mt-3">
        <div class="nav-section-title text-uppercase mb-2 px-4" style="font-size: 10px; color: #888; font-weight: bold;">Menu Utama</div>
        
        <a href="{{ route('hub.districts.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('hub.districts.*') ? 'active' : '' }}">
            <i class="fas fa-city me-3" style="width: 20px;"></i>
            <span>Pusat Kendali</span>
        </a>

        <a href="#" class="nav-link d-flex align-items-center" style="opacity: 0.5;">
            <i class="fas fa-inbox me-3" style="width: 20px;"></i>
            <span>Inbox Terpadu</span>
        </a>

        <a href="#" class="nav-link d-flex align-items-center" style="opacity: 0.5;">
            <i class="fas fa-chart-pie me-3" style="width: 20px;"></i>
            <span>Statistik Global</span>
        </a>

        <div class="nav-section-title text-uppercase mb-2 px-4 mt-4" style="font-size: 10px; color: #888; font-weight: bold;">Integrasi</div>

        <a href="{{ route('hub.whatsapp.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('hub.whatsapp.*') ? 'active' : '' }}">
            <i class="fab fa-whatsapp me-3" style="width: 20px;"></i>
            <span>WhatsApp Hub</span>
        </a>

        <a href="{{ route('hub.api.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('hub.api.*') ? 'active' : '' }}">
            <i class="fas fa-link me-3" style="width: 20px;"></i>
            <span>Aplikasi Eksternal</span>
        </a>

        <a href="{{ route('hub.workflow.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('hub.workflow.*') ? 'active' : '' }}">
            <i class="fas fa-project-diagram me-3" style="width: 20px;"></i>
            <span>Workflow (n8n)</span>
        </a>
    </div>

    <div class="sidebar-footer p-4 border-top mt-auto">
        <div class="user-info mb-3">
            <div class="user-name fw-bold" style="font-size: 13px;">{{ auth()->user()->nama_lengkap }}</div>
            <div class="user-role text-muted" style="font-size: 11px;">Super Admin Kabupaten</div>
        </div>
        <form action="{{ route('hub.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-dark btn-sm w-100" style="font-size: 11px;">
                Log Out
            </button>
        </form>
    </div>
</aside>
