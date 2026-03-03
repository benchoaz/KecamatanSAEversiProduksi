@extends('layouts.kecamatan')

@section('title', 'Monitoring Laporan Penyelenggaraan Desa')

@section('content')
    <div class="content-header mb-5">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="{{ auth()->user()->desa_id ? route('desa.pemerintahan.index') : route('kecamatan.pemerintahan.index') }}"
                class="btn btn-xs btn-light rounded-pill px-3 text-secondary text-decoration-none border shadow-sm">
                <i class="fas fa-arrow-left-long me-2"></i> Kembali ke Menu Utama
            </a>
        </div>
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <h2 class="fw-bold text-primary-900 mb-1">Monitoring Laporan Penyelenggaraan Desa</h2>
                <p class="text-tertiary mb-0">
                    @if($desa_id)
                        <i class="fas fa-file-signature me-1"></i> Pemantauan status penyampaian berbagai laporan tahunan &
                        pertanggungjawaban desa.
                    @else
                        <i class="fas fa-map-location-dot me-1"></i> Pilih Desa untuk Melihat Status Penyampaian Laporan.
                    @endif
                </p>
            </div>
            @if($desa_id)
                <button class="btn btn-brand-600 text-white rounded-pill px-4 shadow-premium" data-bs-toggle="modal"
                    data-bs-target="#uploadLaporanModal">
                    <i class="fas fa-upload me-2"></i> Arsipkan Laporan
                </button>
            @endif
        </div>
    </div>

    @if(!$desa_id)
        <div class="card bg-white border-gray-200 shadow-sm rounded-4 overflow-hidden mt-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small fw-bold">
                        <tr>
                            <th class="ps-4" style="width: 50px;">No</th>
                            <th>Nama Desa</th>
                            <th class="text-center">Laporan Tersimpan</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($desas as $index => $desa)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                                <td class="fw-bold text-slate-700"> Desa {{ $desa->nama_desa }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary-soft text-primary px-3 py-2"
                                        style="font-size: 0.85rem;">{{ $desa->dokumens_count }} Laporan</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ url()->current() }}?desa_id={{ $desa->id }}"
                                        class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Lihat Detail <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="card bg-white border-gray-200 shadow-sm rounded-4 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4">Tahun Laporan</th>
                                    <th>Jenis Dokumen</th>
                                    <th>Tgl Disampaikan</th>
                                    <th>Status Verifikasi</th>
                                    <th>File</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laporans as $l)
                                    <tr>
                                        <td><span class="year-badge-sm">{{ $l->tahun }}</span></td>
                                        <td>
                                            <div class="fw-bold">{{ $l->tipe_label }}</div>
                                            <div class="text-slate-500 small">{{ $l->nomor_dokumen }}</div>
                                        </td>
                                        <td>{{ $l->tanggal_penyampaian ? $l->tanggal_penyampaian->format('d/m/Y') : '-' }}</td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'draft' => 'bg-secondary-soft text-secondary',
                                                    'dikirim' => 'bg-info-soft text-info',
                                                    'dikembalikan' => 'bg-warning-soft text-warning',
                                                    'diterima' => 'bg-success-soft text-success',
                                                ][$l->status] ?? 'bg-secondary-soft text-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClass }} text-uppercase">{{ $l->status_label }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('kecamatan.file.dokumen', $l->id) }}" target="_blank"
                                                class="btn btn-xs btn-outline-primary">
                                                <i class="fas fa-file-pdf"></i> Lihat PDF
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            @if($l->status == 'dikirim')
                                                <button class="btn btn-brand-600 text-white btn-sm rounded-pill px-3"
                                                    onclick="openVerifyModal({{ $l->id }}, '{{ $l->tipe_label }}', '{{ $l->tahun }}')">
                                                    <i class="fas fa-check-circle me-1"></i> Verifikasi
                                                </button>
                                            @else
                                                <button class="btn btn-icon btn-sm"
                                                    onclick="openVerifyModal({{ $l->id }}, '{{ $l->tipe_label }}', '{{ $l->tahun }}', '{{ $l->status }}', '{{ $l->catatan }}')">
                                                    <i class="fas fa-search-plus"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-file-signature fa-3x mb-3 text-muted"></i>
                                            <p class="text-muted">Belum ada laporan yang diarsipkan.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Verifikasi -->
        <div class="modal fade" id="verifyLaporanModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="verifyTitle">Verifikasi Laporan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="verifyForm" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info border-0 small mb-4">
                                <i class="fas fa-info-circle me-1"></i> Tinjau dokumen sebelum memberikan status verifikasi.
                            </div>

                            <div id="infoLaporan" class="mb-4 bg-light p-3 rounded-3">
                                <div class="row">
                                    <div class="col-6 small text-muted">Laporan:</div>
                                    <div class="col-6 small text-muted text-end">Tahun:</div>
                                    <div class="col-6 fw-bold" id="labelLaporan">-</div>
                                    <div class="col-6 fw-bold text-end" id="tahunLaporan">-</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Keputusan Verifikasi</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check cursor-pointer">
                                        <input class="form-check-input" type="radio" name="status" id="statusTerima"
                                            value="diterima" required>
                                        <label class="form-check-label text-success fw-bold" for="statusTerima">
                                            Terima / Sah
                                        </label>
                                    </div>
                                    <div class="form-check cursor-pointer">
                                        <input class="form-check-input" type="radio" name="status" id="statusTolak"
                                            value="dikembalikan">
                                        <label class="form-check-label text-danger fw-bold" for="statusTolak">
                                            Kembalikan / Revisi
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-0" id="catatanWrapper">
                                <label class="form-label fw-bold">Catatan / Alasan (Opsional jika diterima)</label>
                                <textarea name="catatan" id="catatanField" class="form-control" rows="4"
                                    placeholder="Berikan instruksi revisi jika dikembalikan..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" id="btnSubmitVerify" class="btn btn-primary">Simpan Keputusan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        function openVerifyModal(id, label, tahun, status = '', catatan = '') {
            const modal = new bootstrap.Modal(document.getElementById('verifyLaporanModal'));
            const form = document.getElementById('verifyForm');
            const labelEl = document.getElementById('labelLaporan');
            const tahunEl = document.getElementById('tahunLaporan');
            const catField = document.getElementById('catatanField');
            const submitBtn = document.getElementById('btnSubmitVerify');

            // Set Form URL
            form.action = `/kecamatan/pemerintahan/detail/laporan/${id}/verify`;

            // Set Info
            labelEl.innerText = label;
            tahunEl.innerText = tahun;
            catField.value = catatan || '';

            // Reset decision radio buttons
            document.getElementById('statusTerima').checked = false;
            document.getElementById('statusTolak').checked = false;

            if (status === 'diterima') {
                document.getElementById('statusTerima').checked = true;
            } else if (status === 'dikembalikan') {
                document.getElementById('statusTolak').checked = true;
            }

            // If already processed, maybe disable submit?
            // For now, allow re-verification

            modal.show();
        }
    </script>
