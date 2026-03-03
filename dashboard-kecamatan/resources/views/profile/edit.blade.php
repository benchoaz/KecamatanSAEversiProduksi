@extends('layouts.kecamatan')

@section('title', 'Edit Profil')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="content-header mb-4">
            <div class="header-title">
                <h1 class="text-slate-900 fw-bold display-6">Edit Profil</h1>
                <p class="text-slate-500 fs-5 mb-0">Kelola informasi profil akun Anda.</p>
                <div class="header-accent"></div>
            </div>
        </div>

        @if(session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    timer: 4000,
                    showConfirmButton: false,
                    borderRadius: '1rem'
                });
            </script>
            <div class="alert alert-emerald border-0 shadow-sm rounded-4 p-4 mb-4 animate__animated animate__fadeIn">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box icon-box-emerald sm">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-emerald-900">Berhasil!</h6>
                        <p class="mb-0 text-emerald-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($errors) && is_object($errors) && $errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 mb-4 animate__animated animate__shakeX">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-box icon-box-danger sm">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-danger">Terjadi Kesalahan!</h6>
                        <ul class="mb-0 text-danger small ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Profile Edit Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="fas fa-user-circle me-2 text-primary"></i>
                            Informasi Profil
                        </h5>

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label for="nama_lengkap" class="form-label fw-semibold">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                    class="form-control form-control-lg @error('nama_lengkap') is-invalid @enderror"
                                    id="nama_lengkap" name="nama_lengkap"
                                    value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
                                @error('nama_lengkap')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="username" class="form-label fw-semibold">
                                    Username
                                </label>
                                <input type="text" class="form-control form-control-lg" id="username"
                                    value="{{ $user->username }}" disabled>
                                <small class="text-muted">Username tidak dapat diubah.</small>
                            </div>

                            <div class="mb-4">
                                <label for="no_hp" class="form-label fw-semibold">
                                    Nomor HP
                                </label>
                                <input type="text" class="form-control form-control-lg @error('no_hp') is-invalid @enderror"
                                    id="no_hp" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                                    placeholder="Contoh: 081234567890">
                                @error('no_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="foto" class="form-label fw-semibold">
                                    Foto Profil
                                </label>
                                <input type="file" class="form-control @error('foto') is-invalid @enderror" id="foto"
                                    name="foto" accept="image/*">
                                @error('foto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Profile Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 text-center">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="fas fa-id-card me-2 text-primary"></i>
                            Ringkasan Profil
                        </h5>

                        <div class="mb-3">
                            @if($user->foto && Storage::disk('public')->exists($user->foto))
                                <img src="{{ Storage::url($user->foto) }}" alt="Foto Profil" class="rounded-circle mb-3"
                                    style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                    style="width: 120px; height: 120px;">
                                    <i class="fas fa-user text-primary" style="font-size: 48px;"></i>
                                </div>
                            @endif
                        </div>

                        <h4 class="fw-bold">{{ $user->nama_lengkap }}</h4>
                        <p class="text-muted mb-1">@ {{ $user->username }}</p>

                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                            {{ $user->role->nama_role ?? 'Role belum diatur' }}
                        </span>

                        @if($user->desa)
                            <hr>
                            <p class="text-muted mb-1 small">
                                <i class="fas fa-building me-1"></i>
                                {{ $user->desa->nama_desa }}
                            </p>
                        @endif

                        <hr>

                        <a href="{{ route('profile.password.edit') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-key me-1"></i> Ubah Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection