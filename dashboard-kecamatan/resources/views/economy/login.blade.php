@extends('layouts.public')

@section('page_title', 'Masuk Pengelolaan Jasa')

@section('content')
<section class="min-h-screen bg-slate-50 flex items-center justify-center py-20 px-6">
    <div class="max-w-md w-full">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-teal-600 rounded-[2rem] shadow-xl shadow-teal-500/20 mb-6 rotate-3">
                <i class="fas fa-id-badge text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight mb-2">Kelola Data Jasa</h1>
            <p class="text-slate-500 font-medium">Masukkan nomor WhatsApp dan PIN Anda untuk mengelola data keahlian.</p>
        </div>

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-100 text-rose-600 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3 animate-shake">
                <i class="fas fa-exclamation-circle"></i>
                <span class="text-sm font-bold">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-slate-200 border border-slate-50">
            <form action="{{ route('economy.authenticate') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Nomor WhatsApp</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-teal-600 transition-colors">
                            <i class="fab fa-whatsapp text-lg"></i>
                        </div>
                        <input type="text" name="contact_phone" required placeholder="Contoh: 08123456789" value="{{ old('contact_phone') }}"
                            class="w-full bg-slate-50 border-2 border-slate-50 rounded-2xl pl-12 pr-6 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:border-teal-500/20 focus:ring-4 focus:ring-teal-500/10 transition-all outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">6 Digit PIN</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-teal-600 transition-colors">
                            <i class="fas fa-key text-lg"></i>
                        </div>
                        <input type="password" name="owner_pin" required maxlength="6" placeholder="Masukkan PIN"
                            class="w-full bg-slate-50 border-2 border-slate-50 rounded-2xl pl-12 pr-6 py-4 text-sm font-bold text-slate-700 focus:bg-white focus:border-teal-500/20 focus:ring-4 focus:ring-teal-500/10 transition-all outline-none tracking-[0.5em]">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-3 ml-1">PIN dikirimkan melalui WhatsApp saat Anda pertama kali mendaftar.</p>
                </div>

                <button type="submit" 
                    class="w-full bg-teal-600 hover:bg-teal-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-teal-500/30 transition-all transform hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-3">
                    Masuk Ke Pengelolaan <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('economy.index') }}" class="text-sm font-bold text-slate-400 hover:text-teal-600 transition-colors flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Kembali ke Hub Ekonomi
            </a>
        </div>
    </div>
</section>

<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}
.animate-shake { animation: shake 0.5s ease-in-out; }
</style>
@endsection
