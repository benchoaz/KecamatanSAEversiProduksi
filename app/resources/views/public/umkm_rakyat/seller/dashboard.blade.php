<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Centre - {{ $umkm->nama_usaha }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>

<body class="bg-[#F8FAFC] min-h-screen pb-24">

    <!-- Header / Navigation -->
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-40 border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('portal_warga.dashboard') }}" class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-sm font-black text-slate-800 tracking-tight leading-none uppercase flex items-center gap-2">
                        Seller Centre
                        <span class="text-[9px] font-black px-2 py-0.5 rounded-full {{ $metrics['performance_score'] >= 80 ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                            Skor: {{ $metrics['performance_score'] }}
                        </span>
                    </h1>
                    <p class="text-[10px] font-bold text-blue-600 mt-1 uppercase tracking-widest">{{ $umkm->nama_usaha }}</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden md:flex flex-col text-right">
                    <span class="text-xs font-black text-slate-700">{{ $umkm->nama_pemilik }}</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase">Pemilik Toko</span>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-100 border-2 border-white shadow-sm flex items-center justify-center overflow-hidden">
                    @if($umkm->foto_usaha)
                        <img src="{{ asset('storage/' . $umkm->foto_usaha) }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-store text-blue-500"></i>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden group">
                <div class="absolute -right-2 -top-2 w-16 h-16 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Saldo Penjual</span>
                    <h2 class="text-xl font-black text-slate-800">Rp {{ number_format($metrics['total_sales'], 0, ',', '.') }}</h2>
                </div>
            </div>
            <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden group">
                <div class="absolute -right-2 -top-2 w-16 h-16 bg-amber-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Pesanan Baru</span>
                    <h2 class="text-xl font-black text-amber-600">{{ $metrics['new_orders'] }}</h2>
                </div>
            </div>
            <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden group">
                <div class="absolute -right-2 -top-2 w-16 h-16 bg-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Perlu Dikirim</span>
                    <h2 class="text-xl font-black text-indigo-600">{{ $metrics['to_ship'] }}</h2>
                </div>
            </div>
            <div class="bg-white p-5 rounded-[2rem] shadow-sm border border-slate-100 relative overflow-hidden group">
                <div class="absolute -right-2 -top-2 w-16 h-16 bg-rose-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                <div class="relative">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Stok Menipis</span>
                    <h2 class="text-xl font-black text-rose-500">{{ $metrics['low_stock'] }}</h2>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sales Chart -->
            <div class="lg:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-800">Performa Penjualan</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">7 Hari Terakhir</p>
                    </div>
                </div>
                <div class="h-[300px]">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-slate-800">Pesanan Terbaru</h3>
                    <a href="{{ route('umkm_rakyat.manage.seller.orders', $umkm->manage_token) }}" class="text-xs font-black text-blue-600 hover:underline">Semua</a>
                </div>
                
                <div class="space-y-4">
                    @forelse($recent_orders as $order)
                    <div class="flex items-center gap-4 p-3 rounded-2xl hover:bg-slate-50 transition-all border border-transparent hover:border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xs font-black text-slate-800 line-clamp-1">{{ $order->customer_name }}</h4>
                            <p class="text-[10px] font-bold text-slate-400">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                        </div>
                        <span class="text-[9px] font-black px-2 py-1 rounded-md uppercase {{ $order->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $order->status }}
                        </span>
                    </div>
                    @empty
                    <div class="py-10 text-center">
                        <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-inbox text-slate-300"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Belum ada pesanan</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="fixed bottom-0 inset-x-0 bg-white/80 backdrop-blur-lg border-t border-slate-100 z-50">
        <div class="max-w-lg mx-auto flex justify-around p-4">
            <a href="{{ route('umkm_rakyat.manage.seller.dashboard', $umkm->manage_token) }}" class="flex flex-col items-center gap-1 text-blue-600">
                <i class="fas fa-home text-lg"></i>
                <span class="text-[9px] font-black uppercase">Beranda</span>
            </a>
            <a href="{{ route('umkm_rakyat.manage.seller.orders', $umkm->manage_token) }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-600 transition-all">
                <i class="fas fa-shopping-cart text-lg"></i>
                <span class="text-[9px] font-black uppercase">Pesanan</span>
            </a>
            <a href="{{ route('umkm_rakyat.manage.seller.inventory', $umkm->manage_token) }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-600 transition-all">
                <i class="fas fa-box text-lg"></i>
                <span class="text-[9px] font-black uppercase">Produk</span>
            </a>
            <a href="{{ route('umkm_rakyat.manage', $umkm->manage_token) }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-600 transition-all">
                <i class="fas fa-cog text-lg"></i>
                <span class="text-[9px] font-black uppercase">Toko</span>
            </a>
        </div>
    </nav>

    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($sales_chart->pluck('date')) !!},
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: {!! json_encode($sales_chart->pluck('total')) !!},
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        ticks: { font: { weight: 'bold', size: 10 }, color: '#94a3b8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 'bold', size: 10 }, color: '#94a3b8' }
                    }
                }
            }
        });
    </script>
</body>
</html>
