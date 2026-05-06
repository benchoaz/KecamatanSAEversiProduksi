@extends('layouts.kecamatan')

@section('title', 'Pengaduan WhatsApp')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="text-slate-900 fw-bold fs-3 mb-1">
                            <i class="fab fa-whatsapp text-success me-2"></i>
                            Pengaduan WhatsApp
                        </h1>
                        <p class="text-slate-400 small mb-0">
                            Daftar pengaduan yang masuk melalui bot WhatsApp terintegrasi
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        @if(auth()->user()->hasRole('Super Admin'))
                            <form action="{{ route('kecamatan.pelayanan.clear-all') }}" method="POST" onsubmit="return confirm('Hapus seluruh data PENGADUAN? Tindakan ini tidak dapat dibatalkan.')">
                                @csrf
                                <input type="hidden" name="category" value="pengaduan">
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                    <i class="fas fa-trash-alt me-1"></i> Bersihkan Semua
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('kecamatan.pelayanan.inbox', ['category' => 'pelayanan']) }}"
                            class="btn btn-outline-slate btn-sm rounded-pill px-3">
                            <i class="fas fa-inbox me-1"></i> Semua Inbox
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="icon-circle bg-slate-100 text-slate-600">
                                    <i class="fas fa-inbox"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="fw-bold text-slate-900 mb-0">{{ $stats['total'] }}</h3>
                                <p class="text-[10px] text-slate-400 mt-1 font-medium mb-0">Total Pengaduan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="icon-circle bg-amber-100 text-amber-600">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="fw-bold text-slate-900 mb-0">{{ $stats['menunggu'] }}</h3>
                                <p class="text-[10px] text-slate-400 mt-1 font-medium mb-0">Menunggu</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="icon-circle bg-blue-100 text-blue-600">
                                    <i class="fas fa-spinner"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="fw-bold text-slate-900 mb-0">{{ $stats['diproses'] }}</h3>
                                <p class="text-[10px] text-slate-400 mt-1 font-medium mb-0">Diproses</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="icon-circle bg-emerald-100 text-emerald-600">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="fw-bold text-slate-900 mb-0">{{ $stats['selesai'] }}</h3>
                                <p class="text-[10px] text-slate-400 mt-1 font-medium mb-0">Selesai</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-slate-700">
                        <i class="fas fa-list-ul me-2"></i> Daftar Pengaduan
                    </h6>
                </div>
            </div>
            <div class="card-body p-0">
                @if($pengaduans->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-slate-50">
                                <form id="bulk-delete-form" action="{{ route('kecamatan.pelayanan.bulk-destroy') }}" method="POST">
                                @csrf
                                <tr>
                                    <th class="border-0 px-4 py-3" style="width: 40px;">
                                        <input type="checkbox" class="form-check-input" id="check-all">
                                    </th>
                                    <th
                                        class="border-0 px-4 py-3 text-[10px] uppercase tracking-wider text-slate-500 font-bold">
                                        Waktu</th>
                                    <th
                                        class="border-0 px-4 py-3 text-[10px] uppercase tracking-wider text-slate-500 font-bold">
                                        Pengirim</th>
                                    <th
                                        class="border-0 px-4 py-3 text-[10px] uppercase tracking-wider text-slate-500 font-bold">
                                        Isi Pengaduan</th>
                                    <th
                                        class="border-0 px-4 py-3 text-[10px] uppercase tracking-wider text-slate-500 font-bold">
                                        Status</th>
                                    <th
                                        class="border-0 px-4 py-3 text-[10px] uppercase tracking-wider text-slate-500 font-bold text-end">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengaduans as $pengaduan)
                                    <tr class="border-bottom">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" name="ids[]" value="{{ $pengaduan->id }}" class="form-check-input check-item">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-[11px] text-slate-600 font-medium">
                                                {{ $pengaduan->created_at->format('d M Y') }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ $pengaduan->created_at->format('H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <div
                                                    class="avatar-xs bg-success text-white rounded-circle d-flex align-items-center justify-content-center">
                                                    <i class="fab fa-whatsapp text-xs"></i>
                                                </div>
                                                <div>
                                                    <div class="text-[11px] text-slate-700 font-medium">
                                                        {{ $pengaduan->nama ?? 'Anonim' }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400">
                                                        {{ $pengaduan->whatsapp ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-[11px] text-slate-600" style="max-width: 300px;">
                                                {{ Str::limit($pengaduan->uraian, 80) }}
                                            </div>
                                            @if($pengaduan->jenis_layanan)
                                                <span class="badge bg-slate-100 text-slate-600 text-[9px] mt-1">
                                                    {{ $pengaduan->jenis_layanan }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $statusConfig = [
                                                    'menunggu_verifikasi' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'fa-clock', 'label' => 'Menunggu'],
                                                    'diproses' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'fa-spinner', 'label' => 'Diproses'],
                                                    'selesai' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'fa-check', 'label' => 'Selesai'],
                                                    'ditolak' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'icon' => 'fa-times', 'label' => 'Ditolak'],
                                                ];
                                                $cfg = $statusConfig[$pengaduan->status] ?? $statusConfig['menunggu_verifikasi'];
                                            @endphp
                                            <span
                                                class="badge {{ $cfg['bg'] }} {{ $cfg['text'] }} text-[10px] px-2 py-1 rounded-pill">
                                                <i class="fas {{ $cfg['icon'] }} me-1"></i>
                                                {{ $cfg['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('kecamatan.pelayanan.pengaduan.show', $pengaduan->id) }}"
                                                    class="btn btn-sm btn-white border border-slate-200 rounded-3 px-2 fw-bold text-slate-600">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger border-slate-200 rounded-3 px-2" 
                                                    onclick="deleteItem({{ $pengaduan->id }})">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="icon-circle bg-slate-100 text-slate-400 mx-auto mb-3" style="width: 64px; height: 64px;">
                            <i class="fas fa-inbox-in text-2xl"></i>
                        </div>
                        <h6 class="text-slate-500 mb-1">Belum Ada Pengaduan</h6>
                        <p class="text-slate-400 text-[11px] mb-0">
                            Pengaduan yang masuk melalui WhatsApp akan ditampilkan di sini.
                        </p>
                    </div>
                @endif
            </div>
            @if($pengaduans->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $pengaduans->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Floating Bulk Actions -->
    <div id="bulk-actions" class="position-fixed bottom-0 start-50 translate-middle-x mb-4 d-none" style="z-index: 1050;">
        <div class="bg-slate-900 text-white px-4 py-3 rounded-pill shadow-lg d-flex align-items-center gap-3">
            <span class="small fw-bold"><span id="selected-count">0</span> Terpilih</span>
            <div class="vr bg-slate-700"></div>
            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold" onclick="submitBulkDelete()">
                <i class="fas fa-trash-alt me-2"></i> Hapus
            </button>
        </div>
    </div>

    <form id="delete-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('check-all');
            const checkItems = document.querySelectorAll('.check-item');
            const bulkActions = document.getElementById('bulk-actions');
            const selectedCount = document.getElementById('selected-count');

            function updateBulkVisibility() {
                const checked = document.querySelectorAll('.check-item:checked');
                if (checked.length > 0) {
                    bulkActions.classList.remove('d-none');
                    selectedCount.innerText = checked.length;
                } else {
                    bulkActions.classList.add('d-none');
                }
            }

            if(checkAll) {
                checkAll.addEventListener('change', function() {
                    checkItems.forEach(item => item.checked = this.checked);
                    updateBulkVisibility();
                });
            }

            checkItems.forEach(item => {
                item.addEventListener('change', updateBulkVisibility);
            });
        });

        function deleteItem(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                const form = document.getElementById('delete-form');
                form.action = `/kecamatan/pelayanan/${id}`;
                form.submit();
            }
        }

        function submitBulkDelete() {
            if (confirm('Hapus seluruh data terpilih?')) {
                document.getElementById('bulk-delete-form').submit();
            }
        }
    </script>

    <style>
        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-xs {
            width: 28px;
            height: 28px;
            font-size: 10px;
        }
    </style>
@endsection