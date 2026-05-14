@extends('layouts.hub')

@section('content')
<div class="container-fluid py-2">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-5 bg-white p-4 rounded-4 shadow-sm border border-slate-100">
        <div>
            <h1 class="h4 mb-1 fw-black text-slate-800 tracking-tight">Pusat Kendali Jaringan</h1>
            <p class="text-slate-500 small mb-0 font-medium">Manajemen integrasi data 24 kecamatan se-Kabupaten Probolinggo.</p>
        </div>
        <button class="btn btn-primary px-4 py-2 rounded-3 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addDistrictModal">
            <i class="fas fa-plus-circle"></i>
            <span class="fw-bold small">TAMBAH KECAMATAN</span>
        </button>
    </div>

    <div class="row g-4 mb-5">
        <!-- Stats Card 1 -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-3 d-flex align-items-center justify-center" style="width: 45px; height: 45px; background: #eff6ff; color: #2563eb; border-radius: 12px; display: flex; justify-content: center; align-items: center;">
                            <i class="fas fa-server"></i>
                        </div>
                        <span class="text-slate-500 small font-bold text-uppercase tracking-wider">Total Node</span>
                    </div>
                    <div class="h2 mb-0 fw-black text-slate-800">{{ $districts->count() }}</div>
                </div>
            </div>
        </div>
        <!-- Stats Card 2 -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-body p-4 border-start border-success border-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-3 d-flex align-items-center justify-center" style="width: 45px; height: 45px; background: #ecfdf5; color: #10b981; border-radius: 12px; display: flex; justify-content: center; align-items: center;">
                            <i class="fas fa-signal"></i>
                        </div>
                        <span class="text-slate-500 small font-bold text-uppercase tracking-wider">Online</span>
                    </div>
                    <div class="h2 mb-0 fw-black text-slate-800">{{ $districts->where('is_active', true)->count() }}</div>
                </div>
            </div>
        </div>
        <!-- Stats Card 3 -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-body p-4 border-start border-rose-400 border-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-3 d-flex align-items-center justify-center" style="width: 45px; height: 45px; background: #fff1f2; color: #f43f5e; border-radius: 12px; display: flex; justify-content: center; align-items: center;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <span class="text-slate-500 small font-bold text-uppercase tracking-wider">Offline</span>
                    </div>
                    <div class="h2 mb-0 fw-black text-slate-800">{{ $districts->where('is_active', false)->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white p-4 border-bottom border-slate-100">
            <h5 class="mb-0 fw-black text-slate-800 h6">DAFTAR KECAMATAN TERINTEGRASI</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-slate-500 small font-bold text-uppercase">Informasi Node</th>
                            <th class="py-3 text-slate-500 small font-bold text-uppercase text-center">Infrastruktur</th>
                            <th class="py-3 text-slate-500 small font-bold text-uppercase text-center">Konektivitas</th>
                            <th class="px-4 py-3 text-slate-500 small font-bold text-uppercase text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($districts as $district)
                        <tr class="border-bottom border-slate-50">
                            <td class="px-4 py-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="w-10 h-10 bg-slate-100 rounded-circle d-flex align-items-center justify-center text-slate-600 font-bold small" style="width: 35px; height: 35px; background: #f1f5f9; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                                        {{ substr($district->name, 10, 1) ?: substr($district->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-black text-slate-800">{{ $district->name }}</div>
                                        <div class="text-slate-400 small">{{ $district->slug }}.kecamatansae.id</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center py-4">
                                <div class="badge bg-slate-100 text-slate-600 px-3 py-2 rounded-3 border border-slate-200">
                                    <i class="fas fa-database me-1 small"></i> {{ $district->db_name }}
                                </div>
                            </td>
                            <td class="text-center py-4">
                                @if($district->is_active)
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-pill border border-emerald-100 small font-bold">
                                        <span class="w-2 h-2 bg-emerald-500 rounded-circle animate-pulse"></span>
                                        ONLINE
                                    </div>
                                @else
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-slate-50 text-slate-400 rounded-pill border border-slate-100 small font-bold">
                                        <span class="w-2 h-2 bg-slate-300 rounded-circle"></span>
                                        OFFLINE
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 text-end py-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ str_replace('://', '://' . $district->slug . '.', config('app.url')) }}" target="_blank" class="btn btn-sm btn-white border rounded-3 shadow-xs font-bold text-slate-600">
                                        <i class="fas fa-external-link-alt me-1"></i> Dashboard
                                    </a>
                                    <form action="{{ route('hub.districts.toggle', $district) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm {{ $district->is_active ? 'btn-rose-subtle' : 'btn-primary-subtle' }} rounded-3 font-bold" style="{{ $district->is_active ? 'background: #fff1f2; color: #e11d48;' : 'background: #eff6ff; color: #2563eb;' }}">
                                            {{ $district->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-slate-400 font-medium">
                                <i class="fas fa-ghost mb-3 fs-3 d-block"></i>
                                Belum ada kecamatan terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('modals')
<!-- Modal Refined -->
<div class="modal fade" id="addDistrictModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-2xl border-0 overflow-hidden" style="background: white !important; z-index: 9999 !important;">
            <div class="modal-header bg-slate-50 p-4 border-bottom">
                <h5 class="modal-title fw-black text-slate-800">TAMBAH KECAMATAN BARU</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('hub.districts.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label small font-black text-slate-500 uppercase tracking-widest">NAMA KECAMATAN</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Kecamatan Besuk" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small font-black text-slate-500 uppercase tracking-widest">SUBDOMAIN / SLUG</label>
                        <div class="input-group">
                            <input type="text" name="slug" class="form-control" placeholder="besuk" required>
                            <span class="input-group-text bg-slate-50 text-slate-400 font-medium small">.kecamatansae.id</span>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small font-black text-slate-500 uppercase tracking-widest">IDENTIFIKASI DATABASE</label>
                        <input type="text" name="db_name" class="form-control font-monospace" value="dashboard_kecamatan" required>
                        <div class="form-text small italic">Gunakan default jika tidak ada perubahan skema.</div>
                    </div>
                </div>
                <div class="modal-footer bg-slate-50 p-4 border-top">
                    <button type="button" class="btn btn-link text-slate-400 text-decoration-none fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5 rounded-3 fw-bold shadow-sm">SIMPAN DATA NODE</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection
