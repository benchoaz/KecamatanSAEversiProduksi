@extends('layouts.kecamatan')

@section('title', 'Pengaturan Backup & Recovery')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-cloud-arrow-up text-brand-600 me-2"></i> Pengaturan Backup Google Drive</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Tentukan lokasi folder Google Drive tempat file backup akan disimpan setiap malam secara otomatis. 
                        Pastikan remote <code class="bg-light px-2 py-1 rounded text-danger">gdrive</code> sudah terhubung di VPS Anda via perintah <code class="bg-light px-2 py-1 rounded text-primary">rclone config</code>.
                    </p>

                    <form action="{{ route('kecamatan.settings.backup.update') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="gdrive_path" class="form-label fw-bold">Alamat Folder (Rclone Path)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-folder-open text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0 @error('gdrive_path') is-invalid @enderror" 
                                    id="gdrive_path" name="gdrive_path" value="{{ old('gdrive_path', $gdrivePath) }}" 
                                    placeholder="gdrive:nama-folder/" required>
                            </div>
                            <div class="form-text mt-2">
                                <i class="fas fa-info-circle me-1"></i> Contoh format: <span class="text-primary font-monospace">gdrive:backup/kecamatan-files/</span>
                            </div>
                            @error('gdrive_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-brand-600 px-4 py-2 rounded-3 text-white">
                                <i class="fas fa-save me-2"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-4 bg-light">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-halved text-success fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 fw-bold">Keamanan Data</h6>
                            <p class="mb-0 small text-muted">Alamat Google Drive yang baru akan otomatis digunakan pada proses backup terjadwal berikutnya.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-brand-600 text-white">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb me-2"></i> Tips Backup</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle mt-1 me-2"></i>
                            <span>Gunakan folder khusus untuk setiap sistem agar data tidak tercampur.</span>
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-check-circle mt-1 me-2"></i>
                            <span>Pastikan kuota Google Drive Anda mencukupi untuk menampung file database dan dokumen.</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="fas fa-check-circle mt-1 me-2"></i>
                            <span>Cek secara berkala folder Google Drive Anda untuk memastikan file terunggah dengan benar.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
