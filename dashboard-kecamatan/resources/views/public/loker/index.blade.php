@extends('layouts.public')

@section('title', 'Papan Loker Warga – ' . appProfile()->region_level . ' ' . appProfile()->region_name)

@section('content')
    <div class="min-h-screen bg-slate-50">
        {{-- Hero Header - Scaled down for better professional look --}}
        <div
            class="bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 text-white pt-10 pb-20 relative overflow-hidden">
            {{-- Decorative Elements --}}
            <div
                class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full blur-[80px] -translate-y-1/2 translate-x-1/2">
            </div>
            <div
                class="absolute bottom-0 left-0 w-48 h-48 bg-emerald-400/20 rounded-full blur-[60px] translate-y-1/3 -translate-x-1/4">
            </div>

            <div class="container mx-auto px-6 relative z-10">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="max-w-2xl text-center lg:text-left">
                        <div
                            class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-3 py-1.5 rounded-full text-emerald-50 text-[10px] font-bold uppercase tracking-widest mb-4 animate__animated animate__fadeInDown">
                            <i class="fas fa-briefcase text-[10px]"></i>
                            Papan Loker Warga
                        </div>
                        <h1
                            class="text-3xl md:text-5xl font-black mb-4 tracking-tight leading-tight animate__animated animate__fadeInUp">
                            Cari Kerja atau <span class="text-emerald-200">Pasang Info Kerja</span>
                        </h1>
                        <p
                            class="text-base text-emerald-50 mb-8 leading-relaxed opacity-90 max-w-xl mx-auto lg:mx-0 animate__animated animate__fadeInUp animate__delay-1s">
                            Platform informasi kerja warga {{ appProfile()->region_level }} {{ appProfile()->region_name }}.
                            Informal, cepat, dan terpercaya langsung ke warga sekitar.
                        </p>
                        <div
                            class="flex flex-wrap justify-center lg:justify-start gap-3 animate__animated animate__fadeInUp animate__delay-1s">
                            <a href="{{ route('public.loker.create') }}"
                                class="px-6 py-3 bg-white text-emerald-700 rounded-xl font-black shadow-lg shadow-emerald-900/10 hover:scale-105 transition-all text-sm flex items-center gap-2">
                                <i class="fas fa-plus-circle"></i>
                                Pasang Info
                            </a>
                            <a href="#daftar-loker"
                                class="px-6 py-3 bg-emerald-700/30 hover:bg-emerald-700/50 border border-emerald-400/30 text-white rounded-xl font-bold backdrop-blur-sm transition-all text-sm flex items-center gap-2">
                                <i class="fas fa-search"></i>
                                Lihat Lowongan
                            </a>
                        </div>
                    </div>
                    <div class="hidden lg:block w-full max-w-xs animate__animated animate__fadeInRight">
                        <img src="https://illustrations.popsy.co/white/work-from-home.svg" alt="Loker Illustration"
                            class="w-full h-auto drop-shadow-xl">
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & List Section --}}
        <section id="daftar-loker" class="container mx-auto px-6 -mt-10 pb-20 relative z-20">
            {{-- Search & Filter Card - Compact --}}
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-5 md:p-6 border border-slate-100 mb-10">
                <form action="{{ route('public.loker.index') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-5 relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kerja..."
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/5 rounded-xl outline-none text-sm font-medium text-slate-700 transition-all">
                    </div>
                    <div class="md:col-span-3">
                        <select name="category"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/5 rounded-xl outline-none text-sm font-medium text-slate-700 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <select name="desa"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-100 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/5 rounded-xl outline-none text-sm font-medium text-slate-700 transition-all appearance-none cursor-pointer">
                            <option value="">Semua Desa</option>
                            @foreach($desas as $desa)
                                <option value="{{ $desa->id }}" {{ request('desa') == $desa->id ? 'selected' : '' }}>
                                    {{ $desa->nama_desa }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit"
                            class="w-full h-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-md shadow-emerald-200 transition-all text-sm flex items-center justify-center gap-2">
                            <span>Filter</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Grid Loker - Scaled down cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($lokers as $l)
                    <div
                        class="group bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-emerald-900/5 transition-all duration-300 flex flex-col h-full overflow-hidden">
                        <div class="p-6 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                                <span
                                    class="px-2 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black uppercase tracking-widest rounded-md border border-emerald-100 shadow-sm">
                                    {{ $l->job_category }}
                                </span>
                                @if($l->is_available_today)
                                    <span
                                        class="px-2 py-1 bg-rose-50 text-rose-600 text-[9px] font-black uppercase tracking-widest rounded-md border border-rose-100 animate-pulse">
                                        <i class="fas fa-bolt text-amber-500"></i> Segera
                                    </span>
                                @endif
                            </div>

                            <h3
                                class="text-base font-black text-slate-800 mb-3 group-hover:text-emerald-600 transition-colors leading-tight">
                                {{ $l->title }}
                            </h3>

                            <p class="text-slate-500 text-[11px] leading-relaxed mb-5 line-clamp-2">
                                {{ $l->description ?: 'Dibutuhkan segera tenaga kerja untuk posisi ' . $l->title . ' .' }}
                            </p>

                            <div class="space-y-2 mb-6 text-[11px] text-slate-500 font-medium">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-rose-500 w-4 text-center"></i>
                                    <span>{{ $l->desa ? $l->desa->nama_desa : ($l->nama_desa_manual ?: 'Sekitar Kecamatan') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-blue-500 w-4 text-center"></i>
                                    <span>{{ $l->work_time ?: 'Harian' }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-5 gap-2">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $l->contact_wa) }}?text=Halo%2C%20saya%20tertarik%20dengan%20info%20loker%20{{ urlencode($l->title) }}%20di%20Aplikasi%20Kecamatan."
                                    target="_blank"
                                    class="col-span-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-[11px] transition-all flex items-center justify-center gap-2">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                                <a href="tel:{{ $l->contact_wa }}"
                                    class="py-2.5 bg-slate-50 hover:bg-slate-100 text-slate-400 hover:text-blue-600 rounded-xl flex items-center justify-center transition-all">
                                    <i class="fas fa-phone-alt text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                        <div class="bg-slate-50/50 px-6 py-3 border-t border-slate-100 flex items-center justify-between">
                            <span
                                class="text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                <i class="far fa-calendar-alt"></i> {{ $l->created_at->diffForHumans() }}
                            </span>
                            <div class="flex items-center gap-1">
                                <div class="w-1 h-1 rounded-full bg-emerald-400"></div>
                                <span class="text-[9px] font-bold text-emerald-600/70 uppercase">Aktif</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-full py-16 text-center bg-white rounded-[2rem] border-2 border-dashed border-slate-100 transition-all">
                        <div
                            class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-200">
                            <i class="fas fa-folder-open text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 mb-1">Belum Ada Lowongan</h3>
                        <p class="text-slate-500 mb-6 max-w-xs mx-auto text-xs font-medium">Jadilah yang pertama untuk
                            memberikan peluang kerja.</p>
                        <a href="{{ route('public.loker.create') }}"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-500 text-white rounded-xl font-black text-xs shadow-lg shadow-emerald-100 hover:scale-105 transition-all">
                            <i class="fas fa-plus-circle"></i> Pasang Sekarang
                        </a>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($lokers->hasPages())
                <div class="mt-12 flex justify-center custom-pagination">
                    {{ $lokers->links() }}
                </div>
            @endif
        </section>
    </div>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Custom Pagination Styling */
        .custom-pagination nav>div:first-child {
            display: none;
        }

        .custom-pagination nav {
            display: flex;
            gap: 0.25rem;
        }

        .custom-pagination .relative.inline-flex {
            border-radius: 8px !important;
            border-color: #f1f5f9 !important;
            font-weight: 800;
            font-size: 11px;
            padding: 6px 10px;
        }

        .custom-pagination .bg-white {
            color: #64748b;
        }

        .custom-pagination .text-gray-500 {
            background: #10b981 !important;
            color: white !important;
            border-color: #10b981 !important;
        }
    </style>
@endsection