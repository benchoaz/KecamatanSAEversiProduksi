<aside class="sidebar" id="sidebar">
    <div class="sidebar-header py-4 px-4">
        <div class="d-flex align-items-center">
            <div class="logo-icon me-3">
                <img src="{{ asset('images/hub/logo-probolinggo.png') }}" alt="Logo" style="width: 40px;">
            </div>
            <div class="logo-text">
                <div class="logo-title fs-5">PROBOLINGGO</div>
                <div class="logo-subtitle text-xs">GATEWAY HUB</div>
            </div>
        </div>
    </div>

    <div class="sidebar-nav px-3 mt-3">
        <div class="nav-section-title text-uppercase text-xs opacity-50 mb-3 px-3">Administrasi & Otoritas</div>
        
        <a href="{{ route('hub.districts.index') }}" class="nav-link d-flex align-items-center py-3 px-3 mb-2 {{ request()->routeIs('hub.districts.*') ? 'active' : '' }}" style="text-decoration: none; border-radius: 12px;">
            <div class="nav-icon me-3">
                <i class="fas fa-city fs-5"></i>
            </div>
            <span class="nav-text fw-medium">Pusat Kendali</span>
        </a>

        <div class="nav-section-title text-uppercase text-xs opacity-50 mb-3 px-3 mt-4">Monitoring Global</div>

        <a href="#" class="nav-link d-flex align-items-center py-3 px-3 mb-2" style="text-decoration: none; border-radius: 12px; opacity: 0.6;">
            <div class="nav-icon me-3">
                <i class="fas fa-inbox fs-5"></i>
            </div>
            <span class="nav-text fw-medium">Inbox Terpadu</span>
        </a>

        <a href="#" class="nav-link d-flex align-items-center py-3 px-3 mb-2" style="text-decoration: none; border-radius: 12px; opacity: 0.6;">
            <div class="nav-icon me-3">
                <i class="fas fa-bullhorn fs-5"></i>
            </div>
            <span class="nav-text fw-medium">Pengaduan Wilayah</span>
        </a>

        <a href="#" class="nav-link d-flex align-items-center py-3 px-3 mb-2" style="text-decoration: none; border-radius: 12px; opacity: 0.6;">
            <div class="nav-icon me-3">
                <i class="fas fa-chart-pie fs-5"></i>
            </div>
            <span class="nav-text fw-medium">Statistik 24 Kec.</span>
        </a>

        <div class="nav-section-title text-uppercase text-xs opacity-50 mb-3 px-3 mt-4">Integrasi & API</div>

        <a href="{{ route('hub.whatsapp.index') }}" class="nav-link d-flex align-items-center py-3 px-3 mb-2 {{ request()->routeIs('hub.whatsapp.*') ? 'active' : '' }}" style="text-decoration: none; border-radius: 12px;">
            <div class="nav-icon me-3">
                <i class="fab fa-whatsapp fs-5 text-success"></i>
            </div>
            <span class="nav-text fw-medium">WhatsApp Gateway</span>
        </a>

        <a href="#" class="nav-link d-flex align-items-center py-3 px-3 mb-2" style="text-decoration: none; border-radius: 12px; opacity: 0.6;">
            <div class="nav-icon me-3">
                <i class="fas fa-project-diagram fs-5 text-info"></i>
            </div>
            <span class="nav-text fw-medium">Workflow (n8n)</span>
        </a>

        <a href="#" class="nav-link d-flex align-items-center py-3 px-3 mb-2" style="text-decoration: none; border-radius: 12px; opacity: 0.6;">
            <div class="nav-icon me-3">
                <i class="fas fa-link fs-5 text-warning"></i>
            </div>
            <span class="nav-text fw-medium">External API</span>
        </a>

        <div class="nav-section-title text-uppercase text-xs opacity-50 mb-3 px-3 mt-4">Sistem</div>

        <a href="#" class="nav-link d-flex align-items-center py-3 px-3 mb-2" style="text-decoration: none; border-radius: 12px; opacity: 0.6;">
            <div class="nav-icon me-3">
                <i class="fas fa-users-cog fs-5"></i>
            </div>
            <span class="nav-text fw-medium">Admin Kabupaten</span>
        </a>
    </div>

    <div class="sidebar-footer p-4 mt-auto">
        <div class="user-card p-3 rounded-4 bg-white bg-opacity-10">
            <div class="d-flex align-items-center">
                <div class="user-avatar me-3">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-user-shield text-white"></i>
                    </div>
                </div>
                <div class="user-info overflow-hidden">
                    <div class="user-name text-white fw-bold text-truncate">{{ auth()->user()->nama_lengkap }}</div>
                    <div class="user-role text-xs text-white text-opacity-50">Super Admin Hub</div>
                </div>
            </div>
            <form action="{{ route('hub.logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm w-100 rounded-3">
                    <i class="fas fa-power-off me-2"></i> Keluar
                </button>
            </form>
        </div>
    </div>
</aside>
