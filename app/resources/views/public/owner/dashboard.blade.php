@extends('layouts.public')

@section('title', 'Dashboard Pemilik Usaha')

@section('content')
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Dashboard Pemilik Usaha</h4>
                    <a href="{{ route('owner.logout') }}" class="btn btn-outline-secondary btn-sm">
                        Keluar
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4">
            <!-- UMKM/Jasa Card -->
            @if($umkm)
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">UMKM / Jasa</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold">{{ $umkm->name }}</h6>
                            <p class="text-muted small mb-3">
                                @if($umkm->module === 'jasa')
                                    Kategori: Jasa
                                @else
                                    Kategori: UMKM
                                @endif
                            </p>

                            @if($umkm->product)
                                <p class="small"><strong>Produk/Jasa:</strong> {{ $umkm->product }}</p>
                            @endif

                            @if($umkm->address)
                                <p class="small"><strong>Alamat:</strong> {{ $umkm->address }}</p>
                            @endif

                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-{{ $umkm->is_active ? 'success' : 'secondary' }}">
                                    {{ $umkm->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                @if($umkm->is_verified)
                                    <span class="badge bg-info">Terverifikasi</span>
                                @endif
                            </div>

                            <form action="{{ route('owner.toggle_umkm') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-{{ $umkm->is_active ? 'warning' : 'success' }} btn-sm">
                                    {{ $umkm->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                <span class="text-muted small ms-2">
                                    ({{ $umkm->is_active ? 'Tampil di pencarian' : 'Tidak tampil di pencarian' }})
                                </span>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Loker Card -->
            @if($loker)
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0">
                            <h5 class="mb-0">Lowongan Kerja</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold">{{ $loker->title }}</h6>
                            <p class="text-muted small mb-3">
                                Kategori: {{ $loker->job_category ?? 'Umum' }}
                            </p>

                            @if($loker->description)
                                <p class="small"><strong>Deskripsi:</strong> {{ Str::limit($loker->description, 100) }}</p>
                            @endif

                            @if($loker->contact_wa)
                                <p class="small"><strong>WhatsApp:</strong> {{ $loker->contact_wa }}</p>
                            @endif

                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-{{ $loker->status === 'aktif' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($loker->status) }}
                                </span>
                                @if($loker->is_verified)
                                    <span class="badge bg-info">Terverifikasi</span>
                                @endif
                            </div>

                            <form action="{{ route('owner.toggle_loker') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="btn btn-{{ $loker->status === 'aktif' ? 'warning' : 'success' }} btn-sm">
                                    {{ $loker->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                <span class="text-muted small ms-2">
                                    ({{ $loker->status === 'aktif' ? 'Tampil di pencarian' : 'Tidak tampil di pencarian' }})
                                </span>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- No Data -->
            @if(!$umkm && !$loker)
                <div class="col-12">
                    <div class="alert alert-warning">
                        <p class="mb-0">Anda belum memiliki data usaha. Silakan daftar terlebih dahulu.</p>
                        <a href="{{ route('economy.create') }}" class="btn btn-primary btn-sm mt-2">
                            Daftar Sekarang
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Aksi Cepat</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('owner.reset_pin') }}" class="btn btn-outline-primary btn-sm">
                                Ubah PIN
                            </a>
                            <a href="{{ route('economy.index') }}" class="btn btn-outline-secondary btn-sm">
                                Lihat Etalase
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection