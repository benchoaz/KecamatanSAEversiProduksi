@extends('layouts.public')

@section('title', 'Lupa PIN - Portal Pemilik Usaha')

@section('content')
    <style>
        .owner-auth-wrap {
            min-height: calc(100vh - 160px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, #fff7ed 0%, #eff6ff 50%, #fdf4ff 100%);
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .09);
            overflow: hidden;
        }

        .auth-header {
            background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
            padding: 2rem 2rem 3rem;
            text-align: center;
            position: relative;
        }

        .auth-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 32px;
            background: #fff;
            border-radius: 32px 32px 0 0;
        }

        .auth-icon-wrap {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, .2);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, .3);
        }

        .auth-body {
            padding: 1.5rem 2rem 2rem;
        }

        .auth-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #64748b;
            margin-bottom: .4rem;
            display: block;
        }

        .auth-input {
            width: 100%;
            padding: .75rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: .95rem;
            background: #f8fafc;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            box-sizing: border-box;
        }

        .auth-input:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, .1);
            background: #fff;
        }

        .auth-input.is-invalid {
            border-color: #ef4444;
        }

        .btn-auth-primary {
            width: 100%;
            padding: .85rem;
            background: linear-gradient(135deg, #ea580c, #dc2626);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: .95rem;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-bottom: .75rem;
        }

        .btn-auth-primary:hover {
            opacity: .92;
            transform: translateY(-1px);
        }

        .btn-auth-ghost {
            width: 100%;
            padding: .75rem;
            background: transparent;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-weight: 600;
            font-size: .9rem;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            transition: border-color .2s, color .2s;
        }

        .btn-auth-ghost:hover {
            border-color: #fdba74;
            color: #ea580c;
        }

        .auth-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: .75rem 1rem;
            color: #b91c1c;
            font-size: .82rem;
            margin-bottom: 1.25rem;
        }

        .info-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 14px;
            padding: 1rem 1.1rem;
            margin-bottom: 1.25rem;
        }

        @media (max-width: 480px) {
            .auth-body {
                padding: 1.25rem 1.25rem 1.75rem;
            }

            .auth-header {
                padding: 1.5rem 1.25rem 2.5rem;
            }
        }
    </style>

    <div class="owner-auth-wrap">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon-wrap">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4l3 3" />
                    </svg>
                </div>
                <h1 style="color:#fff;font-size:1.2rem;font-weight:800;margin:0 0 .3rem;">Reset PIN</h1>
                <p style="color:rgba(255,255,255,.8);font-size:.82rem;margin:0;">Masukkan nomor WA yang terdaftar</p>
            </div>

            <div class="auth-body">
                @if ($errors->any())
                    <div class="auth-error">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        @foreach ($errors->all() as $error) {{ $error }} @endforeach
                    </div>
                @endif

                <div class="info-box">
                    <p style="font-size:.82rem;color:#92400e;margin:0;line-height:1.55;">
                        <i class="fas fa-info-circle" style="color:#ea580c;margin-right:.4rem;"></i>
                        Masukkan nomor WhatsApp yang terdaftar. Kami akan tampilkan akun yang sesuai, lalu Anda dapat
                        menghubungi admin untuk reset PIN.
                    </p>
                </div>

                <form method="POST" action="{{ route('owner.request_pin_reset') }}">
                    @csrf

                    <div style="margin-bottom:1.25rem;">
                        <label class="auth-label" for="phone">
                            <i class="fab fa-whatsapp" style="color:#22c55e;margin-right:.3rem;"></i>Nomor WhatsApp
                            Terdaftar
                        </label>
                        <input type="tel" class="auth-input @error('phone') is-invalid @enderror" id="phone" name="phone"
                            value="{{ old('phone') }}" placeholder="082345678901" inputmode="numeric" required>
                        <p style="font-size:.72rem;color:#94a3b8;margin:.4rem 0 0 .2rem;">Format: 082xxx / 081xxx (diawali
                            0)</p>
                    </div>

                    <button type="submit" class="btn-auth-primary">
                        <i class="fas fa-search"></i> Cari Akun
                    </button>
                    <a href="{{ route('owner.login') }}" class="btn-auth-ghost">
                        <i class="fas fa-arrow-left"></i> Kembali ke Login
                    </a>
                </form>
            </div>
        </div>
    </div>
@endsection