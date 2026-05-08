<header class="h-24 bg-white/50 backdrop-blur-md border-b border-slate-100 px-6 md:px-10 flex items-center justify-between sticky top-0 z-40">
    <!-- SEARCH (Desktop) -->
    <div class="hidden md:flex flex-1 max-w-md">
        <div class="relative w-full">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" placeholder="Cari pesanan atau produk..." class="w-full pl-12 pr-4 py-3 bg-slate-100/50 border-transparent focus:bg-white focus:border-blue-500 focus:ring-0 rounded-2xl text-sm transition-all">
        </div>
    </div>

    <!-- BRAND (Mobile) -->
    <div class="flex lg:hidden items-center gap-3">
        <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white">
            <i class="fas fa-store text-sm"></i>
        </div>
        <h1 class="text-sm font-black text-slate-800">SAE Toko</h1>
    </div>

    <!-- ACTIONS -->
    <div class="flex items-center gap-2 md:gap-4">
        <!-- Notification -->
        <button class="w-12 h-12 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-500 hover:text-blue-600 hover:border-blue-200 transition-all relative">
            <i class="fas fa-bell"></i>
            <span class="absolute top-3 right-3 w-2 h-2 bg-rose-500 rounded-full border-2 border-white"></span>
        </button>

        <!-- Profile / Logout Mobile -->
        <a href="{{ route('portal_warga.logout') }}" class="lg:hidden w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center">
            <i class="fas fa-power-off"></i>
        </a>
    </div>
</header>
