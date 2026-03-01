@extends('layouts.kecamatan')

@section('title', 'Ubah Password')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="content-header mb-4">
            <div class="header-title">
                <h1 class="text-slate-900 fw-bold display-6">Ubah Password</h1>
                <p class="text-slate-500 fs-5 mb-0">Kelola keamanan akun Anda dengan mengubah password.</p>
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

        <div class="row">
            <div class="col-xl-8">
                <div class="card border-0 shadow-premium rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-4 px-4 border-bottom border-light">
                        <h5 class="mb-0 fw-bold text-slate-800">
                            <i class="fas fa-lock text-info me-2"></i>
                            Form Ubah Password
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('profile.password.update') }}" method="POST" class="needs-validation"
                            novalidate>
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label for="current_password" class="form-label fw-bold text-slate-700">
                                    Password Lama <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-secondary"></i>
                                    </span>
                                    <input type="password"
                                        class="form-control border-start-0 @error('current_password') is-invalid @enderror"
                                        id="current_password" name="current_password" placeholder="Masukkan password lama"
                                        required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold text-slate-700">
                                    Password Baru <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-key text-secondary"></i>
                                    </span>
                                    <input type="password"
                                        class="form-control border-start-0 @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Masukkan password baru (min. 8 karakter)"
                                        required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Password minimal 8 karakter</small>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-bold text-slate-700">
                                    Konfirmasi Password Baru <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-key text-secondary"></i>
                                    </span>
                                    <input type="password"
                                        class="form-control border-start-0 @error('password_confirmation') is-invalid @enderror"
                                        id="password_confirmation" name="password_confirmation"
                                        placeholder="Masukkan kembali password baru" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Kembali
                                </a>
                                <button type="submit" class="btn btn-brand-600 rounded-pill px-4">
                                    <i class="fas fa-save me-2"></i>
                                    Simpan Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mt-4 bg-amber-50">
                    <div class="card-body p-4">
                        <div class="d-flex gap-3">
                            <div class="icon-box icon-box-warning sm">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h6 class="mb-2 fw-bold text-amber-900">Perhatian!</h6>
                                <p class="mb-0 text-amber-800 small">
                                    Setelah mengubah password, Anda akan perlu menggunakan password baru untuk login.
                                    Pastikan password baru yang Anda masukkan berbeda dari password lama dan mudah diingat.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Toggle password visibility
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Form validation
            document.querySelectorAll('.needs-validation').forEach(form => {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        </script>
    @endpush
@endsection