@extends('layouts.desa')

@section('title', 'Buat Laporan Baru')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h4 class="fw-bold text-slate-800 mb-0">Buat Laporan Baru</h4>
                        <p class="text-slate-500 small">Pilih kategori dan judul untuk memulai draf laporan.</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('desa.submissions.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold text-slate-700">Judul Laporan</label>
                                <input type="text" name="judul" class="form-control rounded-3" placeholder="Contoh: Laporan Kegiatan Tahap I 2025" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold text-slate-700">Kategori / Modul</label>
                                <select name="modul" class="form-select rounded-3" required>
                                    <option value="" disabled selected>Pilih Kategori...</option>
                                    <option value="pembangunan">Pembangunan</option>
                                    <option value="blt">BLT Desa</option>
                                    <option value="kesra">Kesra</option>
                                    <option value="trantibum">Trantibum</option>
                                    <option value="umum">Umum / Lainnya</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold text-slate-700">Periode (Opsional)</label>
                                <input type="text" name="periode" class="form-control rounded-3" placeholder="Contoh: Semester I / 2025">
                            </div>
                            
                            <div class="d-flex justify-content-between mt-5">
                                <a href="{{ route('desa.submissions.index') }}" class="btn btn-light rounded-pill px-4">Batal</a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Mulai Draft</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
