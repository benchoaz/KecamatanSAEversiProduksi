@extends('layouts.hub')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1 fw-bold text-primary">Pusat Kendali Jaringan</h1>
                <p class="text-muted">Monitoring dan manajemen 24 jaringan kecamatan se-Kabupaten Probolinggo.</p>
            </div>
            <button class="btn btn-primary px-4 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addDistrictModal">
                <i class="fas fa-plus me-2"></i> TAMBAH KECAMATAN
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4 text-center">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 p-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Jaringan</div>
                <div class="h2 mb-0 fw-bold text-primary">{{ $districts->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 p-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Status Aktif</div>
                <div class="h2 mb-0 fw-bold text-success">{{ $districts->where('is_active', true)->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 p-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Kecamatan Offline</div>
                <div class="h2 mb-0 fw-bold text-danger">{{ $districts->where('is_active', false)->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Districts Table -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold text-primary">Daftar Kecamatan Terhubung</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="ps-4">Nama Kecamatan</th>
                            <th>Domain / Subdomain</th>
                            <th>Database</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($districts as $district)
                        <tr>
                            <td class="ps-4 py-4">
                                <div class="fw-bold" style="font-size: 16px;">{{ $district->name }}</div>
                            </td>
                            <td class="py-4">
                                <code class="text-primary">{{ $district->slug }}.kecamatansae.id</code>
                            </td>
                            <td class="py-4 text-muted small">{{ $district->db_name }}</td>
                            <td class="py-4">
                                @if($district->is_active)
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">● AKTIF</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">○ NONAKTIF</span>
                                @endif
                            </td>
                             <td class="text-end pe-4">
                                <a href="{{ str_replace('://', '://' . $district->slug . '.', config('app.url')) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-3 me-2">
                                    Panel
                                </a>
                                <form action="{{ route('hub.districts.toggle', $district) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-primary rounded-3">Switch</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Simple -->
<div class="modal fade" id="addDistrictModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-bottom px-4 pt-4">
                <h5 class="modal-title fw-bold">Tambah Kecamatan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hub.districts.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kecamatan</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="Contoh: Kecamatan Besuk" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Slug / Subdomain</label>
                        <input type="text" name="slug" class="form-control rounded-3" placeholder="Contoh: besuk" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Database</label>
                        <input type="text" name="db_name" class="form-control rounded-3" value="dashboard_kecamatan" required>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
