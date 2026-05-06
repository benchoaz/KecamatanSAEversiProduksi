@extends('layouts.desa')

@section('title', 'Daftar Laporan Desa')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold text-slate-800">Daftar Laporan & Pengajuan</h2>
                <p class="text-slate-500">Kumpulan laporan yang telah atau sedang dikirimkan ke Kecamatan.</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('desa.submissions.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="fas fa-plus me-2"></i>Buat Laporan Baru
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-slate-50 text-slate-600 fw-bold small uppercase">
                            <tr>
                                <th class="px-4 py-3">Judul Laporan</th>
                                <th class="py-3">Modul / Jenis</th>
                                <th class="py-3">Periode</th>
                                <th class="py-3">Status</th>
                                <th class="py-3 text-end px-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($submissions as $item)
                                <tr>
                                    <td class="px-4">
                                        <div class="fw-bold text-slate-800">{{ $item->judul }}</div>
                                        <div class="text-slate-400 small">Dibuat: {{ $item->created_at->format('d/m/Y') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-slate-100 text-slate-600 rounded-pill px-3 py-2 small fw-normal">
                                            {{ ucfirst($item->modul) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-slate-700">{{ $item->periode ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($item->status) {
                                                'draft' => 'bg-slate-100 text-slate-600',
                                                'submitted' => 'bg-blue-100 text-blue-600',
                                                'returned' => 'bg-amber-100 text-amber-600',
                                                'approved' => 'bg-emerald-100 text-emerald-600',
                                                default => 'bg-slate-100 text-slate-600'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }} rounded-pill px-3 py-2 fw-normal">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end px-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('desa.submissions.show', $item->id) }}" 
                                               class="btn btn-sm btn-outline-slate rounded-pill px-3">
                                                Detail
                                            </a>
                                            
                                            @if($item->isEditable())
                                                <a href="{{ route('desa.submissions.edit', $item->id) }}" 
                                                   class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('desa.submissions.submit', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3" 
                                                            onclick="return confirm('Kirim laporan ini ke Kecamatan?')">
                                                        Kirim
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('desa.submissions.destroy', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                                            onclick="return confirm('Hapus draf laporan ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-5 text-center text-slate-400">
                                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                                        <p class="mb-0">Belum ada laporan yang dibuat.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($submissions->hasPages())
                <div class="card-footer bg-white py-3 px-4">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

<style>
    .btn-outline-slate {
        border-color: #cbd5e1;
        color: #64748b;
    }
    .btn-outline-slate:hover {
        background-color: #f8fafc;
        color: #334155;
    }
</style>
