@extends('layouts.hub')

@section('content')
<div class="container-fluid">
    <div class="row mb-5">
        <div class="col-12">
            <h1 class="h2 mb-2">Manajemen Aplikasi Eksternal</h1>
            <p class="text-muted" style="font-size: 16px;">Konfigurasi integrasi data antar instansi Kabupaten Probolinggo.</p>
        </div>
    </div>

    <div class="row">
        <!-- List of Apps -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span>Daftar Instansi Terhubung</span>
                    <small class="text-muted">Klik ikon mata untuk melihat secret</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Instansi / Dinas</th>
                                    <th>Kredensial API</th>
                                    <th>Izin (Scopes)</th>
                                    <th class="pe-4 text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apps as $app)
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td class="ps-4 py-4">
                                        <div class="fw-bold" style="font-size: 16px;">{{ $app->name }}</div>
                                        <div class="text-muted small">{{ $app->base_url ?? '-' }}</div>
                                    </td>
                                    <td class="py-4">
                                        <div class="mb-2">
                                            <small class="text-muted d-block">Client ID:</small>
                                            <code id="id_{{ $app->id }}">{{ $app->client_id }}</code>
                                            <button class="btn btn-sm p-0 ms-1" onclick="copyText('id_{{ $app->id }}')"><i class="far fa-copy"></i></button>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Client Secret:</small>
                                            <div class="d-flex align-items-center">
                                                <code id="sec_{{ $app->id }}" class="secret-text">********************</code>
                                                <input type="hidden" id="raw_sec_{{ $app->id }}" value="{{ $app->client_secret }}">
                                                <button class="btn btn-sm p-0 ms-2" onclick="toggleSecret('{{ $app->id }}')"><i class="far fa-eye" id="eye_{{ $app->id }}"></i></button>
                                                <button class="btn btn-sm p-0 ms-2" onclick="copySecret('{{ $app->id }}')"><i class="far fa-copy"></i></button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        @foreach($app->scopes as $scope)
                                            <span class="badge mb-1 me-1">
                                                {{ $scope }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="pe-4 py-4 text-end">
                                        @if($app->status == 'active')
                                            <span class="badge bg-dark text-white p-2 mb-2 d-block">AKTIF</span>
                                        @else
                                            <span class="badge bg-light text-dark border p-2 mb-2 d-block">NONAKTIF</span>
                                        @endif
                                        
                                        <form action="{{ route('hub.api.toggle', $app->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $app->status == 'active' ? 'btn-outline-danger' : 'btn-outline-dark' }} w-100">
                                                {{ $app->status == 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">Belum ada dinas yang didaftarkan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add New App -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header py-3">
                    Daftarkan Dinas Baru
                </div>
                <div class="card-body">
                    <form action="{{ route('hub.api.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nama Dinas / Instansi</label>
                            <input type="text" name="name" class="form-control p-2" placeholder="Contoh: Dinas PMD" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Izin Akses Data</label>
                            <div class="border p-3">
                                @php
                                    $available_scopes = [
                                        'PERANGKAT_DESA' => 'Data Perangkat & Desa',
                                        'LAYANAN_PUBLIK' => 'Data Layanan Publik',
                                        'EKONOMI_UMKM' => 'Data Ekonomi & UMKM',
                                        'DANA_DESA' => 'Data Keuangan Desa',
                                        'PENGADUAN' => 'Data Pengaduan Warga',
                                    ];
                                @endphp
                                @foreach($available_scopes as $val => $label)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="scopes[]" value="{{ $val }}" id="scope_{{ $val }}">
                                    <label class="form-check-label" for="scope_{{ $val }}">
                                        {{ $label }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">URL Aplikasi (Opsional)</label>
                            <input type="url" name="base_url" class="form-control p-2" placeholder="https://dinas.probolinggokab.go.id">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 p-3 fw-bold">
                            BUAT AKSES API
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyText(id) {
        var text = document.getElementById(id).innerText;
        navigator.clipboard.writeText(text).then(function() {
            alert('Copied to clipboard: ' + text);
        });
    }

    function toggleSecret(id) {
        var secElement = document.getElementById('sec_' + id);
        var rawSec = document.getElementById('raw_sec_' + id).value;
        var eyeIcon = document.getElementById('eye_' + id);

        if (secElement.innerText === '********************') {
            secElement.innerText = rawSec;
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            secElement.innerText = '********************';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    function copySecret(id) {
        var rawSec = document.getElementById('raw_sec_' + id).value;
        navigator.clipboard.writeText(rawSec).then(function() {
            alert('Secret copied to clipboard!');
        });
    }
</script>
@endsection
