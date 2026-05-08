<aside class="hidden lg:flex w-72 flex-col glass-sidebar sticky top-0 h-screen z-50 transition-all duration-300">
    <div class="p-8 flex items-center gap-3">
        <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-blue-200">
            <i class="fas fa-store text-xl"></i>
        </div>
        <div>
            <h1 class="text-base font-black text-slate-800 leading-none">Toko Online SAE</h1>
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Pusat Kendali Bisnis</p>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-2 mt-4 overflow-y-auto no-scrollbar">
        <div class="pb-2 px-4">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Utama</span>
        </div>
        <a href="{{ route('portal_warga.dashboard') }}" class="flex items-center gap-4 px-5 py-4 sidebar-item-active transition-all group">
            <i class="fas fa-th-large text-lg"></i>
            <span class="text-sm font-bold">Home</span>
        </a>
        
        <div class="pt-6 pb-2 px-4">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Manajemen</span>
        </div>
        <a href="#" class="flex items-center gap-4 px-5 py-4 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-2xl transition-all group">
            <i class="fas fa-box-open text-lg opacity-50 group-hover:opacity-100"></i>
            <div>
                <span class="text-sm font-bold block">Produk</span>
                <span class="text-[9px] font-medium opacity-70">Kelola barang jualan</span>
            </div>
        </a>
        <a href="#" class="flex items-center gap-4 px-5 py-4 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-2xl transition-all group">
            <i class="fas fa-shopping-cart text-lg opacity-50 group-hover:opacity-100"></i>
            <div>
                <span class="text-sm font-bold block">Pesanan</span>
                <span class="text-[9px] font-medium opacity-70">Cek orderan masuk</span>
            </div>
        </a>
        <a href="#" class="flex items-center gap-4 px-5 py-4 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-2xl transition-all group">
            <i class="fas fa-wallet text-lg opacity-50 group-hover:opacity-100"></i>
            <div>
                <span class="text-sm font-bold block">Keuangan</span>
                <span class="text-[9px] font-medium opacity-70">Pantau penghasilan</span>
            </div>
        </a>

        <div class="pt-6 pb-2 px-4">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sistem</span>
        </div>
        <a href="#" class="flex items-center gap-4 px-5 py-4 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-2xl transition-all group">
            <i class="fas fa-cog text-lg opacity-50 group-hover:opacity-100"></i>
            <span class="text-sm font-bold">Setelan</span>
        </a>
    </nav>

    <div class="p-6 border-t border-slate-100">
        <!-- GLOBAL STATUS TOGGLE (Pindah dari Dashboard ke Sidebar) -->
        @if(count($allAssets) > 0)
            @php 
                $activeAsset = $allAssets->first(); // Default to first asset for global toggle
                $item = $activeAsset['data'];
                $type = $activeAsset['type'];
            @endphp
            <div class="mb-6 px-4">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 text-center">Status Toko Utama</p>
                <form action="{{ route('portal_warga.status_update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    <input type="hidden" name="operating_hours" value="{{ $item->operating_hours }}">
                    
                    @if($item->is_on_holiday)
                        <input type="hidden" name="is_on_holiday" value="0">
                        <button type="submit" class="w-full py-3 bg-emerald-500 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-emerald-200 flex items-center justify-center gap-2">
                            <i class="fas fa-play text-[8px]"></i> Buka Toko
                        </button>
                    @else
                        <input type="hidden" name="is_on_holiday" value="1">
                        <button type="submit" class="w-full py-3 bg-rose-50 text-rose-500 border border-rose-100 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-rose-100 flex items-center justify-center gap-2">
                            <i class="fas fa-pause text-[8px]"></i> Setel Libur
                        </button>
                    @endif
                </form>
            </div>
        @endif

        <a href="{{ route('portal_warga.logout') }}" class="flex items-center gap-4 px-5 py-4 text-rose-500 hover:bg-rose-50 rounded-2xl transition-all font-bold text-sm">
            <i class="fas fa-power-off"></i>
            <span>Keluar Sesi</span>
        </a>
    </div>
</aside>
