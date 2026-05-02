
<div class="navbar bg-white/95 backdrop-blur-xl shadow-sm px-3 md:px-6 py-2 sticky top-0 z-50 border-b border-slate-100">
    <div class="navbar-start flex items-center">
        <!-- Mobile Drawer Toggle -->
        <div class="dropdown lg:hidden mr-2">
            <label tabindex="0" class="btn btn-ghost btn-circle text-teal-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
                </svg>
            </label>
            <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow-xl bg-white rounded-2xl w-64 border border-teal-50">
                <div class="px-4 py-3 mb-2 border-b border-slate-50">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-teal-600">Menu Navigasi</span>
                </div>
                <li><a href="/" class="py-3 font-bold {{ request()->is('/') ? 'text-teal-600 bg-teal-50' : 'text-slate-600' }}">Beranda</a></li>
                
                @if(appProfile()->is_menu_pelayanan_active)
                <li><a href="{{ request()->is('/') ? '#layanan' : '/#layanan' }}" class="py-3 font-bold text-slate-600">Layanan</a></li>
                @endif
                
                @if(appProfile()->is_menu_umkm_active)
                <li><a href="{{ route('economy.index') }}" class="py-3 font-bold {{ request()->is('ekonomi*') ? 'text-teal-600 bg-teal-50' : 'text-slate-600' }}">Pusat Ekonomi</a></li>
                @endif
                
                @if(appProfile()->is_menu_statistik_active)
                <li><a href="{{ route('landing.statistik.index') }}" class="py-3 font-bold {{ request()->is('statistik*') ? 'text-teal-600 bg-teal-50' : 'text-slate-600' }}">Statistik</a></li>
                @endif
                
                @if(appProfile()->is_menu_berita_active)
                <li><a href="{{ request()->is('/') ? '#berita' : '/#berita' }}" class="py-3 font-bold text-slate-600">Berita</a></li>
                @endif
                
                <div class="mt-4 pt-4 border-t border-slate-50">
                    @if(appProfile()->is_menu_pengaduan_active)
                    <button onclick="document.getElementById('complaintModal').showModal()" class="btn btn-sm btn-error btn-outline w-full rounded-xl">
                        <i class="fas fa-exclamation-circle"></i> Lapor!
                    </button>
                    @endif
                </div>
            </ul>
        </div>

        <a href="/" class="flex items-center gap-3">
            @if(appProfile()->logo_path && Storage::disk('public')->exists(appProfile()->logo_path))
                <img src="{{ asset('storage/' . appProfile()->logo_path) }}"
                    alt="Logo {{ appProfile()->region_name }}"
                    class="h-10 md:h-14 lg:h-16 w-auto object-contain flex-shrink-0 transition-transform hover:scale-105">
            @else
                <div
                    class="w-12 h-12 bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                    <img src="{{ asset('assets/images/logo-garuda.png') }}" alt="Logo Garuda" class="h-8 object-contain">
                </div>
            @endif
            <div>
                <div class="text-[10px] md:text-xs font-black text-[#003366] uppercase tracking-wide leading-tight">
                    {{ strtoupper(appProfile()->full_region_name) }}
                </div>
                <div class="text-[8px] md:text-[10px] text-slate-500 font-medium tracking-wider">{{ appProfile()->app_name }}</div>
            </div>
        </a>
    </div>
    <div class="navbar-center hidden lg:flex">
        <ul class="menu menu-horizontal px-1 gap-1">
            <li><a href="/"
                    class="text-sm font-medium {{ request()->is('/') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-teal-600 hover:bg-teal-50' }} rounded-lg">Beranda</a>
            </li>
            <li><a href="{{ request()->is('/') ? '#layanan' : '/#layanan' }}"
                    class="text-sm font-medium text-gray-600 hover:text-teal-600 hover:bg-teal-50 rounded-lg">Layanan</a>
            </li>
            <li><a href="{{ route('economy.index') }}"
                    class="text-sm font-medium {{ request()->is('ekonomi*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-teal-600 hover:bg-teal-50' }} rounded-lg">Pusat
                    Ekonomi</a>
            </li>
            <li>
                <a href="{{ route('landing.statistik.index') }}"
                    class="text-sm font-medium {{ request()->is('statistik*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-teal-600 hover:bg-teal-50' }} rounded-lg">
                    Statistik
                </a>
            </li>
            <li><a href="{{ request()->is('/') ? '#berita' : '/#berita' }}"
                    class="text-sm font-medium text-gray-600 hover:text-teal-600 hover:bg-teal-50 rounded-lg">Berita</a>
            </li>
        </ul>
    </div>
    <div class="navbar-end gap-2">

        <button onclick="document.getElementById('complaintModal').showModal()"
            class="btn btn-sm bg-rose-100/50 hover:bg-rose-500 text-rose-600 hover:text-white border-0 hidden lg:flex rounded-lg px-5 font-bold shadow-sm transition-colors">
            <i class="fas fa-exclamation-circle mr-1"></i> Lapor!
        </button>
        <div class="hidden lg:block w-px h-6 bg-slate-200 mx-1"></div>
        
        <!-- Mobile Portal Admin Icon -->
        <a href="{{ route('login') }}"
            class="lg:hidden btn btn-ghost btn-circle text-teal-600 transition-all active:scale-90">
            <i class="fas fa-fingerprint text-xl"></i>
        </a>

        <!-- Desktop Portal Admin Button -->
        <a href="{{ route('login') }}"
            class="hidden lg:flex btn btn-sm bg-teal-600 hover:bg-teal-700 text-white border-0 rounded-xl px-6 font-black shadow-lg shadow-teal-900/10 transition-all">
            Portal Admin
        </a>
    </div>
</div>