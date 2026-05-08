@extends('layouts.seller')

@section('title', 'Beranda UMKM')

@section('content')
<div class="space-y-8 pb-10">
    
    <!-- TOP SECTION: Welcome & Quick Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Hero Welcome -->
        <div class="lg:col-span-2 bg-white rounded-[2.5rem] p-8 md:p-10 border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 opacity-50 group-hover:bg-blue-100 transition-colors duration-700"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <span class="px-4 py-1.5 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">Pemilik Toko Terverifikasi</span>
                </div>
                
                <div class="flex items-start justify-between gap-6">
                    <div>
                        <h2 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight">Halo, {{ $userName }}</h2>
                        <p class="text-slate-500 font-medium mt-4 max-w-md leading-relaxed">Selamat datang kembali! Lapak Anda terpantau aman hari ini. Siap untuk melayani pesanan baru?</p>
                        
                        <div class="flex items-center gap-6 mt-8">
                            <div class="flex items-center gap-3 bg-slate-50 px-4 py-2 rounded-2xl border border-slate-100">
                                <i class="fab fa-whatsapp text-emerald-500 text-lg"></i>
                                <span class="text-xs font-bold text-slate-600">+{{ $phone }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-32 h-32 bg-slate-100 rounded-[2.5rem] overflow-hidden border-4 border-white shadow-lg">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($userName) }}&background=0D8ABC&color=fff&size=200" alt="Avatar" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Today Chart (Mockup Style) -->
        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-sm font-black text-slate-800">Penjualan Hari Ini</h3>
                    <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">Update 5 menit lalu</p>
                </div>
                <div class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-tighter">
                    Sedang Buka
                </div>
            </div>
            
            <div class="flex-1 flex flex-col justify-center">
                <div class="text-3xl font-black text-slate-900 mb-2">Rp 129.000</div>
                <div class="text-xs font-bold text-emerald-500 flex items-center gap-1">
                    <i class="fas fa-arrow-up"></i> +12% dari kemarin
                </div>
                <div class="h-24 mt-6">
                    <canvas id="miniChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- MIDDLE SECTION: Assets & Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Lapak & Bisnis List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between px-2">
                <h3 class="font-black text-xl text-slate-800">Lapak & Bisnis Saya</h3>
                <a href="{{ route('umkm_rakyat.create') }}" class="text-[10px] font-black text-blue-600 hover:underline uppercase tracking-widest flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Tambah Baru
                </a>
            </div>

            @if(count($allAssets) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($allAssets as $asset)
                    @php 
                        $item = $asset['data'];
                        $type = $asset['type'];
                        $name = $type === 'umkm' ? $item->nama_usaha : ($type === 'jasa' ? $item->job_title : $item->name);
                        $opStatus = $item->operational_status;
                        $manageUrl = $type === 'umkm' ? route('umkm_rakyat.manage.seller.dashboard', $item->manage_token) : route('portal_warga.bridge.jasa', $item->id);
                        $products = $type === 'umkm_local' ? explode(',', $item->product) : [];
                    @endphp
                    
                    <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100 hover:shadow-xl hover:shadow-blue-900/5 transition-all group">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 {{ $opStatus['bg'] }} {{ $opStatus['text'] }} rounded-2xl flex items-center justify-center text-2xl shadow-inner border-2 border-white">
                                <i class="fas {{ $opStatus['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-lg font-black text-slate-800 leading-tight truncate">{{ $name }}</h4>
                                <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $opStatus['bg'] }} {{ $opStatus['text'] }} uppercase tracking-wider">{{ $opStatus['label'] }}</span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <a href="{{ $manageUrl }}" class="w-full flex items-center justify-center gap-3 py-4 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all">
                                <i class="fas fa-box-open opacity-50 text-base"></i> Atur Toko & Produk
                            </a>
                        </div>

                        <a href="{{ match($type) { 'umkm' => route('umkm_rakyat.show', $item->slug), 'jasa' => route('economy.show', $item->id), 'umkm_local' => route('economy.produk.show', $item->id), default => '#' } }}" target="_blank" class="text-[9px] font-bold text-slate-400 hover:text-blue-600 uppercase tracking-widest flex items-center justify-center gap-2 border-t border-slate-50 pt-4">
                            <i class="fas fa-eye text-[10px]"></i> Lihat Lapak Publik
                        </a>
                    </div>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-[2.5rem] p-12 text-center border-2 border-dashed border-slate-100">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <i class="fas fa-store-alt-slash text-3xl"></i>
                </div>
                <h4 class="text-lg font-black text-slate-800 mb-2">Belum Ada Lapak</h4>
                <p class="text-xs text-slate-500 font-medium mb-8">Daftarkan usaha Anda sekarang.</p>
                <a href="{{ route('umkm_rakyat.create') }}" class="inline-flex py-4 px-8 bg-blue-600 text-white rounded-2xl font-black text-xs shadow-lg shadow-blue-200">
                    Daftar UMKM Baru
                </a>
            </div>
            @endif
        </div>

        <!-- Order Status / Distribution -->
        <div class="space-y-6">
            <h3 class="font-black text-xl text-slate-800 px-2">Status Pesanan</h3>
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm">
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
                
                <div class="mt-8 space-y-4">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                            <span class="text-slate-500">Menunggu</span>
                        </div>
                        <span class="text-slate-800">12 Pesanan</span>
                    </div>
                    <div class="flex items-center justify-between text-xs font-bold">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                            <span class="text-slate-500">Diproses</span>
                        </div>
                        <span class="text-slate-800">8 Pesanan</span>
                    </div>
                    <div class="flex items-center justify-between text-xs font-bold">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-emerald-500 rounded-full"></div>
                            <span class="text-slate-500">Selesai</span>
                        </div>
                        <span class="text-slate-800">45 Pesanan</span>
                    </div>
                </div>
            </div>

            <!-- Promotion Card -->
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-[2.5rem] p-8 text-white shadow-xl shadow-slate-900/10">
                <h4 class="text-lg font-black mb-2">Mau Dagangan Laris?</h4>
                <p class="text-[10px] font-medium text-slate-400 leading-relaxed mb-6">Gunakan fitur promosi untuk menampilkan produk Anda di halaman utama portal.</p>
                <button class="w-full py-4 bg-white/10 hover:bg-white/20 border border-white/20 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all">
                    Pelajari Promosi
                </button>
            </div>
        </div>

    </div>
</div>
@endsection

@section('extra_js')
<script>
    // Mini Chart for Sales
    const miniCtx = document.getElementById('miniChart').getContext('2d');
    new Chart(miniCtx, {
        type: 'line',
        data: {
            labels: ['08', '10', '12', '14', '16', '18', '20'],
            datasets: [{
                data: [10, 40, 20, 80, 50, 90, 70],
                borderColor: '#10b981',
                borderWidth: 3,
                tension: 0.4,
                pointRadius: 0,
                fill: true,
                backgroundColor: (context) => {
                    const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 100);
                    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
                    gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
                    return gradient;
                }
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: ['2024', '2025', '2026'],
            datasets: [
                {
                    label: 'Pending',
                    data: [10, 12, 20],
                    backgroundColor: '#2563eb',
                    borderRadius: 10
                },
                {
                    label: 'Processed',
                    data: [8, 15, 12],
                    backgroundColor: '#f59e0b',
                    borderRadius: 10
                },
                {
                    label: 'Shipped',
                    data: [5, 10, 8],
                    backgroundColor: '#10b981',
                    borderRadius: 10
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, grid: { borderDash: [5, 5] }, ticks: { display: false } }
            }
        }
    });
</script>
@endsection
