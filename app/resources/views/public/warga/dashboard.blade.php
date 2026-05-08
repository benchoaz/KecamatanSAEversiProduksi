<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Center - {{ appProfile()->nama_kecamatan ?? 'Kecamatan' }} SAE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f1f5f9; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.5); }
        .sidebar-item-active { background: #e0f2fe; color: #0369a1; border-right: 4px solid #0369a1; }
    </style>
</head>

<body class="text-slate-700">

    <div class="min-h-screen flex flex-col md:flex-row">
        
        <!-- SIDEBAR (Navigation) -->
        <aside class="w-full md:w-64 bg-white border-r border-slate-200 flex-shrink-0 flex flex-col sticky top-0 h-auto md:h-screen z-40">
            <div class="p-6 flex items-center gap-3">
                <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center text-white">
                    <i class="fas fa-store-alt"></i>
                </div>
                <h1 class="font-bold text-lg text-slate-800">Seller Center</h1>
            </div>

            <nav class="flex-1 space-y-1 px-3">
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl sidebar-item-active font-bold transition-all">
                    <i class="fas fa-home"></i> Beranda
                </a>
                <a href="{{ route('umkm_rakyat.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-500 font-semibold transition-all">
                    <i class="fas fa-plus-circle"></i> Tambah Toko
                </a>
                <a href="{{ route('economy.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-500 font-semibold transition-all">
                    <i class="fas fa-tools"></i> Tawarkan Jasa
                </a>
                <div class="pt-4 pb-2 px-4">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Layanan Warga</span>
                </div>
                <a href="{{ route('layanan') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-500 font-semibold transition-all text-sm">
                    <i class="fas fa-file-invoice"></i> Portal Layanan
                </a>
                <a href="{{ route('apply.layanan', 'pengaduan') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-slate-50 text-slate-500 font-semibold transition-all text-sm">
                    <i class="fas fa-bullhorn"></i> Lapor Masalah
                </a>
            </nav>

            <div class="p-4 border-t border-slate-100">
                <a href="{{ route('portal_warga.logout') }}" class="flex items-center justify-center gap-2 w-full py-3 bg-rose-50 text-rose-600 rounded-xl font-bold text-xs hover:bg-rose-100 transition-all">
                    <i class="fas fa-sign-out-alt"></i> Keluar Portal
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-4 md:p-10 overflow-y-auto">
            
            <!-- Top Header Info -->
            <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Halo, Warga Kreatif</h2>
                    <p class="text-slate-500 font-medium">Kelola toko dan aset ekonomi Anda di sini.</p>
                </div>
                <div class="flex items-center gap-4 bg-white p-2 pr-6 rounded-2xl shadow-sm border border-slate-100">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center text-xl">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider leading-none mb-1">Terhubung Ke</p>
                        <p class="font-bold text-slate-800 text-sm">+{{ $phone }}</p>
                    </div>
                </div>
            </header>

            @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl flex items-center gap-3 mb-8 animate-bounce">
                <i class="fas fa-check-circle text-xl"></i>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
            @endif

            <!-- Business Assets (THE HERO SECTION) -->
            <section class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-black text-xl text-slate-800 flex items-center gap-2">
                        <i class="fas fa-boxes-packing text-brand-600"></i>
                        Aset Toko & Jasa Anda
                    </h3>
                    @if(count($allAssets) > 0)
                    <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full uppercase tracking-widest border border-emerald-100">
                        {{ count($allAssets) }} Aktif
                    </span>
                    @endif
                </div>

                @if(count($allAssets) > 0)
                <div class="grid grid-cols-1 gap-6">
                    @foreach($allAssets as $asset)
                        @php 
                            $item = $asset['data'];
                            $type = $asset['type'];
                            $name = $type === 'umkm' ? $item->nama_usaha : ($type === 'jasa' ? $item->job_title : $item->name);
                            $opStatus = $item->operational_status;
                            $manageUrl = $type === 'umkm' ? route('umkm_rakyat.manage.seller.dashboard', $item->manage_token) : route('portal_warga.bridge.jasa', $item->id);
                        @endphp
                        
                        <div class="bg-white rounded-[2.5rem] p-6 md:p-8 shadow-md border border-slate-100 hover:shadow-xl transition-all group relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-8 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity">
                                <i class="fas {{ $type === 'umkm' ? 'fa-store' : 'fa-hand-sparkles' }} text-8xl"></i>
                            </div>
                            
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 relative z-10">
                                <div class="flex items-center gap-6">
                                    <div class="w-20 h-20 {{ $opStatus['bg'] }} {{ $opStatus['text'] }} rounded-[2rem] flex items-center justify-center text-3xl shadow-inner border-4 border-white">
                                        <i class="fas {{ $opStatus['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-3 mb-2">
                                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ $name }}</h4>
                                            <span class="text-[10px] font-black px-3 py-1 rounded-full {{ $opStatus['bg'] }} {{ $opStatus['text'] }} uppercase tracking-wider shadow-sm">{{ $opStatus['label'] }}</span>
                                            @if($type === 'umkm')
                                                <span class="text-[10px] font-black px-3 py-1 rounded-full bg-slate-900 text-white uppercase tracking-widest">Official UMKM</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-sm font-semibold text-slate-400">
                                            <span class="flex items-center gap-1.5">
                                                <i class="fas fa-clock text-slate-300"></i> {{ $item->operating_hours ?: '24 Jam' }}
                                            </span>
                                            @if(isset($item->product_count))
                                            <span class="flex items-center gap-1.5">
                                                <i class="fas fa-layer-group text-slate-300"></i> {{ $item->product_count }} Produk Tersedia
                                            </span>
                                            @endif
                                            @if($item->is_on_holiday)
                                            <span class="flex items-center gap-1.5 text-rose-500">
                                                <i class="fas fa-umbrella-beach"></i> Sedang Libur
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <form action="{{ route('portal_warga.status_update') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="type" value="{{ $type }}">
                                        <input type="hidden" name="id" value="{{ $item->id }}">
                                        <input type="hidden" name="operating_hours" value="{{ $item->operating_hours }}">
                                        @if($item->is_on_holiday)
                                            <input type="hidden" name="is_on_holiday" value="0">
                                            <button type="submit" class="h-14 px-6 bg-emerald-600 text-white rounded-2xl font-black text-sm shadow-lg shadow-emerald-900/20 hover:bg-emerald-700 transition-all flex items-center gap-3">
                                                <i class="fas fa-play"></i> Buka Toko Sekarang
                                            </button>
                                        @else
                                            <input type="hidden" name="is_on_holiday" value="1">
                                            <button type="submit" class="h-14 px-6 bg-slate-100 text-slate-600 rounded-2xl font-black text-sm hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center gap-3">
                                                <i class="fas fa-calendar-minus"></i> Setel Libur
                                            </button>
                                        @endif
                                    </form>

                                    <a href="{{ $manageUrl }}" class="h-14 px-8 bg-slate-900 text-white rounded-2xl font-black text-sm shadow-xl shadow-slate-900/30 hover:translate-y-[-2px] transition-all flex items-center gap-3">
                                        <i class="fas fa-external-link-alt text-slate-400"></i> Kelola Bisnis
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                <!-- Empty State -->
                <div class="bg-white rounded-[3rem] p-16 text-center border border-dashed border-slate-200">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                        <i class="fas fa-store-slash text-4xl"></i>
                    </div>
                    <h4 class="text-xl font-black text-slate-800 mb-2">Anda Belum Memiliki Toko</h4>
                    <p class="text-slate-500 font-medium mb-8 max-w-sm mx-auto">Daftarkan usaha atau jasa Anda untuk mulai berjualan di portal kecamatan.</p>
                    <div class="flex justify-center gap-4">
                        <a href="{{ route('umkm_rakyat.create') }}" class="px-8 py-4 bg-brand-600 text-white rounded-2xl font-black text-sm shadow-lg shadow-brand-900/20 hover:bg-brand-700 transition-all">
                            Daftar UMKM Baru
                        </a>
                        <a href="{{ route('economy.create') }}" class="px-8 py-4 bg-white border border-slate-200 text-slate-700 rounded-2xl font-black text-sm hover:bg-slate-50 transition-all">
                            Daftar Jasa
                        </a>
                    </div>
                </div>
                @endif
            </section>

            <!-- Services Tracking (MINIMALIST LIST) -->
            @if($services->isNotEmpty())
            <section class="mt-20 pt-10 border-t border-slate-200">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-black text-sm text-slate-400 uppercase tracking-[0.2em]">Riwayat Layanan & Laporan</h3>
                </div>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 divide-y divide-slate-50 overflow-hidden">
                    @foreach($services as $service)
                    <div class="flex items-center justify-between p-5 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm {{ $service->category === 'pengaduan' ? 'bg-rose-50 text-rose-500' : 'bg-brand-50 text-brand-600' }}">
                                <i class="fas {{ $service->category === 'pengaduan' ? 'fa-bullhorn' : 'fa-file-invoice' }}"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-800 text-sm">{{ $service->jenis_layanan }}</h5>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $service->tracking_code }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-tighter
                                {{ $service->status === 'selesai' ? 'bg-emerald-100 text-emerald-700' : 
                                   ($service->status === 'proses' ? 'bg-brand-100 text-brand-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $service->status }}
                            </span>
                            <a href="{{ route('public.tracking') }}?q={{ $service->tracking_code }}" class="w-8 h-8 rounded-lg border border-slate-100 flex items-center justify-center text-slate-400 hover:text-brand-600 hover:border-brand-200 transition-all">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

        </main>
    </div>

</body>
</html>
