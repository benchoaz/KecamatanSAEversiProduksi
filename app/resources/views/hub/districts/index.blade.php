@extends('layouts.hub')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 text-gray-800">🏛️ Kabupaten Gateway Hub</h1>
                <p class="text-muted">Pusat kendali layanan digital 24 kecamatan.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDistrictModal">
                <i class="fas fa-plus"></i> Tambah Kecamatan
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Kecamatan</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $districts->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Kecamatan Aktif</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $districts->where('is_active', true)->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Districts Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Jaringan Kecamatan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nama Kecamatan</th>
                            <th>Subdomain</th>
                            <th>Database</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($districts as $district)
                        <tr>
                            <td>{{ $district->name }}</td>
                            <td><code>{{ $district->slug }}.{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</code></td>
                            <td>{{ $district->db_name }}</td>
                            <td>
                                <span class="badge badge-{{ $district->is_active ? 'success' : 'danger' }}">
                                    {{ $district->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                             <td>
                                <a href="{{ str_replace('://', '://' . $district->slug . '.', config('app.url')) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="fas fa-external-link-alt"></i> Masuk Dashboard
                                </a>
                                <form action="{{ route('hub.districts.toggle', $district) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-warning">Switch Status</button>
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

<!-- Modal Tambah Kecamatan -->
<div class="modal fade" id="addDistrictModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">Tambah Kecamatan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hub.districts.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kecamatan</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="Contoh: Kecamatan Paiton" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subdomain / Slug</label>
                        <input type="text" name="slug" class="form-control rounded-3" placeholder="Contoh: paiton" required>
                        <small class="text-muted">Gunakan huruf kecil tanpa spasi.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Database</label>
                        <input type="text" name="db_name" class="form-control rounded-3" value="dashboard_kecamatan" required>
                        <small class="text-muted">Nama database yang menyimpan data kecamatan ini.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan Kecamatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
