@extends('layouts.hub')

@section('content')
<div class="container-fluid">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="h2 mb-2">Otomasi & Alur Kerja (n8n)</h1>
            <p class="text-muted" style="font-size: 16px;">Kelola integrasi otomatis antar aplikasi dan layanan pihak ketiga.</p>
        </div>
    </div>

    <div class="row">
        <!-- n8n Control Panel -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header py-3">
                    Status Server Otomasi
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="border p-3 text-center" style="width: 80px;">
                                <i class="fas fa-robot fa-2x"></i>
                            </div>
                        </div>
                        <div class="ms-4">
                            <div class="fw-bold fs-5">n8n Engine</div>
                            <div class="text-muted small">Versi: Centralized Hub Instance</div>
                            <div class="mt-2">
                                @if($is_online)
                                    <span class="fw-bold" style="color: #000;">● TERHUBUNG (ONLINE)</span>
                                @else
                                    <span style="color: #888;">○ TERPUTUS (OFFLINE)</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-4">
                        <p class="small text-muted mb-3">Gunakan tombol di bawah ini untuk membuka Editor Alur Kerja. Pastikan Bapak sudah masuk ke VPN (Tailscale) jika mengakses dari luar jaringan lokal.</p>
                        <a href="{{ $n8n_url }}" target="_blank" class="btn btn-dark w-100 p-3 fw-bold">
                            BUKA EDITOR n8n <i class="fas fa-external-link-alt ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documentation/Help -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header py-3">
                    Contoh Alur Kerja (Workflows)
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item p-4">
                            <div class="fw-bold">1. Broadcast Notifikasi WA</div>
                            <p class="text-muted small mb-0">Otomatis mengirim pesan WhatsApp ke warga saat berkas layanan selesai diproses di kecamatan.</p>
                        </div>
                        <div class="list-group-item p-4">
                            <div class="fw-bold">2. Sinkronisasi Data PMD</div>
                            <p class="text-muted small mb-0">Menarik data perangkat desa terbaru dari 24 kecamatan ke database pusat Kabupaten setiap jam 12 malam.</p>
                        </div>
                        <div class="list-group-item p-4">
                            <div class="fw-bold">3. Backup Database Global</div>
                            <p class="text-muted small mb-0">Melakukan backup terenkripsi ke Google Drive/Cloud storage untuk seluruh database kecamatan.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 p-4 border" style="background-color: #fafafa;">
                <h6 class="fw-bold"><i class="fas fa-shield-alt me-2"></i> Info Keamanan</h6>
                <p class="small text-muted mb-0">Setiap alur kerja menggunakan <strong>API Key</strong> yang dibuat di menu "Aplikasi Eksternal" untuk menjamin keamanan akses data antar dinas.</p>
            </div>
        </div>
    </div>
</div>
@endsection
