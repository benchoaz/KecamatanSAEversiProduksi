@extends('layouts.desa')

@section('title', 'Audit Log Aktivitas')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary fw-bold">
                        <i class="fas fa-history me-2"></i>Audit Log Aktivitas Desa
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>Waktu</th>
                                    <th>User</th>
                                    <th>Aksi</th>
                                    <th>Modul / Tabel</th>
                                    <th>Record ID</th>
                                    <th class="text-center">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td class="text-center text-muted">{{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $log->created_at->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-light-primary text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                {{ substr($log->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold small">{{ $log->user->name }}</div>
                                                <small class="text-muted">{{ $log->user->role }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($log->event) {
                                                'create' => 'bg-success',
                                                'update' => 'bg-warning',
                                                'delete' => 'bg-danger',
                                                'submit' => 'bg-info',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} text-uppercase" style="font-size: 0.7rem;">
                                            {{ $log->event }}
                                        </span>
                                    </td>
                                    <td>
                                        <code class="text-dark small">{{ $log->table_name }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border small">ID: {{ $log->record_id }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('desa.audit-logs.show', $log->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="fas fa-eye me-1"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                                        Belum ada data aktivitas yang tercatat.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-primary { background-color: #eef2ff !important; }
    .avatar { flex-shrink: 0; }
</style>
@endsection
