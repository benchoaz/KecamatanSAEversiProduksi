<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Bisnis UMKM - {{ appProfile()->nama_kecamatan ?? 'Kecamatan' }} SAE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fafafa; }
        .shop-card { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .shop-card:hover { transform: translateY(-5px); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); }
    </style>
</head>

<body class="text-slate-900 pb-20">

    <!-- NAVIGATION: BRAND FOCUS -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50">
        <div class="max-w-xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div>
                    <h1 class="text-base font-black text-slate-800 leading-none">Dasbor UMKM</h1>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Pusat Kendali Bisnis</p>
                </div>
            </div>
            <a href="{{ route('portal_warga.logout') }}" class="w-10 h-10 bg-slate-50 text-slate-400 rounded-2xl flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
    </nav>

    <main class="max-w-xl mx-auto px-6 py-8 space-y-10">

        @if(session('success'))
        <div class="bg-emerald-500 text-white px-6 py-4 rounded-3xl flex items-center gap-3 font-bold text-sm shadow-xl shadow-emerald-200">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- BUSINESS PROFILE HEADER -->
        <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 opacity-50"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-4">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">Pemilik Toko</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 leading-tight">Halo, Warga Kreatif</h2>
                <div class="flex items-center gap-3 mt-6">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-slate-100 border-2 border-white flex items-center justify-center text-[10px] font-bold">W</div>
                        <div class="w-8 h-8 rounded-full bg-blue-100 border-2 border-white flex items-center justify-center text-[10px] font-bold text-blue-600">A</div>
                    </div>
                    <span class="text-xs font-bold text-slate-400">+{{ $phone }}</span>
                </div>
            </div>
        </div>

        <!-- MY ASSETS / SHOP LIST -->
        <section class="space-y-6">
            <div class="flex items-center justify-between px-2">
                <h3 class="font-black text-xl text-slate-800">Lapak & Bisnis Saya</h3>
                <a href="{{ route('umkm_rakyat.create') }}" class="text-[10px] font-black text-blue-600 bg-blue-50 px-4 py-2 rounded-xl uppercase tracking-widest border border-blue-100">
                    + Tambah Baru
                </a>
            </div>

            @if(count($allAssets) > 0)
            <div class="space-y-6">
                @foreach($allAssets as $asset)
                    @php 
                        $item = $asset['data'];
                        $type = $asset['type'];
                        $name = $type === 'umkm' ? $item->nama_usaha : ($type === 'jasa' ? $item->job_title : $item->name);
                        $opStatus = $item->operational_status;
                        $manageUrl = $type === 'umkm' ? route('umkm_rakyat.manage.seller.dashboard', $item->manage_token) : route('portal_warga.bridge.jasa', $item->id);
                        $products = $type === 'umkm_local' ? explode(',', $item->product) : [];
                    @endphp
                    
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100 shop-card group">
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                            <div class="flex items-center gap-6">
                                <div class="w-20 h-20 {{ $opStatus['bg'] }} {{ $opStatus['text'] }} rounded-[2rem] flex items-center justify-center text-3xl shadow-inner border-4 border-white flex-shrink-0">
                                    <i class="fas {{ $opStatus['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="text-2xl font-black text-slate-800 leading-tight">{{ $name }}</h4>
                                        <span class="text-[9px] font-black px-2.5 py-1 rounded-full {{ $opStatus['bg'] }} {{ $opStatus['text'] }} uppercase tracking-wider">{{ $opStatus['label'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-4 text-xs font-bold text-slate-400">
                                        <span><i class="fas fa-clock mr-1 opacity-50"></i> {{ $item->operating_hours ?: '24 Jam' }}</span>
                                        @if(isset($item->product_count))
                                            <span><i class="fas fa-tag mr-1 opacity-50"></i> {{ $item->product_count }} Produk</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ETALASE MINI (Visual Focus) -->
                        @if($type === 'umkm_local' && count($products) > 0)
                        <div class="mb-8">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Etalase Produk</p>
                            <div class="flex gap-3 overflow-x-auto pb-2 no-scrollbar">
                                @foreach($products as $p)
                                <div class="bg-slate-50 border border-slate-100 px-4 py-3 rounded-2xl flex-shrink-0">
                                    <span class="text-xs font-bold text-slate-700">{{ trim($p) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- CONTROLS -->
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ $manageUrl }}" class="flex items-center justify-center gap-3 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs shadow-xl shadow-slate-900/20 hover:bg-slate-800 transition-all">
                                <i class="fas fa-edit opacity-50"></i> Kelola Toko
                            </a>
                            
                            <form action="{{ route('portal_warga.status_update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <input type="hidden" name="id" value="{{ $item->id }}">
                                <input type="hidden" name="operating_hours" value="{{ $item->operating_hours }}">
                                
                                @if($item->is_on_holiday)
                                    <input type="hidden" name="is_on_holiday" value="0">
                                    <button type="submit" class="w-full flex items-center justify-center gap-3 py-4 bg-emerald-500 text-white rounded-2xl font-black text-xs shadow-xl shadow-emerald-500/20 hover:bg-emerald-600 transition-all">
                                        <i class="fas fa-play opacity-50"></i> Buka Sekarang
                                    </button>
                                @else
                                    <input type="hidden" name="is_on_holiday" value="1">
                                    <button type="submit" class="w-full flex items-center justify-center gap-3 py-4 bg-white border border-rose-100 text-rose-500 rounded-2xl font-black text-xs hover:bg-rose-50 transition-all">
                                        <i class="fas fa-calendar-times opacity-50"></i> Setel Libur
                                    </button>
                                @endif
                            </form>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-slate-50 flex justify-center">
                            @php
                                $previewUrl = match($type) {
                                    'umkm' => route('umkm_rakyat.show', $item->slug),
                                    'jasa' => route('economy.show', $item->id),
                                    'umkm_local' => route('economy.produk.show', $item->id),
                                    default => '#'
                                };
                            @endphp
                            <a href="{{ $previewUrl }}" target="_blank" class="text-[10px] font-black text-blue-500 uppercase tracking-[0.2em] flex items-center gap-2 hover:underline">
                                <i class="fas fa-external-link-alt"></i> Lihat Lapak Publik
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            <!-- EMPTY STATE -->
            <div class="bg-white rounded-[3rem] p-16 text-center border-2 border-dashed border-slate-100">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8 text-slate-300">
                    <i class="fas fa-store-alt-slash text-4xl"></i>
                </div>
                <h4 class="text-xl font-black text-slate-800 mb-2">Belum Ada Lapak Aktif</h4>
                <p class="text-sm text-slate-500 font-medium mb-10 leading-relaxed">Daftarkan usaha atau jasa Anda untuk mulai berjualan online di platform SAE.</p>
                <div class="flex flex-col gap-3">
                    <a href="{{ route('umkm_rakyat.create') }}" class="w-full py-5 bg-blue-600 text-white rounded-[2rem] font-black text-sm shadow-2xl shadow-blue-200 hover:bg-blue-700 transition-all">
                        Daftar Toko UMKM
                    </a>
                    <a href="{{ route('economy.create') }}" class="w-full py-5 bg-white border border-slate-200 text-slate-700 rounded-[2rem] font-black text-sm hover:bg-slate-50 transition-all">
                        Daftar Jasa / Tenaga
                    </a>
                </div>
            </div>
            @endif
        </section>

        <!-- FOOTER: PURE BUSINESS FOCUS -->
        <footer class="text-center pt-10 opacity-30">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Business Dashboard &bull; Kecamatan SAE</p>
        </footer>

    </main>

</body>
</html>
