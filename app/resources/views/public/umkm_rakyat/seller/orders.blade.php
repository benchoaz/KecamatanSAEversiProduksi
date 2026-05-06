<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pesanan - {{ $umkm->nama_usaha }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<body class="bg-[#F8FAFC] min-h-screen pb-24 text-slate-800">

    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-40 border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-3">
            <a href="{{ route('umkm_rakyat.manage.seller.dashboard', $umkm->manage_token) }}" class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-sm font-black tracking-tight uppercase">Manajemen Pesanan</h1>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Status Filter -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 no-scrollbar">
            @php
                $filters = [
                    'all' => 'Semua',
                    'pending' => 'Baru',
                    'packing' => 'Dikemas',
                    'sent' => 'Dikirim',
                    'completed' => 'Selesai',
                    'cancelled' => 'Batal'
                ];
            @endphp
            @foreach($filters as $key => $label)
            <a href="?status={{ $key }}" 
               class="px-5 py-2.5 rounded-full text-[10px] font-black uppercase tracking-widest whitespace-nowrap transition-all
               {{ $status === $key ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-slate-400 border border-slate-100 hover:border-blue-200' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <!-- Orders List -->
        <div class="space-y-4">
            @forelse($orders as $order)
            <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer</span>
                        <h3 class="font-black text-slate-800 uppercase text-sm tracking-tight">{{ $order->customer_name }}</h3>
                        <p class="text-[10px] font-bold text-blue-600">+{{ $order->customer_whatsapp }}</p>
                    </div>
                    <span class="text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest
                        {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 
                           ($order->status === 'cancelled' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ $order->status }}
                    </span>
                </div>

                <div class="space-y-3 mb-6">
                    @foreach($order->items as $item)
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-600">{{ $item->quantity }}x {{ $item->product->nama_produk ?? 'Produk dihapus' }}</span>
                        <span class="font-black text-slate-400">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center py-4 border-y border-slate-50 mb-6">
                    <span class="text-[10px] font-black text-slate-400 uppercase">Total Bayar</span>
                    <span class="text-lg font-black text-slate-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-2">
                    <form action="{{ route('umkm_rakyat.manage.seller.orders.status', [$umkm->manage_token, $order->id]) }}" method="POST" class="flex-1 min-w-[140px]">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-4 focus:ring-blue-500/10">
                            @foreach($filters as $val => $lab)
                                @if($val !== 'all')
                                <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>Set: {{ $lab }}</option>
                                @endif
                            @endforeach
                        </select>
                    </form>
                    
                    <a href="https://wa.me/{{ $order->customer_whatsapp }}" target="_blank" class="bg-emerald-500 text-white p-3 rounded-2xl flex items-center justify-center gap-2 font-black text-[10px] uppercase tracking-widest flex-1 min-w-[140px]">
                        <i class="fab fa-whatsapp"></i> Chat Customer
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-[3rem] p-16 text-center border border-dashed border-slate-200">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <i class="fas fa-shopping-cart text-3xl"></i>
                </div>
                <h3 class="text-lg font-black text-slate-800">Tidak ada pesanan</h3>
                <p class="text-xs text-slate-400 mt-2">Pesanan dengan status <b>{{ $status }}</b> belum tersedia.</p>
            </div>
            @endforelse

            <div class="mt-8">
                {{ $orders->appends(['status' => $status])->links() }}
            </div>
        </div>
    </main>

    <!-- Bottom Navigation (Copy from dashboard) -->
    <nav class="fixed bottom-0 inset-x-0 bg-white/80 backdrop-blur-lg border-t border-slate-100 z-50">
        <div class="max-w-lg mx-auto flex justify-around p-4">
            <a href="{{ route('umkm_rakyat.manage.seller.dashboard', $umkm->manage_token) }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-600 transition-all">
                <i class="fas fa-home text-lg"></i>
                <span class="text-[9px] font-black uppercase">Beranda</span>
            </a>
            <a href="{{ route('umkm_rakyat.manage.seller.orders', $umkm->manage_token) }}" class="flex flex-col items-center gap-1 text-blue-600">
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

</body>
</html>
