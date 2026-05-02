@extends('layouts.kecamatan')

@section('title', 'Hasil Survei Kepuasan Masyarakat')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Hasil Survei Kepuasan</h1>
            <p class="text-sm text-slate-500 font-medium uppercase tracking-widest mt-1">Monitoring Kualitas Layanan Publik</p>
        </div>
        <div class="flex gap-4">
            <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-4">
                <div class="w-10 h-10 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center text-lg">
                    <i class="fas fa-star"></i>
                </div>
                <div>
                    <div class="text-[10px] font-black text-slate-400 uppercase leading-none mb-1">Rata-rata Rating</div>
                    <div class="text-xl font-black text-slate-800">{{ number_format(\App\Models\PublicService::whereNotNull('rating')->avg('rating') ?? 0, 1) }} / 5.0</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Feedback List --}}
    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="text-[10px] font-black text-slate-400 uppercase p-6">Warga / Layanan</th>
                        <th class="text-[10px] font-black text-slate-400 uppercase">Rating</th>
                        <th class="text-[10px] font-black text-slate-400 uppercase">Masukan / Komentar</th>
                        <th class="text-[10px] font-black text-slate-400 uppercase text-center">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feedbacks as $fb)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-black">
                                    {{ substr($fb->nama_pemohon, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-black text-slate-800 text-sm">{{ $fb->nama_pemohon }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{{ $fb->jenis_layanan }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-[10px] {{ $i <= $fb->rating ? 'text-amber-400' : 'text-slate-200' }}"></i>
                                @endfor
                            </div>
                        </td>
                        <td>
                            <p class="text-xs text-slate-600 font-medium italic">"{{ $fb->citizen_feedback ?? 'Tidak ada komentar' }}"</p>
                        </td>
                        <td class="text-center">
                            <div class="text-xs font-bold text-slate-800">{{ $fb->feedback_at->format('d M Y') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $fb->feedback_at->format('H:i') }}</div>
                        </td>
                    </tr>
                    @endforeach

                    @if($feedbacks->isEmpty())
                    <tr>
                        <td colspan="4" class="p-20 text-center">
                            <div class="w-20 h-20 bg-slate-50 text-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-comment-slash text-3xl"></i>
                            </div>
                            <h3 class="text-slate-400 font-black uppercase tracking-widest text-sm">Belum ada masukan masuk</h3>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        @if($feedbacks->hasPages())
        <div class="p-6 bg-slate-50 border-t border-slate-100">
            {{ $feedbacks->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
