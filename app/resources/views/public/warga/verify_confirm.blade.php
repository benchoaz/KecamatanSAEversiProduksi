<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Akses - Kecamatan Digital</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
        }
        .pattern-grid {
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 40px 40px;
        }
        @keyframes pulse-soft {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        .animate-pulse-soft { animation: pulse-soft 3s infinite ease-in-out; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center relative overflow-hidden pattern-grid">

    <!-- Decorative Background -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-blue-400/10 rounded-full blur-[120px] -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-indigo-400/10 rounded-full blur-[120px] translate-x-1/2 translate-y-1/2"></div>
    </div>

    <!-- Main Card -->
    <div class="w-full max-w-[420px] p-6 relative z-10">
        <div class="glass-effect rounded-[2.5rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] border border-white/60 relative overflow-hidden">
            
            <!-- Top Decoration -->
            <div class="h-2 w-full bg-gradient-to-r from-emerald-400 via-blue-500 to-indigo-500"></div>

            <div class="p-8 md:p-10 text-center">
                <div class="relative mb-8">
                    <div class="w-24 h-24 bg-gradient-to-br from-emerald-50 to-white rounded-[2rem] shadow-xl border border-white mx-auto flex items-center justify-center transform -rotate-6 animate-pulse-soft">
                        <i class="fas fa-shield-check text-4xl text-transparent bg-clip-text bg-gradient-to-br from-emerald-500 to-teal-600"></i>
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center border border-slate-50">
                        <i class="fas fa-lock text-emerald-500 text-xs"></i>
                    </div>
                </div>

                <h1 class="text-2xl font-black text-slate-800 mb-3">Konfirmasi Akses</h1>
                <p class="text-slate-500 text-xs font-medium leading-relaxed mb-8 px-4">
                    Sistem mendeteksi permintaan masuk ke Dasbor Warga untuk nomor:
                    <span class="block mt-2 font-black text-slate-700 text-sm tracking-widest">+{{ substr($phone, 0, 4) }}xxxx{{ substr($phone, -4) }}</span>
                </p>

                <div class="bg-blue-50/50 rounded-3xl p-5 mb-8 border border-blue-100/50">
                    <p class="text-[10px] text-blue-600 font-black uppercase tracking-wider mb-2">Instruksi Aman</p>
                    <p class="text-[11px] text-slate-600 leading-relaxed font-medium">
                        Tekan tombol di bawah untuk melanjutkan ke Dashboard. Langkah ini untuk memastikan Anda adalah manusia, bukan bot otomatis.
                    </p>
                </div>

                <form action="{{ request()->fullUrl() }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full relative group overflow-hidden bg-slate-900 text-white font-bold py-5 rounded-2xl shadow-2xl shadow-slate-900/30 transform hover:-translate-y-1 active:scale-95 transition-all duration-300">
                        <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-emerald-500 to-teal-600 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></span>
                        <div class="relative flex items-center justify-center gap-3">
                            <span class="tracking-tight">Masuk ke Dashboard</span>
                            <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </button>
                </form>

                <p class="mt-8 text-[10px] text-slate-400 font-medium">
                    Tautan ini akan hangus setelah digunakan sekali.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-[10px] text-slate-400 mt-8 font-medium opacity-60">
            &copy; {{ date('Y') }} Keamanan Terpadu Kecamatan.
        </p>
    </div>

</body>
</html>
