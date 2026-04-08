@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 font-sans relative overflow-hidden">
    <!-- Background Decorations -->
    <div class="fixed top-0 left-0 w-[500px] h-[500px] bg-teal-200/20 rounded-full blur-[100px] -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
    <div class="fixed bottom-0 right-0 w-[500px] h-[500px] bg-indigo-200/20 rounded-full blur-[100px] translate-x-1/2 translate-y-1/2 pointer-events-none"></div>

    <div class="w-full max-w-lg relative z-10">
        <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/60 border border-slate-100 overflow-hidden transform animate-fade-in">
            <!-- Header Accent -->
            <div class="h-3 w-full bg-gradient-to-r from-teal-400 via-emerald-500 to-indigo-500"></div>
            
            <div class="p-10 md:p-14 text-center">
                <!-- Success icon -->
                <div class="w-24 h-24 bg-emerald-50 rounded-[2rem] flex items-center justify-center mx-auto mb-8 relative group">
                    <div class="absolute inset-0 bg-emerald-100 rounded-[2rem] scale-110 opacity-0 group-hover:opacity-100 transition-all duration-500 blur-xl"></div>
                    <i class="fas fa-check-circle text-5xl text-emerald-500 relative z-10 animate-bounce"></i>
                </div>

                <h1 class="text-3xl font-black text-slate-800 mb-4 tracking-tight">Link Akses Terkirim!</h1>
                <p class="text-slate-500 font-medium mb-10 leading-relaxed">
                    Halo, <strong>{{ $umkm->nama_pemilik }}</strong>. <br>
                    Demi keamanan toko <strong>{{ $umkm->nama_usaha }}</strong>, kami telah mengirimkan link masuk eksklusif <i>(Magic Link)</i> ke nomor WhatsApp Anda.
                </p>

                <!-- Actions Container -->
                <div class="space-y-4">
                    <a href="https://wa.me/" 
                       target="_blank"
                       class="w-full bg-emerald-50 text-emerald-600 font-bold py-4 rounded-2xl hover:bg-emerald-100 transition-colors flex items-center justify-center gap-3 text-sm">
                        <i class="fab fa-whatsapp text-xl"></i>
                        <span>Buka WhatsApp Sekarang</span>
                    </a>
                </div>

                <!-- Guidance -->
                <div class="mt-12 p-6 bg-slate-50 rounded-3xl border border-slate-100 text-left relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <i class="fas fa-shield-alt text-4xl text-amber-500"></i>
                    </div>
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="fas fa-info-circle text-teal-500"></i> Keamanan Login
                    </h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed font-medium">
                        Bot Kecamatan telah mengirimkan link rahasia ke WhatsApp Anda. Silakan klik link tersebut untuk masuk secara aman. <b>Jangan membagikan</b> link rahasia tersebut kepada siapa pun untuk melindungi data Anda.
                    </p>
                </div>
            </div>
        </div>

        <p class="text-center mt-10 text-[10px] font-black text-slate-400 uppercase tracking-widest opacity-60">
            &copy; {{ date('Y') }} Kecamatan Digital • Akses Aman Terverifikasi
        </p>
    </div>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>
@endsection
