@extends('layouts.public')

@section('title', 'Akun Ditemukan - Reset PIN')

@section('content')
    <style>
        .owner-auth-wrap {
            min-height: calc(100vh - 160px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, #f0fdf4 0%, #eff6ff 50%, #f0fdf4 100%);
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .09);
            overflow: hidden;
        }

        .auth-header {
            background: linear-gradient(135deg, #16a34a 0%, #059669 100%);
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

        .success-icon {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, .2);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            border: 2px solid rgba(255, 255, 255, .4);
        }

        .auth-body {
            padding: 1.5rem 2rem 2rem;
        }

        .wa-badge {
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            border-radius: 14px;
            padding: .9rem 1.1rem;
            display: flex;
            align-items: center;
            gap: .8rem;
            margin-bottom: 1.25rem;
        }

        .wa-badge-icon {
            width: 42px;
            height: 42px;
            background: #22c55e;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .steps-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 14px;
            padding: 1rem 1.1rem;
            margin-bottom: 1.25rem;
        }

        .steps-box ol {
            margin: .5rem 0 0;
            padding-left: 1.1rem;
            font-size: .82rem;
            color: #1e40af;
            line-height: 1.7;
        }

        .btn-wa {
            width: 100%;
            padding: .85rem;
            background: linear-gradient(135deg, #16a34a, #059669);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: .95rem;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .6rem;
            margin-bottom: .75rem;
            transition: opacity .2s, transform .1s;
        }

        .btn-wa:hover {
            opacity: .9;
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-ghost {
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
            transition: border-color .2s;
        }

        .btn-ghost:hover {
            border-color: #86efac;
            color: #16a34a;
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
                <div class="success-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </div>
                <h1 style="color:#fff;font-size:1.2rem;font-weight:800;margin:0 0 .3rem;">Akun Ditemukan!</h1>
                <p style="color:rgba(255,255,255,.8);font-size:.82rem;margin:0;">Nomor WhatsApp Anda teridentifikasi</p>
            </div>

            <div class="auth-body">
                {{-- WA number display --}}
                <div class="wa-badge">
                    <div class="wa-badge-icon">
                        <i class="fab fa-whatsapp" style="color:#fff;font-size:1.2rem;"></i>
                    </div>
                    <div>
                        <p
                            style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin:0 0 .15rem;">
                            Nomor WA Terdaftar</p>
                        <p style="font-size:1.05rem;font-weight:800;color:#15803d;margin:0;letter-spacing:.04em;">
                            {{ $phone }}</p>
                    </div>
                </div>

                {{-- Steps --}}
                <div class="steps-box">
                    <p
                        style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#1e40af;margin:0;">
                        <i class="fas fa-list-ol me-1"></i> Langkah Selanjutnya
                    </p>
                    <ol>
                        <li>Hubungi petugas kecamatan via WhatsApp</li>
                        <li>Informasikan nomor Anda: <strong>{{ $phone }}</strong></li>
                        <li>Petugas akan verifikasi & berikan PIN baru</li>
                        <li>Login kembali dengan PIN baru</li>
                    </ol>
                </div>

                {{-- Actions --}}
                <a href="https://wa.me/{{ $adminWa }}?text={{ urlencode('Halo, saya ingin reset PIN untuk nomor WA terdaftar: ' . ($phoneRaw ?? $phone)) }}"
                    class="btn-wa" target="_blank">
                    <i class="fab fa-whatsapp" style="font-size:1.1rem;"></i>
                    Hubungi Petugas via WhatsApp
                </a>
                <a href="{{ route('owner.login') }}" class="btn-ghost">
                    <i class="fas fa-arrow-left"></i> Kembali ke Login
                </a>
            </div>
        </div>
    </div>
@endsection