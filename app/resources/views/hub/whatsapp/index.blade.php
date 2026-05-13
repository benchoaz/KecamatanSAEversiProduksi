@extends('layouts.hub')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h3 mb-1" style="font-weight: 700;">WhatsApp Hub</h1>
            <p class="text-muted mb-0">Centralized message routing and AI handler for 24 districts.</p>
        </div>
        <div class="d-flex gap-3">
             @if($is_online)
                <span class="badge-status-active d-flex align-items-center"><i class="fas fa-check-circle me-2"></i> WAHA ONLINE</span>
            @else
                <span class="badge bg-light text-muted border-0 rounded-pill px-3 py-1 d-flex align-items-center" style="font-size: 11px;"><i class="fas fa-times-circle me-2"></i> WAHA OFFLINE</span>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Routing Configuration -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header border-0 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="text-muted small fw-bold text-uppercase mb-0">Intelligent Routing Logic</h6>
                    <button class="btn btn-sm btn-outline-dark" onclick="alert('Saving logic...')">Save Changes</button>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">AI SYSTEM PROMPT (GLOBAL ROUTER)</label>
                        <textarea class="form-control" rows="12" style="font-family: 'Fira Code', monospace; font-size: 13px; background: #fafafa; border: 1px solid #eee;">{{ $routing_logic ?? "Anda adalah AI Gateway Hub untuk Kabupaten Probolinggo. 
Tugas utama Anda adalah:
1. Mengidentifikasi asal kecamatan warga (Besuk, Kraksaan, Gending, dll).
2. Mengarahkan pertanyaan ke database kecamatan yang relevan.
3. Menjawab pertanyaan umum terkait layanan publik kabupaten.

Aturan Penting:
- Selalu gunakan bahasa yang sopan dan profesional.
- Jika warga menyebutkan nama desa, identifikasi kecamatannya terlebih dahulu." }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status & Stats -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    Connectivity
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted small">WAHA Instance</span>
                        <span class="fw-bold" style="font-size: 12px;">v2024.5.1</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted small">Server Load</span>
                        <span class="fw-bold text-success" style="font-size: 12px;">Healthy (12%)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Active Sessions</span>
                        <span class="fw-bold" style="font-size: 12px;">24 Districts</span>
                    </div>
                </div>
            </div>

            <div class="card bg-primary text-white border-0 shadow-lg">
                <div class="card-body p-4">
                    <div class="small fw-bold text-uppercase mb-2" style="opacity: 0.8;">Total Messages Today</div>
                    <div class="h2 mb-3 fw-bold">1,429</div>
                    <div class="small" style="opacity: 0.9;">
                        <i class="fas fa-arrow-up me-1"></i> 12% increase from yesterday
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
