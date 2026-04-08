@extends('layouts.umkm')

@section('page_title', 'Kelola Produk')

@section('content')
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-slate-500 font-medium">Tambah atau perbarui daftar produk jualan Anda.</p>
        </div>
        <button onclick="document.getElementById('modalProduct').classList.remove('hidden')"
            class="bg-seller-primary text-white font-black px-8 py-4 rounded-2xl shadow-lg shadow-sky-500/20 hover:bg-seller-primary/90 transition-all flex items-center justify-center gap-3">
            <i class="fas fa-plus"></i>
            <span>Tambah Produk Baru</span>
        </button>
    </div>

    @if(session('success'))
        <div
            class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-500"></i>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
        @forelse($products as $product)
            <div
                class="bg-white rounded-[2.5rem] p-4 shadow-xl shadow-slate-200/50 border border-slate-100 group transition-all hover:-translate-y-2">
                <div class="relative aspect-square rounded-[2rem] overflow-hidden mb-6 bg-slate-50">
                    @if($product->foto_produk)
                        <img src="{{ asset('storage/' . $product->foto_produk) }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-200 text-4xl">
                            <i class="fas fa-box"></i>
                        </div>
                    @endif
                    <div class="absolute bottom-4 right-4">
                        <span
                            class="bg-white/90 backdrop-blur-md px-4 py-2 rounded-xl text-sm font-black text-slate-800 shadow-sm border border-white/20">
                            Rp {{ number_format($product->harga, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="px-2">
                    <div class="flex items-start justify-between gap-2 mb-4">
                        <h4 class="font-black text-slate-800 text-lg truncate flex-1">{{ $product->nama_produk }}</h4>
                        <div class="flex flex-col items-end shrink-0">
                            <span class="text-[9px] font-black uppercase tracking-widest {{ $product->is_available ? 'text-emerald-500' : 'text-rose-500' }} mb-1">
                                {{ $product->is_available ? 'Tersedia' : 'Habis' }}
                            </span>
                            <form action="{{ route('umkm_rakyat.manage.product.toggle', ['token' => $umkm->manage_token, 'productId' => $product->id]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-10 h-5 {{ $product->is_available ? 'bg-emerald-400' : 'bg-slate-200' }} rounded-full relative shadow-inner block">
                                    <div class="w-4 h-4 bg-white rounded-full absolute top-0.5 {{ $product->is_available ? 'right-0.5' : 'left-0.5' }} shadow-sm transition-all"></div>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-2 pt-4 border-t border-slate-50">
                        <button type="button" class="w-full sm:w-1/2 py-3 rounded-xl bg-slate-50 text-slate-600 font-bold text-xs uppercase tracking-widest hover:bg-slate-100 transition-all">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </button>
                        <form
                            action="{{ route('umkm_rakyat.manage.product.delete', ['token' => $umkm->manage_token, 'productId' => $product->id]) }}"
                            method="POST" class="w-full sm:w-1/2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Hapus produk ini?')"
                                class="w-full py-3 rounded-xl border-2 border-slate-100 text-slate-400 font-bold text-xs uppercase tracking-widest hover:bg-rose-50 hover:border-rose-100 hover:text-rose-500 transition-all">
                                <i class="fas fa-trash-alt md:hidden lg:inline mr-1 text-[10px]"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <div
                    class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 text-3xl">
                    <i class="fas fa-box-open"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-2">Belum ada produk</h3>
                <p class="text-slate-500 font-medium">Mulai tambahkan produk pertama Anda untuk mulai berjualan.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal Add Product -->
    <div id="modalProduct"
        class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-6">
        <div
            class="bg-white w-full max-w-xl rounded-[3rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-800 tracking-tight">Tambah Produk Baru</h3>
                <button onclick="document.getElementById('modalProduct').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="{{ route('umkm_rakyat.manage.product.store', $umkm->manage_token) }}" method="POST"
                enctype="multipart/form-data" class="p-8 space-y-6">
                @csrf
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Nama
                        Produk</label>
                    <input type="text" name="nama_produk" required placeholder="Contoh: Keripik Singkong Renyah"
                        class="w-full bg-slate-50 border-2 border-slate-50 rounded-2xl px-6 py-4 text-sm font-semibold text-slate-700 focus:bg-white focus:border-sky-500/20 focus:ring-4 focus:ring-sky-500/10 transition-all outline-none">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Harga
                            (Rp)</label>
                        <input type="number" name="harga" required placeholder="Contoh: 15000"
                            class="w-full bg-slate-50 border-2 border-slate-50 rounded-2xl px-6 py-4 text-sm font-semibold text-slate-700 focus:bg-white focus:border-sky-500/20 focus:ring-4 focus:ring-sky-500/10 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Satuan</label>
                        <div class="relative">
                            <select name="satuan_harga" required
                                class="w-full bg-slate-50 border-2 border-slate-50 rounded-2xl px-6 py-4 text-sm font-semibold text-slate-700 focus:bg-white focus:border-sky-500/20 focus:ring-4 focus:ring-sky-500/10 transition-all outline-none appearance-none cursor-pointer">
                                <option value="Pcs">per Pcs</option>
                                <option value="Bungkus">per Bungkus</option>
                                <option value="Porsi">per Porsi</option>
                                <option value="Kotak">per Kotak</option>
                                <option value="Botol">per Botol</option>
                                <option value="Cup">per Cup</option>
                                <option value="Kg">per Kg</option>
                                <option value="Gram">per Gram</option>
                                <option value="Liter">per Liter</option>
                                <option value="Paket">per Paket</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">Foto
                        Produk</label>
                    <div class="relative group">
                        <input type="file" name="foto_produk" accept="image/*"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div
                            class="w-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center group-hover:bg-slate-100 group-hover:border-sky-500/30 transition-all">
                            <i
                                class="fas fa-image text-3xl text-slate-300 mb-3 group-hover:text-sky-500 transition-colors"></i>
                            <p class="text-xs font-bold text-slate-400">Klik atau geser foto ke sini</p>
                        </div>
                    </div>
                </div>
                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-seller-primary text-white font-black py-4 rounded-2xl shadow-lg shadow-sky-500/20 hover:scale-[1.02] active:scale-95 transition-all">
                        Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection