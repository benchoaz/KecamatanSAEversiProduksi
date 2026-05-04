@extends('layouts.kecamatan')

@section('title', 'Rekapitulasi Siltap 17 Desa')

@section('content')
    <div class="content-header mb-5">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="{{ route('kecamatan.pemerintahan.index') }}"
                class="btn btn-xs btn-light rounded-pill px-3 text-secondary text-decoration-none border shadow-sm">
                <i class="fas fa-arrow-left-long me-2"></i> Kembali ke Menu Utama
            </a>
        </div>
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <h2 class="fw-bold text-primary-900 mb-1">Rekapitulasi Siltap 17 Desa</h2>
                <p class="text-tertiary mb-0">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Konsolidasi data penghasilan tetap perangkat desa se-Kecamatan.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('kecamatan.pemerintahan.detail.rekap-siltap.download') }}" class="btn btn-brand-600 text-white rounded-pill px-4 shadow-premium">
                    <i class="fas fa-file-pdf me-2"></i> Cetak Rekapitulasi & Pengantar
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary-900 text-white">
                <div class="small opacity-75 mb-1">Total Pagu Siltap Tahunan (17 Desa)</div>
                <h3 class="fw-bold mb-0">Rp {{ number_format($desas->sum('pagu_siltap'), 0, ',', '.') }}</h3>
                <div class="mt-3 x-small">
                    <span class="bg-white bg-opacity-25 px-2 py-1 rounded-pill">Target Anggaran 2026</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-emerald-600 text-white">
                <div class="small opacity-75 mb-1">Total Siltap Bulanan (Realasi)</div>
                <h3 class="fw-bold mb-0">Rp {{ number_format($desas->sum('total_siltap'), 0, ',', '.') }}</h3>
                <div class="mt-3 x-small">
                    <span class="bg-white bg-opacity-25 px-2 py-1 rounded-pill">Total Akumulasi 17 Desa</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-premium rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary-900 text-white small fw-bold">
                    <tr>
                        <th class="ps-4 py-3" style="width: 50px;">NO</th>
                        <th class="py-3">NAMA DESA</th>
                        <th class="py-3">NO REKENING DESA</th>
                        <th class="text-end py-3">PAGU SILTAP (Rp)</th>
                        <th class="text-end py-3">SILTAP BULANAN (Rp)</th>
                        <th class="text-center py-3">KETERANGAN</th>
                        <th class="text-end pe-4 py-3">AKSI</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($desas as $index => $desa)
                        <tr>
                            <td class="ps-4 text-secondary small fw-medium">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-primary-900 text-uppercase">{{ $desa->nama_desa }}</div>
                            </td>
                            <td class="small fw-medium text-secondary">
                                {{ $desa->rekening_desa ?? '-' }}
                            </td>
                            <td class="text-end fw-medium">
                                {{ number_format($desa->pagu_siltap ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold text-brand-600">
                                {{ number_format($desa->total_siltap ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-center small">
                                <span class="badge bg-light text-dark border">
                                    {{ $desa->kades_count }}K / {{ $desa->sekdes_count }}S / {{ $desa->staff_count }}P
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" 
                                    class="btn btn-sm btn-light rounded-pill px-3 shadow-sm border"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editModal{{ $desa->id }}">
                                    <i class="fas fa-edit me-1 small"></i> Finansial
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light">
                    <tr class="fw-bold text-primary-900 text-uppercase" style="font-size: 11px;">
                        <td colspan="3" class="ps-4 py-3 text-center">TOTAL KECAMATAN</td>
                        <td class="text-end py-3">Rp {{ number_format($desas->sum('pagu_siltap'), 0, ',', '.') }}</td>
                        <td class="text-end py-3 text-brand-600">Rp {{ number_format($desas->sum('total_siltap'), 0, ',', '.') }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection

@section('modal')
    <!-- Modals Section (Moved to dedicated @yield('modal') in layout to prevent backdrop issues) -->
    @foreach($desas as $desa)
        <div class="modal fade" id="editModal{{ $desa->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <form action="{{ route('kecamatan.pemerintahan.detail.rekap-siltap.update-pagu', $desa->id) }}" method="POST">
                        @csrf
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">Update Data Keuangan Desa {{ $desa->nama_desa }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body py-4">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary-900">Nomor Rekening Desa (Tetap)</label>
                                <input type="text" name="rekening_desa" class="form-control rounded-3 border-primary-100" 
                                    value="{{ $desa->rekening_desa }}" placeholder="Masukkan Nomor Rekening" required>
                                <div class="form-text x-small text-muted">Data tersimpan permanen di profil desa.</div>
                            </div>
                            
                            <hr class="my-4 opacity-50">
                            <div class="small fw-bold text-secondary mb-3 text-uppercase">Standar Siltap Bulanan</div>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Siltap Kepala Desa (Rp)</label>
                                    <input type="number" name="siltap_kades" class="form-control rounded-3 bg-light border-0" 
                                        value="{{ (int)$desa->siltap_kades }}" placeholder="Contoh: 2426640" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Siltap Sekretaris Desa (Rp)</label>
                                    <input type="number" name="siltap_sekdes" class="form-control rounded-3 bg-light border-0" 
                                        value="{{ (int)$desa->siltap_sekdes }}" placeholder="Contoh: 2224420" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Siltap Perangkat / Staff (Rp)</label>
                                    <input type="number" name="siltap_perangkat" class="form-control rounded-3 bg-light border-0" 
                                        value="{{ (int)$desa->siltap_perangkat }}" placeholder="Contoh: 2022200" required>
                                </div>
                            </div>

                            <hr class="my-4 opacity-50">
                            <div class="mb-0">
                                <label class="form-label small fw-bold text-primary-900">Pagu Siltap Tahunan (Rp)</label>
                                <input type="number" name="pagu_siltap" class="form-control rounded-3 border-primary-100" 
                                    value="{{ (int)$desa->pagu_siltap }}" placeholder="Contoh: 300000000" required>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-brand-600 text-white rounded-pill px-4">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('styles')
    <style>
        .shadow-premium { box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05); }
        .text-primary-900 { color: #0f172a; }
        .btn-brand-600 { background-color: #2563eb; }
        .btn-brand-600:hover { background-color: #1d4ed8; }
        .text-brand-600 { color: #2563eb; }
        .x-small { font-size: 10px; }
    </style>
@endpush
