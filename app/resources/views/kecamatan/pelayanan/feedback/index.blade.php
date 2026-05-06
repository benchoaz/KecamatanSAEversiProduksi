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
            @if(auth()->user()->hasRole('Super Admin'))
                <form action="{{ route('kecamatan.pelayanan.clear-all') }}" method="POST" onsubmit="return confirm('Hapus seluruh data HASIL SURVEI? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    <input type="hidden" name="category" value="feedback">
                    <button type="submit" class="bg-rose-50 text-rose-600 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-rose-100 transition-colors">
                        <i class="fas fa-trash-alt me-1"></i> Bersihkan Data
                    </button>
                </form>
            @endif
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
                    <form id="bulk-delete-form" action="{{ route('kecamatan.pelayanan.bulk-destroy') }}" method="POST">
                    @csrf
                    <tr>
                        <th class="p-6" style="width: 40px;">
                            <input type="checkbox" class="checkbox checkbox-sm checkbox-primary" id="check-all">
                        </th>
                        <th class="text-[10px] font-black text-slate-400 uppercase">Warga / Layanan</th>
                        <th class="text-[10px] font-black text-slate-400 uppercase">Rating</th>
                        <th class="text-[10px] font-black text-slate-400 uppercase">Masukan / Komentar</th>
                        <th class="text-[10px] font-black text-slate-400 uppercase text-center">Tanggal</th>
                        <th class="text-[10px] font-black text-slate-400 uppercase text-end pe-6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feedbacks as $fb)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="p-6">
                            <input type="checkbox" name="ids[]" value="{{ $fb->id }}" class="checkbox checkbox-sm checkbox-primary check-item">
                        </td>
                        <td>
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
                        <td class="pe-6 text-end">
                            <button type="button" class="text-rose-400 hover:text-rose-600 transition-colors" 
                                onclick="deleteItem({{ $fb->id }})" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
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
            </form>
        </div>
        
        @if($feedbacks->hasPages())
        <div class="p-6 bg-slate-50 border-t border-slate-100">
            {{ $feedbacks->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Floating Bulk Actions -->
<div id="bulk-actions" class="fixed bottom-10 left-1/2 -translate-x-1/2 d-none" style="z-index: 1050;">
    <div class="bg-slate-900 text-white px-6 py-4 rounded-3xl shadow-2xl flex items-center gap-4 border border-slate-700/50 backdrop-blur-xl">
        <span class="text-xs font-black uppercase tracking-widest"><span id="selected-count">0</span> Data Terpilih</span>
        <div class="w-px h-6 bg-slate-700"></div>
        <button type="button" class="bg-rose-500 hover:bg-rose-600 text-white px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all" onclick="submitBulkDelete()">
            <i class="fas fa-trash-alt me-2"></i> Hapus Masal
        </button>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('check-all');
        const checkItems = document.querySelectorAll('.check-item');
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');

        function updateBulkVisibility() {
            const checked = document.querySelectorAll('.check-item:checked');
            if (checked.length > 0) {
                bulkActions.classList.remove('d-none');
                selectedCount.innerText = checked.length;
            } else {
                bulkActions.classList.add('d-none');
            }
        }

        if(checkAll) {
            checkAll.addEventListener('change', function() {
                checkItems.forEach(item => item.checked = this.checked);
                updateBulkVisibility();
            });
        }

        checkItems.forEach(item => {
            item.addEventListener('change', updateBulkVisibility);
        });
    });

    function deleteItem(id) {
        if (confirm('Hapus hasil survei ini?')) {
            const form = document.getElementById('delete-form');
            form.action = `/kecamatan/pelayanan/${id}`;
            form.submit();
        }
    }

    function submitBulkDelete() {
        if (confirm('Hapus seluruh data terpilih?')) {
            document.getElementById('bulk-delete-form').submit();
        }
    }
</script>
@endsection
