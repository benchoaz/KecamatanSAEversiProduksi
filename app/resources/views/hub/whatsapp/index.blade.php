@extends('layouts.hub')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">🤖 WhatsApp Gateway & AI Hub</h1>
            <p class="text-muted">Kelola instruksi AI global dan perutean pesan antar kecamatan.</p>
        </div>
    </div>

    <div class="row">
        <!-- Global AI Instructions -->
        <div class="col-lg-8">
            <div class="card shadow mb-4 border-0 rounded-4">
                <div class="card-header py-3 bg-white border-0">
                    <h6 class="m-0 font-weight-bold text-primary">Instruksi AI Global (Kabupaten)</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('hub.whatsapp.config.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">System Prompt Utama</label>
                            <textarea name="value" class="form-control rounded-4" rows="10" placeholder="Contoh: Anda adalah asisten resmi Pemerintah Kabupaten Probolinggo. Gunakan bahasa yang sopan...">{{ $configs->where('key', 'global_system_prompt')->first()?->value }}</textarea>
                            <input type="hidden" name="key" value="global_system_prompt">
                            <small class="text-muted">Instruksi ini akan digabungkan dengan instruksi spesifik di tiap kecamatan.</small>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 rounded-3">
                            <i class="fas fa-save me-2"></i> Simpan Instruksi Global
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sticky Session Status -->
        <div class="col-lg-4">
            <div class="card shadow mb-4 border-0 rounded-4 bg-primary text-white">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-white bg-opacity-20 p-3 me-3">
                            <i class="fas fa-link fa-2x text-white"></i>
                        </div>
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase opacity-75">Sesi Aktif</div>
                            <div class="h3 mb-0 font-weight-bold">{{ \App\Models\Hub\HubWaSession::where('is_active', true)->count() }}</div>
                        </div>
                    </div>
                    <p class="small mb-0">Warga yang sedang terhubung ke kecamatan spesifik dalam 24 jam terakhir.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
