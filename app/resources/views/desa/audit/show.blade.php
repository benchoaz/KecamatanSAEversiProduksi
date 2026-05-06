@extends('layouts.desa')

@section('title', 'Detail Audit Log')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary fw-bold">
                        <i class="fas fa-search me-2"></i>Detail Aktivitas #{{ $log->id }}
                    </h5>
                    <a href="{{ route('desa.audit-logs.index') }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Informasi Dasar</label>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td style="width: 120px;">Waktu</td>
                                    <td class="fw-bold">: {{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td>User</td>
                                    <td class="fw-bold">: {{ $log->user->name }}</td>
                                </tr>
                                <tr>
                                    <td>Aksi</td>
                                    <td>: 
                                        <span class="badge bg-primary text-uppercase">{{ $log->event }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Target Data</label>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td style="width: 120px;">Modul / Tabel</td>
                                    <td class="fw-bold">: <code class="text-dark">{{ $log->table_name }}</code></td>
                                </tr>
                                <tr>
                                    <td>ID Record</td>
                                    <td class="fw-bold">: {{ $log->record_id }}</td>
                                </tr>
                                <tr>
                                    <td>IP Address</td>
                                    <td class="fw-bold">: {{ $log->ip_address ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($log->old_values || $log->new_values)
                    <div class="row">
                        <div class="col-12">
                            <label class="text-muted small text-uppercase fw-bold mb-2 d-block">Perubahan Data</label>
                            
                            <div class="accordion" id="accordionAudit">
                                @if($log->old_values)
                                <div class="accordion-item border-0 mb-3 shadow-sm rounded overflow-hidden">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button bg-light-danger text-danger fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOld">
                                            <i class="fas fa-history me-2"></i> Data Lama
                                        </button>
                                    </h2>
                                    <div id="collapseOld" class="accordion-collapse collapse show">
                                        <div class="accordion-body bg-light p-0">
                                            <pre class="m-0 p-3" style="font-size: 0.85rem; max-height: 400px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if($log->new_values)
                                <div class="accordion-item border-0 shadow-sm rounded overflow-hidden">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button bg-light-success text-success fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNew">
                                            <i class="fas fa-check-circle me-2"></i> Data Baru / Saat Ini
                                        </button>
                                    </h2>
                                    <div id="collapseNew" class="accordion-collapse collapse show">
                                        <div class="accordion-body bg-light p-0">
                                            <pre class="m-0 p-3" style="font-size: 0.85rem; max-height: 400px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info border-0 shadow-sm">
                        <i class="fas fa-info-circle me-2"></i> Tidak ada detail data yang terekam untuk aksi ini.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-danger { background-color: #fef2f2 !important; }
    .bg-light-success { background-color: #f0fdf4 !important; }
    pre { white-space: pre-wrap; word-wrap: break-word; }
    .accordion-button:not(.collapsed) {
        box-shadow: none;
    }
</style>
@endsection
