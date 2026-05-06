@extends('layouts.desa')

@section('title', 'Edit Draft Laporan')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="{{ route('desa.submissions.index') }}">Laporan</a></li>
                        <li class="breadcrumb-item active">Edit Draft</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-slate-800">Edit Draft Laporan</h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold text-slate-800 mb-0">Informasi Dasar</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('desa.submissions.update', $submission->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label fw-bold text-slate-700">Judul Laporan</label>
                                <input type="text" name="judul" class="form-control rounded-3" value="{{ $submission->judul }}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-slate-700">Kategori / Modul</label>
                                <input type="text" class="form-control rounded-3 bg-light" value="{{ ucfirst($submission->modul) }}" readonly>
                                <small class="text-muted italic">* Kategori tidak dapat diubah setelah draf dibuat.</small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold text-slate-700">Periode</label>
                                <input type="text" name="periode" class="form-control rounded-3" value="{{ $submission->periode }}">
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Section for Bukti Dukung / Files -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-slate-800 mb-0">Bukti Dukung / Lampiran</h5>
                        <button type="button" class="btn btn-sm btn-sky rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload me-1"></i> Upload File
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="small text-muted fw-bold">
                                        <th>NAMA FILE</th>
                                        <th>UKURAN</th>
                                        <th class="text-end">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($submission->buktiDukung ?? [] as $file)
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                                {{ $file->nama_file }}
                                            </td>
                                            <td>{{ $file->ukuran_label ?? 'N/A' }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('desa.submissions.file.destroy', $file->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Hapus lampiran ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-slate-400 small italic">Belum ada lampiran.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-emerald-50 mb-4 border border-emerald-100">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-emerald-800 mb-3"><i class="fas fa-paper-plane me-2"></i>Kirim Laporan</h6>
                        <p class="small text-emerald-700 mb-4">Pastikan semua data dan lampiran sudah lengkap sebelum mengirim ke Kecamatan.</p>
                        
                        <form action="{{ route('desa.submissions.submit', $submission->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-emerald text-white w-100 rounded-pill shadow-sm py-2 fw-bold" 
                                    onclick="return confirm('Kirim laporan ini?')">
                                KIRIM KE KECAMATAN
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-slate-50">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-slate-700 mb-3">Status Draft</h6>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white rounded-circle p-2 shadow-sm text-slate-400">
                                <i class="fas fa-edit fa-lg"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-slate-800">Drafting</div>
                                <div class="x-small text-slate-500 italic">Dibuat: {{ $submission->created_at->format('d M Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .btn-sky { background-color: #0ea5e9; color: white; }
    .btn-sky:hover { background-color: #0284c7; color: white; }
    .btn-emerald { background-color: #10b981; }
    .btn-emerald:hover { background-color: #059669; }
    .bg-emerald-50 { background-color: #ecfdf5; }
    .text-emerald-800 { color: #065f46; }
    .text-emerald-700 { color: #047857; }
    .border-emerald-100 { border-color: #d1fae5 !important; }
    .x-small { font-size: 0.75rem; }
</style>
