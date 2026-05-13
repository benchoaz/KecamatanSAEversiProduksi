@extends('layouts.hub')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">🚀 Dashboard Kabupaten Gateway</h1>
            <p class="text-muted">Selamat datang di sistem kendali terpusat Kabupaten Probolinggo.</p>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 py-2 border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Jaringan Kecamatan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Hub\HubDistrict::count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-city fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100 py-2 border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Sesi WhatsApp Aktif</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \App\Models\Hub\HubWaSession::where('is_active', true)->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fab fa-whatsapp fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('hub.districts.index') }}" class="list-group-item list-group-item-action border-0 px-0">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                        <i class="fas fa-plus text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Tambah Kecamatan Baru</h6>
                                        <small class="text-muted">Daftarkan subdomain dan database baru.</small>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-muted small"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
