<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <div class="logo-icon bg-transparent text-white">
                @if(appProfile()->logo_path)
                    <img src="{{ asset('storage/' . appProfile()->logo_path) }}" class="img-fluid"
                        style="max-height: 48px; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.2));">
                @else
                    <i class="fas fa-landmark"></i>
                @endif
            </div>
            <div class="logo-text">
                <span class="logo-title fw-bold text-uppercase">DASHBOARD</span>
                <span class="logo-subtitle tracking-wider">{{ strtoupper(appProfile()->full_region_name) }}</span>
            </div>
        </div>
        <button class="sidebar-close" id="sidebarClose"><i class="fas fa-times"></i></button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <span class="nav-section-title">ADMINISTRASI & OTORITAS</span>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="{{ route('kecamatan.dashboard') }}"
                        class="nav-link {{ request()->is('kecamatan/dashboard*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-layer-group"></i></span>
                        <span class="nav-text">Beranda Pusat</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kecamatan.pelayanan.layanan.index') }}"
                        class="nav-link {{ request()->is('kecamatan/pelayanan/layanan*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-concierge-bell"></i></span>
                        <span class="nav-text">Daftar Layanan (Node)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kecamatan.users.index') }}"
                        class="nav-link {{ request()->is('kecamatan/users*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-users-cog"></i></span>
                        <span class="nav-text">Manajemen User (Advanced)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('filament.admin.pages.dashboard') }}"
                        class="nav-link {{ request()->is('admin*') ? 'active' : '' }}">
                        <span class="nav-icon"><i class="fas fa-tools"></i></span>
                        <span class="nav-text">Panel Manajemen (Baru)</span>
                    </a>
                </li>

            </ul>
        </div>





        {{-- Dynamic Menus Section --}}
        @if(isset($dynamicMenus) && $dynamicMenus->count() > 0)
            <div class="nav-section">
                <span class="nav-section-title">MODUL OTORISASI (DINAMIS)</span>
                <ul class="nav-menu">
                    @foreach($dynamicMenus as $menu)
                        @if($menu->subMenus->count() > 0)
                            <li class="nav-item has-submenu {{ request()->is($menu->slug.'*') ? 'open' : '' }}">
                                <a href="javascript:void(0)" class="nav-link submenu-toggle {{ request()->is($menu->slug.'*') ? 'active' : '' }}">
                                    <span class="nav-icon"><i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i></span>
                                    <span class="nav-text">{{ $menu->name }}</span>
                                    <span class="ms-auto small"><i class="fas fa-chevron-right nav-arrow"></i></span>
                                </a>
                                <ul class="nav-submenu">
                                    @foreach($menu->subMenus as $sub)
                                        <li class="nav-submenu-item">
                                            <a href="{{ $sub->route_name ? route($sub->route_name) : '#' }}"
                                               class="nav-sublink {{ request()->is($sub->slug.'*') ? 'active' : '' }}">
                                                <i class="fas fa-dot-circle me-2 small"></i> {{ $sub->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href="{{ $menu->slug ? '#'.$menu->slug : '#' }}"
                                   class="nav-link {{ request()->is($menu->slug.'*') ? 'active' : '' }}">
                                    <span class="nav-icon"><i class="{{ $menu->icon ?? 'fas fa-circle' }}"></i></span>
                                    <span class="nav-text">{{ $menu->name }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif


    </nav>

    <div class="sidebar-footer">
        <div class="user-card border-0 shadow-sm" style="background: rgba(255,255,255,0.03);">
            <div class="user-avatar bg-brand-600 text-white"><i class="fas fa-user-tie"></i></div>
            <div class="user-info">
                <span class="user-name text-truncate text-white">{{ auth()->user()->nama_lengkap }}</span>
                <span
                    class="user-role small text-muted text-uppercase tracking-tighter">{{ optional(auth()->user()->role)->nama_role }}</span>
            </div>
        </div>

        <!-- Logout Button -->
        <a href="#"
            class="btn btn-danger btn-sm w-100 rounded-3 d-flex align-items-center justify-content-center gap-2 py-2 shadow-sm mt-2"
            onclick="event.preventDefault(); if(confirm('Konfirmasi Keluar\n\nApakah Anda yakin ingin keluar dari aplikasi?')) document.getElementById('logout-form').submit();"
            style="font-size: 13px;">
            <i class="fas fa-power-off"></i>
            <span>Keluar Aplikasi</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</aside>