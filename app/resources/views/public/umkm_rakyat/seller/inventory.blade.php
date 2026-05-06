<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok & Inventaris - {{ $umkm->nama_usaha }}</title>
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
            <h1 class="text-sm font-black tracking-tight uppercase">Stok & Inventaris</h1>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Stock Summary -->
        <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100 mb-8 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Total Produk</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Dalam Katalog</p>
            </div>
            <div class="text-3xl font-black text-blue-600">{{ $products->count() }}</div>
        </div>

        <!-- Products List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($products as $product)
            <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-slate-100 flex gap-4 items-center">
                <div class="w-20 h-20 rounded-3xl bg-slate-100 flex-shrink-0 overflow-hidden border border-slate-50">
                    @if($product->foto_produk)
                        <img src="{{ asset('storage/' . $product->foto_produk) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                            <i class="fas fa-image text-2xl"></i>
                        </div>
                    @endif
                </div>
                
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs font-black text-slate-800 uppercase truncate">{{ $product->nama_produk }}</h4>
                    <p class="text-[10px] font-bold text-slate-400 mt-1">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                    
                    <div class="mt-4 flex items-center gap-4">
                        <div class="flex-1">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1">Stok Tersedia</label>
                            <div class="flex items-center gap-3">
                                <button onclick="changeStock({{ $product->id }}, -1)" class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-rose-50 hover:text-rose-500 transition-all">
                                    <i class="fas fa-minus text-[10px]"></i>
                                </button>
                                <span id="stock-{{ $product->id }}" class="text-sm font-black text-slate-700 min-w-[20px] text-center">{{ $product->stock }}</span>
                                <button onclick="changeStock({{ $product->id }}, 1)" class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-emerald-50 hover:text-emerald-500 transition-all">
                                    <i class="fas fa-plus text-[10px]"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 inset-x-0 bg-white/80 backdrop-blur-lg border-t border-slate-100 z-50">
        <div class="max-w-lg mx-auto flex justify-around p-4">
            <a href="{{ route('umkm_rakyat.manage.seller.dashboard', $umkm->manage_token) }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-600 transition-all">
                <i class="fas fa-home text-lg"></i>
                <span class="text-[9px] font-black uppercase">Beranda</span>
            </a>
            <a href="{{ route('umkm_rakyat.manage.seller.orders', $umkm->manage_token) }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-600 transition-all">
                <i class="fas fa-shopping-cart text-lg"></i>
                <span class="text-[9px] font-black uppercase">Pesanan</span>
            </a>
            <a href="{{ route('umkm_rakyat.manage.seller.inventory', $umkm->manage_token) }}" class="flex flex-col items-center gap-1 text-blue-600">
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
        async function changeStock(productId, delta) {
            const stockEl = document.getElementById(`stock-${productId}`);
            let currentStock = parseInt(stockEl.innerText);
            let newStock = Math.max(0, currentStock + delta);
            
            // Optimistic Update
            stockEl.innerText = newStock;
            
            try {
                const response = await fetch(`{{ url('/') }}/umkm-rakyat/{{ $umkm->manage_token }}/manage/seller/inventory/${productId}/stock`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ stock: newStock })
                });
                
                const data = await response.json();
                if (!data.success) {
                    stockEl.innerText = currentStock; // Revert
                    alert('Gagal mengupdate stok');
                }
            } catch (error) {
                stockEl.innerText = currentStock; // Revert
                alert('Terjadi kesalahan');
            }
        }
    </script>

</body>
</html>
