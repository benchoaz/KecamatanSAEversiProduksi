@php
    $items = [
        ['icon' => 'fa-home', 'label' => 'BERANDA', 'url' => '/', 'active' => request()->is('/') || request()->is('home')],
        ['icon' => 'fa-landmark', 'label' => 'LAYANAN', 'url' => '#layanan', 'active' => request()->is('layanan*') || request()->is('#layanan'), 'visible' => appProfile()->is_menu_pelayanan_active],
        ['icon' => 'fa-bullhorn', 'label' => 'PENGADUAN', 'url' => '#pengaduan', 'active' => false, 'isAction' => true, 'visible' => appProfile()->is_menu_pengaduan_active],
        ['icon' => 'fa-chart-bar', 'label' => 'STATISTIK', 'url' => route('landing.statistik.index'), 'active' => request()->is('statistik*'), 'visible' => appProfile()->is_menu_statistik_active],
        ['icon' => 'fa-newspaper', 'label' => 'BERITA', 'url' => '#berita', 'active' => request()->is('berita*') || request()->is('#berita'), 'visible' => appProfile()->is_menu_berita_active],
        ['icon' => 'fa-store', 'label' => 'UMKM', 'url' => route('economy.index'), 'active' => request()->is('ekonomi*'), 'visible' => appProfile()->is_menu_umkm_active],
    ];
    $activeColor = "text-[#003366]";
    $inactiveColor = "text-slate-400";
@endphp

<div class="fixed bottom-0 left-0 right-0 z-[100] lg:hidden bg-white/95 backdrop-blur-md border-t border-slate-100 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] pb-safe">
    <div class="grid grid-cols-6 h-16 w-full max-w-lg mx-auto">
        @foreach($items as $item)
            @php 
                $visible = $item['visible'] ?? true; 
                if (!$visible) continue;
            @endphp
            
            <a href="{{ $item['url'] }}" 
               @if(isset($item['isAction']) && $item['isAction']) onclick="event.preventDefault(); document.getElementById('complaintModal').showModal()" @endif
               class="flex flex-col items-center justify-center relative transition-all duration-300 active:scale-90 active:bg-slate-50 nav-item-press">
                
                {{-- Active Dot Indicator (ABOVE ICON) --}}
                @if($item['active'])
                    <div class="absolute top-1 w-1 h-0.5 bg-[#003366] rounded-full animate-pulse"></div>
                @endif

                <div class="flex flex-col items-center gap-0.5 {{ $item['active'] ? 'translate-y-[-2px] scale-110' : '' }} transition-transform duration-300">
                    <i class="fas {{ $item['icon'] }} text-lg {{ $item['active'] ? $activeColor : $inactiveColor }}"></i>
                    <span class="text-[7px] font-black uppercase tracking-tighter {{ $item['active'] ? $activeColor : $inactiveColor }}">
                        {{ $item['label'] }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>
</div>

<style>
    .pb-safe {
        padding-bottom: env(safe-area-inset-bottom);
    }
    
    /* Government Press Effect */
    @keyframes nav-ripple {
        0% { transform: scale(0.8); opacity: 0.5; }
        100% { transform: scale(1.5); opacity: 0; }
    }
    
    .nav-item-press {
        position: relative;
        overflow: hidden;
    }
    
    .nav-item-press::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100px;
        height: 100px;
        background: rgba(0, 51, 102, 0.1);
        border-radius: 50%;
        transform: translate(-50%, -50%) scale(0);
        pointer-events: none;
    }
    
    .nav-item-press:active::after {
        animation: nav-ripple 0.4s ease-out;
    }
</style>