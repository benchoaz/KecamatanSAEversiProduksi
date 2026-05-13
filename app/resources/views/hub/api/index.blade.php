@extends('layouts.hub')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-1 fw-bold text-primary">Aplikasi Eksternal</h1>
            <p class="text-muted">Kelola integrasi data dengan dinas-dinas Kabupaten Probolinggo.</p>
        </div>
    </div>

    <div class="row">
        <!-- List of Apps -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary">Instansi Terhubung</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr class="bg-light">
                                    <th class="ps-4">Dinas / App</th>
                                    <th>Kredensial API</th>
                                    <th>Izin Data</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apps as $app)
                                <tr>
                                    <td class="ps-4 py-4">
                                        <div class="fw-bold">{{ $app->name }}</div>
                                        <small class="text-muted">{{ $app->base_url ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div class="mb-2">
                                            <span class="text-xs text-muted">Client ID:</span>
                                            <code id="id_{{ $app->id }}" class="ms-1">{{ $app->client_id }}</code>
                                            <button class="btn btn-link btn-sm p-0 ms-1" onclick="copyText('id_{{ $app->id }}')"><i class="far fa-copy"></i></button>
                                        </div>
                                        <div>
                                            <span class="text-xs text-muted">Secret:</span>
                                            <code id="sec_{{ $app->id }}" class="ms-1 text-muted">********************</code>
                                            <input type="hidden" id="raw_sec_{{ $app->id }}" value="{{ $app->client_secret }}">
                                            <button class="btn btn-link btn-sm p-0 ms-1 text-muted" onclick="toggleSecret('{{ $app->id }}')"><i class="far fa-eye" id="eye_{{ $app->id }}"></i></button>
                                            <button class="btn btn-link btn-sm p-0 ms-1" onclick="copySecret('{{ $app->id }}')"><i class="far fa-copy"></i></button>
                                        </div>
                                    </td>
                                    <td>
                                        @foreach($app->scopes as $scope)
                                            <span class="badge bg-info-subtle text-info border-0 rounded-pill px-2 mb-1" style="font-size: 10px;">
                                                {{ $scope }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="mb-2">
                                            @if($app->status == 'active')
                                                <span class="badge bg-success text-white">AKTIF</span>
                                            @else
                                                <span class="badge bg-secondary text-white">NONAKTIF</span>
                                            @endif
                                        </div>
                                        <form action="{{ route('hub.api.toggle', $app->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $app->status == 'active' ? 'btn-outline-danger' : 'btn-primary' }} rounded-3">
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
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-primary">Daftarkan Dinas</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('hub.api.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Instansi</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Contoh: Dinas PMD" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Izin Akses Data</label>
                            <div class="bg-light p-3 rounded-3 border">
                                @php
                                    $available_scopes = [
                                        'PERANGKAT_DESA' => 'Data Perangkat Desa',
                                        'LAYANAN_PUBLIK' => 'Data Layanan Publik',
                                        'EKONOMI_UMKM' => 'Data Ekonomi & UMKM',
                                        'DANA_DESA' => 'Data Keuangan Desa',
                                        'PENGADUAN' => 'Data Pengaduan Warga',
                                    ];
                                @endphp
                                @foreach($available_scopes as $val => $label)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="scopes[]" value="{{ $val }}" id="scope_{{ $val }}">
                                    <label class="form-check-label small" for="scope_{{ $val }}">{{ $label }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">URL Aplikasi</label>
                            <input type="url" name="base_url" class="form-control rounded-3" placeholder="https://dinas.probolinggokab.go.id">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold">
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
            alert('Berhasil disalin: ' + text);
        });
    }

    function toggleSecret(id) {
        var secElement = document.getElementById('sec_' + id);
        var rawSec = document.getElementById('raw_sec_' + id).value;
        var eyeIcon = document.getElementById('eye_' + id);

        if (secElement.innerText === '********************') {
            secElement.innerText = rawSec;
            secElement.classList.remove('text-muted');
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            secElement.innerText = '********************';
            secElement.classList.add('text-muted');
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    function copySecret(id) {
        var rawSec = document.getElementById('raw_sec_' + id).value;
        navigator.clipboard.writeText(rawSec).then(function() {
            alert('Client Secret berhasil disalin!');
        });
    }
</script>
@endsection
