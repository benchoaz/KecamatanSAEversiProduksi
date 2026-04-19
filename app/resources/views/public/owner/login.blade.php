@extends('layouts.public')

@section('title', 'Login Pemilik Usaha')

@section('content')
    <style>
        .owner-auth-wrap {
            min-height: calc(100vh - 160px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: linear-gradient(135deg, #f0fdf4 0%, #eff6ff 50%, #fdf4ff 100%);
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
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
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
        }

        .auth-input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .1);
            background: #fff;
        }

        .auth-input.is-invalid {
            border-color: #ef4444;
        }

        .pin-row {
            display: flex;
            gap: .5rem;
        }

        .pin-box {
            flex: 1;
            text-align: center;
            padding: .85rem .4rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1.4rem;
            font-weight: 700;
            background: #f8fafc;
            transition: border-color .2s;
            outline: none;
        }

        .pin-box:focus {
            border-color: #7c3aed;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .1);
        }

        .btn-auth-primary {
            width: 100%;
            padding: .85rem;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
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
            border-color: #c4b5fd;
            color: #7c3aed;
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

        .auth-divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: #94a3b8;
            font-size: .8rem;
            font-weight: 600;
            margin: 1.25rem 0;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .register-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            color: #15803d;
            font-size: .82rem;
            font-weight: 600;
            border-radius: 12px;
            padding: .65rem 1rem;
            text-decoration: none;
            transition: background .2s;
        }

        .register-pill:hover {
            background: #dcfce7;
            color: #15803d;
        }

        #pinInput {
            display: none;
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
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
                <h1 style="color:#fff;font-size:1.25rem;font-weight:800;margin:0 0 .3rem;">Login Pemilik Usaha</h1>
                <p style="color:rgba(255,255,255,.75);font-size:.82rem;margin:0;">Kelola UMKM, Loker & Jasa Anda</p>
            </div>

            <div class="auth-body">
                @if ($errors->any())
                    <div class="auth-error">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        @foreach ($errors->all() as $error) {{ $error }}@if(!$loop->last), @endif @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('owner.authenticate') }}">
                    @csrf

                    <div style="margin-bottom:1.1rem;">
                        <label class="auth-label" for="phone">
                            <i class="fab fa-whatsapp" style="color:#22c55e;margin-right:.3rem;"></i>Nomor WhatsApp
                        </label>
                        <input type="tel" class="auth-input @error('phone') is-invalid @enderror" id="phone" name="phone"
                            value="{{ old('phone') }}" placeholder="08123456789" inputmode="numeric" required>
                        <p style="font-size:.72rem;color:#94a3b8;margin:.4rem 0 0 .2rem;">Nomor yang didaftarkan saat
                            registrasi</p>
                    </div>

                    <div style="margin-bottom:1.5rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem;">
                            <label class="auth-label" style="margin:0;" for="pinBox0">
                                <i class="fas fa-key" style="color:#7c3aed;margin-right:.3rem;"></i>PIN 6 Digit
                            </label>
                            <a href="{{ route('owner.forgot_pin') }}"
                                style="font-size:.72rem;color:#7c3aed;text-decoration:none;font-weight:600;">Lupa PIN?</a>
                        </div>
                        <div class="pin-row" id="pinBoxRow">
                            @for($i = 0; $i < 6; $i++)
                                <input class="pin-box" id="pinBox{{$i}}" maxlength="1" inputmode="numeric" pattern="[0-9]"
                                    autocomplete="off">
                            @endfor
                        </div>
                        <input type="hidden" name="pin" id="pinInput">
                    </div>

                    <button type="submit" class="btn-auth-primary" id="loginBtn">
                        <i class="fas fa-sign-in-alt"></i> Masuk ke Dashboard
                    </button>
                </form>

                <div class="auth-divider">atau</div>

                <a href="{{ route('economy.create') }}" class="register-pill">
                    <i class="fas fa-store"></i> Daftar UMKM / Loker / Jasa Baru
                </a>
            </div>
        </div>
    </div>

    <script>
        // PIN boxes UX
        const boxes = document.querySelectorAll('.pin-box');
        const pinInput = document.getElementById('pinInput');

        boxes.forEach((box, idx) => {
            box.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !box.value && idx > 0) {
                    boxes[idx - 1].focus();
                    boxes[idx - 1].value = '';
                }
            });
            box.addEventListener('input', e => {
                box.value = box.value.replace(/[^0-9]/g, '');
                if (box.value && idx < boxes.length - 1) {
                    boxes[idx + 1].focus();
                }
                syncPin();
            });
            box.addEventListener('paste', e => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                [...pasted].forEach((ch, i) => { if (boxes[i]) boxes[i].value = ch; });
                if (boxes[Math.min(pasted.length, 5)]) boxes[Math.min(pasted.length, 5)].focus();
                syncPin();
            });
        });

        function syncPin() {
            pinInput.value = [...boxes].map(b => b.value).join('');
        }

        // Prevent submit if PIN not complete
        document.querySelector('form').addEventListener('submit', e => {
            syncPin();
            if (pinInput.value.length !== 6) {
                e.preventDefault();
                boxes[pinInput.value.length]?.focus();
            }
        });
    </script>
@endsection