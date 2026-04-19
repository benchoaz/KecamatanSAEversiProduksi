@extends('layouts.public')

@section('title', 'Ubah PIN')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold">Ubah PIN</h4>
                            <p class="text-muted small">Masukkan PIN baru untuk mengamankan akun Anda</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('owner.reset_pin') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="new_pin" class="form-label">PIN Baru (6 digit)</label>
                                <input type="password" class="form-control @error('new_pin') is-invalid @enderror"
                                    id="new_pin" name="new_pin" maxlength="6" placeholder="Masukkan 6 digit PIN baru"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="new_pin_confirmation" class="form-label">Konfirmasi PIN</label>
                                <input type="password" class="form-control" id="new_pin_confirmation"
                                    name="new_pin_confirmation" maxlength="6" placeholder="Masukkan ulang 6 digit PIN"
                                    required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    Simpan PIN Baru
                                </button>
                                <a href="{{ route('owner.dashboard') }}" class="btn btn-outline-secondary">
                                    Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection