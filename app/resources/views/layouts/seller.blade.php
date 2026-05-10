<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dasbor UMKM') - {{ appProfile()->nama_kecamatan ?? 'Kecamatan' }} SAE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        seller: {
                            primary: '#2563eb',
                            secondary: '#64748b',
                            accent: '#f8fafc'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; }
        .glass-sidebar { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-right: 1px solid rgba(255, 255, 255, 0.2); }
        .sidebar-item-active { background: #eff6ff; color: #2563eb; border-radius: 1rem; font-weight: 700; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @yield('extra_css')
</head>
<body class="text-slate-700 antialiased">

    <div class="flex min-h-screen overflow-hidden">
        
        <!-- SIDEBAR (Modular) -->
        @include('public.seller.partials.sidebar')

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#f8fafc]">
            
            <!-- NAVBAR (Modular) -->
            @include('public.seller.partials.navbar')

            <!-- CONTENT -->
            <main class="flex-1 overflow-y-auto no-scrollbar p-4 md:p-8">
                <div class="max-w-6xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- MOBILE BOTTOM NAVIGATION (Improvisasi untuk kenyamanan HP) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-lg border-t border-slate-100 px-6 py-3 flex justify-between items-center z-50">
        <a href="{{ route('portal_warga.dashboard') }}" class="flex flex-col items-center gap-1 {{ Route::is('portal_warga.dashboard') ? 'text-blue-600' : 'text-slate-400' }}">
            <i class="fas fa-home text-lg"></i>
            <span class="text-[9px] font-black uppercase">Home</span>
        </a>
        <a href="{{ isset($primaryUmkm) ? route('umkm_rakyat.manage.products', $primaryUmkm->manage_token) : '#' }}" class="flex flex-col items-center gap-1 {{ Route::is('umkm_rakyat.manage.products') ? 'text-blue-600' : 'text-slate-400' }}">
            <i class="fas fa-box-open text-lg"></i>
            <span class="text-[9px] font-black uppercase">Produk</span>
        </a>
        <a href="{{ isset($primaryUmkm) ? route('umkm_rakyat.manage.seller.dashboard', $primaryUmkm->manage_token) : '#' }}" class="flex flex-col items-center gap-1 {{ Route::is('umkm_rakyat.manage.seller.dashboard') ? 'text-blue-600' : 'text-slate-400' }}">
            <i class="fas fa-shopping-cart text-lg"></i>
            <span class="text-[9px] font-black uppercase">Pesanan</span>
        </a>
        <a href="{{ route('portal_warga.logout') }}" class="flex flex-col items-center gap-1 text-rose-400">
            <i class="fas fa-power-off text-lg"></i>
            <span class="text-[9px] font-black uppercase">Keluar</span>
        </a>
    </div>

    @yield('extra_js')
</body>
</html>