@endpush

<!-- Modal Upload -->
<div class="modal fade" id="uploadLaporanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Arsip Laporan Tahunan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('kecamatan.pemerintahan.detail.dokumen.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="desa_id" value="{{ $desa_id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Jenis Laporan</label>
                            <select name="tipe_dokumen" class="form-select" required>
                                <option value="LPPD">LPPD Akhir Tahun Anggaran</option>
                                <option value="LPPD_AMJ">LPPD Akhir Masa Jabatan (LPPD-AMJ)</option>
                                <option value="LKPPD">LKPPD (Keterangan Pemerintah Desa)</option>
                                <option value="LPJ_APBDes">LPJ Realisasi APBDesa</option>
                                <option value="IPPD">Informasi Penyelenggaraan (IPPD)</option>
                                <option value="BUMDes">Laporan BUMDes</option>
                                <option value="Rekap_Penduduk">Laporan Rekapitulasi Penduduk</option>
                                <option value="LKPJ">LKPJ (Archive)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Laporan</label>
                            <input type="number" name="tahun" class="form-control" value="{{ date('Y') - 1 }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Penyampaian</label>
                            <input type="date" name="tanggal_penyampaian" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pilih File PDF (Max 5MB)</label>
                            <input type="file" name="file_dokumen" class="form-control" accept="application/pdf"
                                required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>



@push('styles')
    <link rel="stylesheet" href="{{ asset('css/menu-pages.css') }}">
    <style>
        .year-badge-sm {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .bg-info-soft {
            background-color: rgba(6, 182, 212, 0.1);
        }

        .bg-success-soft {
            background-color: rgba(16, 185, 129, 0.1);
        }

        .bg-warning-soft {
            background-color: rgba(245, 158, 11, 0.1);
        }

        .btn-xs {
            padding: 0.2rem 0.6rem;
            font-size: 0.75rem;
        }

        .btn-teal {
            background-color: #14b8a6;
            color: white;
        }

        .btn-teal:hover {
            background-color: #0d9488;
            color: white;
        }
    </style>
@endpush