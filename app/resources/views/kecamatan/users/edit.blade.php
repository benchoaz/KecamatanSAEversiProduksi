@extends('layouts.kecamatan')

@section('title', 'Ubah Pengguna')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('kecamatan.users.index') }}">Manajemen
                                    Pengguna</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Ubah Data User</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold">Perbarui Akun Pengguna</h4>
                    <p class="text-muted">Modifikasi hak akses atau profil user. Username tidak dapat diubah demi integritas
                        log audit.</p>
                </div>

                <form action="{{ route('kecamatan.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom border-light">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-id-card me-2 text-primary"></i> Identitas & Kredensial
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Username (Permanen)</label>
                                    <input type="text" class="form-control bg-light" value="{{ $user->username }}" readonly
                                        disabled title="Username bersifat permanen dan tidak dapat diubah">
                                    <div class="form-text x-small text-danger">Identitas username terkunci secara sistem.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" name="nama_lengkap"
                                        class="form-control @error('nama_lengkap') is-invalid @enderror"
                                        value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
                                    @error('nama_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Ganti Password <small
                                            class="text-muted">(Opsional)</small></label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror">
                                    <div class="form-text x-small">Kosongkan jika tidak ingin mengubah password akun ini.
                                    </div>
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom border-light">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-key me-2 text-warning"></i> Pengaturan Akses</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Role / Peran</label>
                                    <select name="role_id" id="role_select"
                                        class="form-select @error('role_id') is-invalid @enderror" required>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" data-role="{{ $role->nama_role }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                                {{ $role->nama_role }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6" id="desa_select_container" style="display: none;">
                                    <label class="form-label fw-bold">Desa Penempatan</label>
                                    <select name="desa_id" class="form-select @error('desa_id') is-invalid @enderror">
                                        <option value="">Pilih Desa...</option>
                                        @foreach($villages as $desa)
                                            <option value="{{ $desa->id }}" {{ old('desa_id', $user->desa_id) == $desa->id ? 'selected' : '' }}>
                                                {{ $desa->nama_desa }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('desa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Status Akun</label>
                                    <select name="status" class="form-select" required>
                                        <option value="aktif" {{ old('status', $user->status) == 'aktif' ? 'selected' : '' }}>
                                            Aktif (Memberi Akses)</option>
                                        <option value="nonaktif" {{ old('status', $user->status) == 'nonaktif' ? 'selected' : '' }}>Non-Aktif (Blokir Akses)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section Otorisasi Menu Terintegrasi --}}
                    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom border-light d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-shield-halved me-2 text-danger"></i> Otorisasi Menu (Akses Khusus)</h6>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-xs btn-outline-primary py-0" onclick="checkAllVisible()" style="font-size:11px;">Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0" onclick="uncheckAllVisible()" style="font-size:11px;">Hapus</button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="nav nav-tabs nav-fill bg-light border-0" id="permissionTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active py-3 border-0 rounded-0 fw-semibold" id="kecamatan-tab" data-bs-toggle="tab" data-bs-target="#kecamatan-pane" type="button" role="tab">
                                        <i class="fas fa-city me-1"></i> Dashboard Kecamatan
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link py-3 border-0 rounded-0 fw-semibold" id="desa-tab" data-bs-toggle="tab" data-bs-target="#desa-pane" type="button" role="tab">
                                        <i class="fas fa-house-chimney me-1"></i> Dashboard Desa
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content p-4" id="permissionTabsContent">
                                {{-- Dashboard Kecamatan Pane --}}
                                <div class="tab-pane fade show active" id="kecamatan-pane" role="tabpanel" tabindex="0">
                                    <div class="row g-3">
                                        @foreach($menus->where('target_dashboard', 'kecamatan') as $menu)
                                            <div class="col-12">
                                                <div class="menu-item p-3 border rounded-3 mb-2 bg-white">
                                                    <div class="form-check d-flex align-items-center gap-2">
                                                        <input class="form-check-input menu-parent" type="checkbox" name="permissions[]" 
                                                               id="p_m_{{ $menu->id }}" value="{{ $menu->permission_name }}"
                                                               {{ in_array($menu->permission_name, $userPermissions) ? 'checked' : '' }}
                                                               data-group="{{ $menu->id }}">
                                                        <label class="form-check-label fw-bold cursor-pointer" for="p_m_{{ $menu->id }}">
                                                            <i class="{{ $menu->icon }} text-muted me-2" style="width:20px;"></i> {{ $menu->name }}
                                                        </label>
                                                    </div>
                                                    @if($menu->subMenus->count() > 0)
                                                        <div class="submenu-list ms-4 mt-2 pt-2 border-top border-light row">
                                                            @foreach($menu->subMenus as $sub)
                                                                <div class="col-md-6 mb-1">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input menu-child child-group-{{ $menu->id }}" type="checkbox" name="permissions[]" 
                                                                               id="p_s_{{ $sub->id }}" value="{{ $sub->permission_name }}"
                                                                               {{ in_array($sub->permission_name, $userPermissions) ? 'checked' : '' }}
                                                                               data-parent="{{ $menu->id }}">
                                                                        <label class="form-check-label small cursor-pointer" for="p_s_{{ $sub->id }}">
                                                                            {{ $sub->name }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Dashboard Desa Pane --}}
                                <div class="tab-pane fade" id="desa-pane" role="tabpanel" tabindex="0">
                                    <div class="row g-3">
                                        @foreach($menus->where('target_dashboard', 'desa') as $menu)
                                            <div class="col-12">
                                                <div class="menu-item p-3 border rounded-3 mb-2 bg-white">
                                                    <div class="form-check d-flex align-items-center gap-2">
                                                        <input class="form-check-input menu-parent" type="checkbox" name="permissions[]" 
                                                               id="p_m_d_{{ $menu->id }}" value="{{ $menu->permission_name }}"
                                                               {{ in_array($menu->permission_name, $userPermissions) ? 'checked' : '' }}
                                                               data-group="{{ $menu->id }}">
                                                        <label class="form-check-label fw-bold cursor-pointer" for="p_m_d_{{ $menu->id }}">
                                                            <i class="{{ $menu->icon }} text-muted me-2" style="width:20px;"></i> {{ $menu->name }}
                                                        </label>
                                                    </div>
                                                    @if($menu->subMenus->count() > 0)
                                                        <div class="submenu-list ms-4 mt-2 pt-2 border-top border-light row">
                                                            @foreach($menu->subMenus as $sub)
                                                                <div class="col-md-6 mb-1">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input menu-child child-group-{{ $menu->id }}" type="checkbox" name="permissions[]" 
                                                                               id="p_s_d_{{ $sub->id }}" value="{{ $sub->permission_name }}"
                                                                               {{ in_array($sub->permission_name, $userPermissions) ? 'checked' : '' }}
                                                                               data-parent="{{ $menu->id }}">
                                                                        <label class="form-check-label small cursor-pointer" for="p_s_d_{{ $sub->id }}">
                                                                            {{ $sub->name }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mb-5">
                        <a href="{{ route('kecamatan.users.index') }}" class="btn btn-light px-4">Kembali</a>
                        <button type="submit" class="btn btn-primary px-5 d-flex align-items-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const roleSelect = document.getElementById('role_select');
                const desaContainer = document.getElementById('desa_select_container');
                const pTabs = document.querySelectorAll('#permissionTabs button');

                function toggleDesaSelect() {
                    const selectedOption = roleSelect.options[roleSelect.selectedIndex];
                    const roleName = selectedOption.getAttribute('data-role');

                    if (roleName === 'Operator Desa') {
                        desaContainer.style.display = 'block';
                        // Switch to Desa tab automatically
                        const desaTab = document.getElementById('desa-tab');
                        if (desaTab) bootstrap.Tab.getOrCreateInstance(desaTab).show();
                    } else {
                        desaContainer.style.display = 'none';
                        const kecTab = document.getElementById('kecamatan-tab');
                        if (kecTab) bootstrap.Tab.getOrCreateInstance(kecTab).show();
                    }
                }

                roleSelect.addEventListener('change', toggleDesaSelect);
                toggleDesaSelect(); // Initial check

                // Parent-Child sync logic
                document.querySelectorAll('.menu-parent').forEach(parent => {
                    parent.addEventListener('change', function() {
                        const groupId = this.dataset.group;
                        document.querySelectorAll('.child-group-' + groupId).forEach(child => {
                            child.checked = this.checked;
                        });
                    });
                });

                document.querySelectorAll('.menu-child').forEach(child => {
                    child.addEventListener('change', function() {
                        const parentId = this.dataset.parent;
                        const parent = document.querySelector('.menu-parent[data-group="' + parentId + '"]');
                        const cousins = document.querySelectorAll('.child-group-' + parentId);
                        const anyChecked = Array.from(cousins).some(c => c.checked);
                        if(anyChecked && !parent.checked) {
                            parent.checked = true;
                        }
                    });
                });
            });

            function checkAllVisible() {
                const activePane = document.querySelector('.tab-pane.active');
                activePane.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = true);
            }

            function uncheckAllVisible() {
                const activePane = document.querySelector('.tab-pane.active');
                activePane.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false);
            }
        </script>
    @endpush

    <style>
        .x-small { font-size: 0.75rem; }
        .bg-light { background-color: #f8fafc !important; }
        .cursor-pointer { cursor: pointer; }
        .menu-item:hover { border-color: rgba(var(--bs-primary-rgb), 0.5) !important; background: rgba(var(--bs-primary-rgb), 0.01) !important; }
        .nav-tabs .nav-link { color: #64748b; }
        .nav-tabs .nav-link.active { color: var(--bs-primary); border-bottom: 2px solid var(--bs-primary) !important; background: white; }
    </style>
@endsection