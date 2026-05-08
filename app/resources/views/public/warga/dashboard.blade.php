<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Toko - {{ appProfile()->nama_kecamatan ?? 'Kecamatan' }} SAE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #fdfdfd; }
        .btn-shadow { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
        .shop-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .shop-card:active { transform: scale(0.98); }
    </style>
</head>

<body class="text-slate-900 pb-12">

    <!-- TOP HEADER: Identitas & Logout -->
    <header class="bg-white px-6 py-4 flex justify-between items-center border-b border-slate-100 sticky top-0 z-50 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white shadow-lg">
                <i class="fas fa-store"></i>
            </div>
            <div>
                <h1 class="text-sm font-black text-slate-800 leading-none">Dashboard Toko</h1>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Portal Warga</p>
            </div>
        </div>
        <a href="{{ route('portal_warga.logout') }}" class="w-10 h-10 bg-slate-50 text-rose-500 rounded-full flex items-center justify-center hover:bg-rose-50 transition-all">
            <i class="fas fa-power-off"></i>
        </a>
    </header>

    <main class="max-w-xl mx-auto px-5 py-8 space-y-8">

        <!-- WELCOME CARD -->
        <div class="bg-gradient-to-br from-blue-700 to-indigo-800 rounded-[2.5rem] p-8 text-white relative overflow-hidden shadow-2xl">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i class="fas fa-certificate text-8xl -rotate-12"></i>
            </div>
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-widest text-blue-200 mb-2">Selamat Datang,</p>
                <h2 class="text-3xl font-black mb-1">Identitas Warga</h2>
                <div class="flex items-center gap-2 text-xs font-semibold text-blue-100 bg-white/10 w-fit px-3 py-1.5 rounded-full mt-4 backdrop-blur-sm">
                    <i class="fab fa-whatsapp"></i> +{{ $phone }}
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-emerald-500 text-white px-6 py-4 rounded-3xl flex items-center gap-3 font-bold text-sm shadow-lg animate__animated animate__fadeInUp">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- SHOP MANAGEMENT HERO -->
        <section class="space-y-6">
            <div class="flex items-center justify-between px-2">
                <h3 class="font-black text-lg text-slate-800">Lapak & Jasa Saya</h3>
                <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-widest border border-blue-100">
                    {{ count($allAssets) }} Aktif
                </span>
            </div>

            @if(count($allAssets) > 0)
            <div class="space-y-4">
                @foreach($allAssets as $asset)
                    @php 
                        $item = $asset['data'];
                        $type = $asset['type'];
                        $name = $type === 'umkm' ? $item->nama_usaha : ($type === 'jasa' ? $item->job_title : $item->name);
                        $opStatus = $item->operational_status;
                        $manageUrl = $type === 'umkm' ? route('umkm_rakyat.manage.seller.dashboard', $item->manage_token) : route('portal_warga.bridge.jasa', $item->id);
                    @endphp
                    
                    <div class="bg-white rounded-[2.5rem] p-6 shadow-md border border-slate-100 shop-card relative overflow-hidden group">
                        <!-- Shop Status Badge -->
                        <div class="absolute top-0 right-0 mt-6 mr-6">
                             <span class="text-[9px] font-black px-3 py-1.5 rounded-full {{ $opStatus['bg'] }} {{ $opStatus['text'] }} uppercase tracking-wider shadow-sm border border-white">
                                {{ $opStatus['label'] }}
                             </span>
                        </div>

                        <div class="flex items-center gap-5 mb-8">
                            <div class="w-16 h-16 {{ $opStatus['bg'] }} {{ $opStatus['text'] }} rounded-3xl flex items-center justify-center text-3xl shadow-inner border-4 border-white flex-shrink-0">
                                <i class="fas {{ $opStatus['icon'] }}"></i>
                            </div>
                            <div class="pr-12">
                                <h4 class="text-xl font-black text-slate-800 leading-tight mb-1">{{ $name }}</h4>
                                <p class="text-xs font-bold text-slate-400">
                                    <i class="fas fa-clock mr-1 opacity-50"></i> {{ $item->operating_hours ?: 'Buka 24 Jam' }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons (Big & Touch Friendly) -->
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ $manageUrl }}" class="flex flex-col items-center justify-center p-4 bg-slate-900 text-white rounded-3xl transition-all active:bg-slate-800 shadow-lg shadow-slate-900/20">
                                <i class="fas fa-cog text-lg mb-2"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Atur Toko</span>
                            </a>
                            
                            <form action="{{ route('portal_warga.status_update') }}" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <input type="hidden" name="id" value="{{ $item->id }}">
                                <input type="hidden" name="operating_hours" value="{{ $item->operating_hours }}">
                                
                                @if($item->is_on_holiday)
                                    <input type="hidden" name="is_on_holiday" value="0">
                                    <button type="submit" class="w-full h-full flex flex-col items-center justify-center p-4 bg-emerald-500 text-white rounded-3xl transition-all active:bg-emerald-600 shadow-lg shadow-emerald-500/20">
                                        <i class="fas fa-play text-lg mb-2"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Buka Sekarang</span>
                                    </button>
                                @else
                                    <input type="hidden" name="is_on_holiday" value="1">
                                    <button type="submit" class="w-full h-full flex flex-col items-center justify-center p-4 bg-rose-50 text-rose-500 rounded-3xl border border-rose-100 hover:bg-rose-100 transition-all">
                                        <i class="fas fa-calendar-times text-lg mb-2"></i>
                                        <span class="text-[10px] font-black uppercase tracking-widest">Setel Libur</span>
                                    </button>
                                @endif
                            </form>
                        </div>

                        <!-- Preview Link -->
                        <div class="mt-4 pt-4 border-t border-slate-50 flex justify-center">
                            @php
                                $previewUrl = match($type) {
                                    'umkm' => route('umkm_rakyat.show', $item->slug),
                                    'jasa' => route('economy.show', $item->id),
                                    'umkm_local' => route('economy.produk.show', $item->id),
                                    default => '#'
                                };
                            @endphp
                            <a href="{{ $previewUrl }}" target="_blank" class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] flex items-center gap-2 hover:underline">
                                <i class="fas fa-eye"></i> Lihat Tampilan Lapak
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            <!-- EMPTY STATE -->
            <div class="bg-white rounded-[2.5rem] p-12 text-center border-2 border-dashed border-slate-100">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <i class="fas fa-store-slash text-3xl"></i>
                </div>
                <h4 class="text-lg font-black text-slate-800 mb-2">Mulai Jualan Yuk!</h4>
                <p class="text-xs text-slate-500 font-medium mb-8 leading-relaxed">Daftarkan usaha atau jasa Anda untuk dilihat oleh seluruh warga kecamatan.</p>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('umkm_rakyat.create') }}" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-blue-600/20">
                        Daftar Toko (UMKM)
                    </a>
                    <a href="{{ route('economy.create') }}" class="w-full py-4 bg-white border border-slate-200 text-slate-700 rounded-2xl font-black text-sm">
                        Daftar Jasa / Tenaga
                    </a>
                </div>
            </div>
            @endif
        </section>

        <!-- QUICK ACCESS FOR OTHER SERVICES (Compact) -->
        <section class="grid grid-cols-2 gap-4">
             <a href="{{ route('apply.layanan', 'pengaduan') }}" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col items-center text-center">
                <div class="w-10 h-10 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-800">Lapor Masalah</span>
             </a>
             <a href="{{ route('layanan') }}" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm flex flex-col items-center text-center">
                <div class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center mb-3">
                    <i class="fas fa-file-contract"></i>
                </div>
                <span class="text-[10px] font-black uppercase text-slate-800">Minta Surat</span>
             </a>
        </section>

        <!-- PERSONAL HISTORY (Minimized at Bottom) -->
        @if($services->isNotEmpty())
        <section class="pt-8 border-t border-slate-100">
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Riwayat Layanan Saya</h4>
            <div class="space-y-3">
                @foreach($services as $service)
                <div class="bg-white p-4 rounded-2xl border border-slate-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs {{ $service->category === 'pengaduan' ? 'bg-rose-50 text-rose-500' : 'bg-blue-50 text-blue-500' }}">
                            <i class="fas {{ $service->category === 'pengaduan' ? 'fa-bullhorn' : 'fa-file-invoice' }}"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-800 leading-none mb-1">{{ $service->jenis_layanan }}</p>
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ $service->tracking_code }}</p>
                        </div>
                    </div>
                    <span class="text-[8px] font-black px-2 py-1 rounded-full uppercase
                        {{ $service->status === 'selesai' ? 'bg-emerald-100 text-emerald-700' : 
                           ($service->status === 'proses' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ $service->status }}
                    </span>
                </div>
                @endforeach
            </div>
            <a href="{{ route('public.tracking') }}" class="w-full mt-6 py-4 bg-slate-50 text-slate-500 rounded-2xl font-black text-[10px] uppercase tracking-widest text-center border border-slate-100">
                Lihat Semua Riwayat
            </a>
        </section>
        @endif

    </main>

</body>
</html>
