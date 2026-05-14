@extends('layouts.hub')

@section('content')
<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h3 mb-1" style="font-weight: 700; letter-spacing: -0.02em;">Overview</h1>
            <p class="text-muted mb-0">Statistik global Kabupaten Probolinggo per hari ini.</p>
        </div>
        <div class="text-muted small">
            <i class="fas fa-clock me-1"></i> {{ now()->translatedFormat('l, d F Y — H:i') }} WIB
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card border-0 h-100" style="border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                <div class="card-body p-4">
                    <div class="text-muted small fw-bold text-uppercase mb-3" style="letter-spacing: 0.05em;">Kecamatan Aktif</div>
                    <div class="d-flex align-items-end justify-content-between">
                        <div class="h2 mb-0 fw-bold" style="letter-spacing: -0.03em; color: #111827;">{{ $active_districts }}</div>
                        <div class="text-muted small">dari {{ $total_districts }}</div>
                    </div>
                    <div class="progress mt-3" style="height: 4px; border-radius: 4px;">
                        <div class="progress-bar bg-primary" style="width: {{ $total_districts > 0 ? ($active_districts / $total_districts) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 h-100" style="border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                <div class="card-body p-4">
                    <div class="text-muted small fw-bold text-uppercase mb-3" style="letter-spacing: 0.05em;">Total Layanan</div>
                    <div class="h2 mb-0 fw-bold" style="letter-spacing: -0.03em; color: #111827;">{{ number_format($total_services) }}</div>
                    <div class="text-muted small mt-2">Semua pengajuan warga</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 h-100" style="border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                <div class="card-body p-4">
                    <div class="text-muted small fw-bold text-uppercase mb-3" style="letter-spacing: 0.05em;">Menunggu Proses</div>
                    <div class="h2 mb-0 fw-bold" style="letter-spacing: -0.03em; color: #f59e0b;">{{ number_format($pending_services) }}</div>
                    <div class="text-muted small mt-2">Perlu tindak lanjut</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 h-100" style="border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                <div class="card-body p-4">
                    <div class="text-muted small fw-bold text-uppercase mb-3" style="letter-spacing: 0.05em;">Selesai Dilayani</div>
                    <div class="h2 mb-0 fw-bold" style="letter-spacing: -0.03em; color: #10b981;">{{ number_format($done_services) }}</div>
                    <div class="text-muted small mt-2">Layanan tuntas</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Activity -->
        <div class="col-lg-7">
            <div class="card border-0" style="border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4" style="letter-spacing: -0.01em;">Aktivitas Terbaru</h6>
                    @forelse($recent_activities as $activity)
                    <div class="d-flex align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="flex-shrink-0 me-3">
                            @php
                                $color = match($activity->status) {
                                    'selesai', 'done' => 'success',
                                    'pending', 'diajukan' => 'warning',
                                    'ditolak' => 'danger',
                                    default => 'secondary'
                                };
                            @endphp
                            <div class="rounded-circle bg-{{ $color }}-subtle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fas fa-file-alt text-{{ $color }} small"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-bold text-truncate" style="font-size: 14px;">{{ $activity->nama_pemohon ?? 'Warga' }}</div>
                            <div class="text-muted" style="font-size: 12px;">{{ $activity->jenis_layanan ?? 'Layanan Umum' }}</div>
                        </div>
                        <div class="flex-shrink-0 text-end ms-3">
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} rounded-pill border-0" style="font-size: 11px; font-weight: 600;">
                                {{ ucfirst($activity->status) }}
                            </span>
                            <div class="text-muted mt-1" style="font-size: 11px;">{{ $activity->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x mb-3 opacity-25"></i>
                        <p class="small mb-0">Belum ada aktivitas layanan.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- District Status (Live Stats) -->
        <div class="col-lg-5">
            <div class="card border-0" style="border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0" style="letter-spacing: -0.01em;">Status Jaringan</h6>
                        <a href="{{ route('hub.districts.index') }}" class="text-primary small fw-bold" style="text-decoration: none;">Kelola →</a>
                    </div>
                    @forelse($districts as $district)
                    @php $stat = $district_stats[$district->slug] ?? null; @endphp
                    <div class="d-flex align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="flex-shrink-0 me-3">
                            @if($stat && $stat['is_reachable'])
                                <div class="rounded-circle bg-success" style="width: 10px; height: 10px;"></div>
                            @elseif($district->is_active)
                                <div class="rounded-circle bg-warning" style="width: 10px; height: 10px;"></div>
                            @else
                                <div class="rounded-circle bg-secondary" style="width: 10px; height: 10px;"></div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold" style="font-size: 14px;">{{ $district->name }}</div>
                            @if($stat && $stat['is_reachable'])
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ number_format($stat['total_services']) }} layanan &middot;
                                    <span class="text-warning fw-bold">{{ $stat['pending'] }} pending</span>
                                </div>
                            @else
                                <div class="text-muted" style="font-size: 11px;">{{ $district->db_name }}</div>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            @if($stat && $stat['is_reachable'])
                                <span class="badge rounded-pill border-0" style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 700;">LIVE</span>
                            @elseif($district->is_active)
                                <span class="badge rounded-pill border-0" style="background: #fffbeb; color: #92400e; font-size: 11px; font-weight: 700;">LOKAL</span>
                            @else
                                <span class="badge rounded-pill border-0" style="background: #f3f4f6; color: #6b7280; font-size: 11px; font-weight: 700;">OFFLINE</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <p class="small mb-0">Belum ada kecamatan terdaftar.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Statistik Per Kecamatan (Live Data) --}}
    @if(count($district_stats) > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0" style="border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="fw-bold mb-1" style="letter-spacing: -0.01em;">Statistik Per Kecamatan</h6>
                            <p class="text-muted small mb-0">Data real-time dari database masing-masing kecamatan yang terhubung.</p>
                        </div>
                        <span class="badge rounded-pill px-3 py-2" style="background: #eff6ff; color: #1d4ed8; font-size: 12px;">
                            {{ collect($district_stats)->where('is_reachable', true)->count() }} / {{ count($district_stats) }} Terhubung
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                            <thead>
                                <tr style="border-bottom: 2px solid #f3f4f6;">
                                    <th class="text-muted text-uppercase fw-bold pb-3" style="font-size: 11px; letter-spacing: 0.05em; border: none;">Kecamatan</th>
                                    <th class="text-muted text-uppercase fw-bold pb-3 text-center" style="font-size: 11px; letter-spacing: 0.05em; border: none;">Total Layanan</th>
                                    <th class="text-muted text-uppercase fw-bold pb-3 text-center" style="font-size: 11px; letter-spacing: 0.05em; border: none;">Pending</th>
                                    <th class="text-muted text-uppercase fw-bold pb-3 text-center" style="font-size: 11px; letter-spacing: 0.05em; border: none;">Selesai</th>
                                    <th class="text-muted text-uppercase fw-bold pb-3 text-center" style="font-size: 11px; letter-spacing: 0.05em; border: none;">Warga Terdaftar</th>
                                    <th class="text-muted text-uppercase fw-bold pb-3 text-center" style="font-size: 11px; letter-spacing: 0.05em; border: none;">Koneksi DB</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($district_stats as $slug => $stat)
                                <tr>
                                    <td class="py-3" style="border: none; border-bottom: 1px solid #f9fafb;">
                                        <div class="fw-bold">{{ $stat['name'] }}</div>
                                        <div class="text-muted" style="font-size: 11px;">{{ $stat['domain'] ?? $slug . '.kecamatansae.id' }}</div>
                                    </td>
                                    <td class="text-center py-3" style="border: none; border-bottom: 1px solid #f9fafb;">
                                        @if($stat['is_reachable'])
                                            <span class="fw-bold">{{ number_format($stat['total_services']) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-3" style="border: none; border-bottom: 1px solid #f9fafb;">
                                        @if($stat['is_reachable'])
                                            <span class="fw-bold" style="color: {{ ($stat['pending'] ?? 0) > 0 ? '#f59e0b' : '#10b981' }};">
                                                {{ $stat['pending'] ?? 0 }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-3" style="border: none; border-bottom: 1px solid #f9fafb;">
                                        @if($stat['is_reachable'])
                                            <span class="fw-bold" style="color: #10b981;">{{ $stat['done'] ?? 0 }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-3" style="border: none; border-bottom: 1px solid #f9fafb;">
                                        @if($stat['is_reachable'])
                                            <span class="fw-bold">{{ number_format($stat['total_warga'] ?? 0) }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-3" style="border: none; border-bottom: 1px solid #f9fafb;">
                                        @if($stat['is_reachable'])
                                            <span class="badge rounded-pill border-0 px-3" style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 700;">
                                                <i class="fas fa-circle me-1" style="font-size: 6px;"></i>LIVE
                                            </span>
                                        @else
                                            <span class="badge rounded-pill border-0 px-3" style="background: #fef2f2; color: #991b1b; font-size: 11px; font-weight: 700;">
                                                <i class="fas fa-circle me-1" style="font-size: 6px;"></i>OFFLINE
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
