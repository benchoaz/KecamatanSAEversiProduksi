@extends('layouts.hub')

@section('content')
<div class="container-fluid">
    <div class="row mb-5">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 mb-2">Pusat Kendali Kabupaten</h1>
                <p class="text-muted" style="font-size: 16px;">Monitoring dan manajemen 24 jaringan kecamatan se-Kabupaten Probolinggo.</p>
            </div>
            <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addDistrictModal">
                TAMBAH KECAMATAN
            </button>
        </div>
    </div>

    <!-- Simple Stats -->
    <div class="row mb-5 text-center">
        <div class="col-md-4">
            <div class="border p-4">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Jaringan</div>
                <div class="h2 mb-0 fw-bold">{{ $districts->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border p-4">
                <div class="text-muted small fw-bold text-uppercase mb-1">Status Aktif</div>
                <div class="h2 mb-0 fw-bold text-success">{{ $districts->where('is_active', true)->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border p-4">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Pengaduan (Global)</div>
                <div class="h2 mb-0 fw-bold">1,248</div>
            </div>
        </div>
    </div>

    <!-- Districts Table -->
    <div class="card">
        <div class="card-header py-3">
            Daftar Jaringan Kecamatan
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Nama Kecamatan</th>
                            <th>Subdomain / Alamat</th>
                            <th>Database</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($districts as $district)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td class="ps-4 py-4">
                                <div class="fw-bold" style="font-size: 16px;">{{ $district->name }}</div>
                            </td>
                            <td class="py-4">
                                <code style="color: #000;">{{ $district->slug }}.{{ str_replace(['http://', 'https://'], '', config('app.url')) }}</code>
                            </td>
                            <td class="py-4 text-muted">{{ $district->db_name }}</td>
                            <td class="py-4">
                                @if($district->is_active)
                                    <span class="fw-bold" style="color: #000;">● AKTIF</span>
                                @else
                                    <span style="color: #888;">○ NONAKTIF</span>
                                @endif
                            </td>
                             <td class="pe-4 py-4 text-end">
                                <a href="{{ str_replace('://', '://' . $district->slug . '.', config('app.url')) }}" target="_blank" class="btn btn-sm btn-outline-dark me-2">
                                    Buka Panel
                                </a>
                                <form action="{{ route('hub.districts.toggle', $district) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-dark">Switch</button>
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
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Input Data Kecamatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('hub.districts.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nama Kecamatan</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Subdomain / Slug</label>
                        <input type="text" name="slug" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Database Name</label>
                        <input type="text" name="db_name" class="form-control" value="dashboard_kecamatan" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-dark px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
