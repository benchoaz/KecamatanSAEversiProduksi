@extends('layouts.kecamatan')

@section('title', 'Pengaturan Layanan Publik')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="content-header mb-4">
            <div class="header-title">
                <h1 class="text-slate-900 fw-bold display-6">Pusat Kendali Layanan</h1>
                <p class="text-slate-500 fs-5 mb-0">Atur validasi berkas otomatis dan prosedur standar pelayanan.</p>
                <div class="header-accent"></div>
            </div>
        </div>

        @if(session('success'))
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false,
                    borderRadius: '1rem'
                });
            </script>
        @endif

        <form action="{{ route('kecamatan.settings.pelayanan.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                {{-- Left Side: AI Validation --}}
                <div class="col-xl-6">
                    <div class="card border-0 shadow-premium rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-white py-4 px-4 border-bottom border-light">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-3">
                                        <i class="fas fa-microchip"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold">Validasi Dokumen (AI)</h5>
                                        <p class="text-[11px] text-slate-400 mb-0">Deteksi otomatis kesesuaian berkas warga.</p>
                                    </div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_document_ai_active" 
                                        id="is_document_ai_active" {{ $profile->is_document_ai_active ? 'checked' : '' }}
                                        style="width: 3em; height: 1.5em; cursor: pointer;">
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-indigo border-0 shadow-sm rounded-4 mb-4">
                                <div class="d-flex gap-3">
                                    <i class="fas fa-info-circle mt-1"></i>
                                    <small>AI akan mengecek apakah file yang diunggah warga (KTP, KK, dll) sudah sesuai dengan kategorinya sebelum diproses operator.</small>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-slate-700 fw-bold">Pilih Provider AI</label>
                                <select name="document_ai_provider" class="form-select form-select-lg bg-slate-50 border-slate-200 rounded-3">
                                    <option value="none" {{ $profile->document_ai_provider == 'none' ? 'selected' : '' }}>Nonaktifkan</option>
                                    <option value="gemini" {{ $profile->document_ai_provider == 'gemini' ? 'selected' : '' }}>Google Gemini (Rekomendasi)</option>
                                    <option value="vision_api" {{ $profile->document_ai_provider == 'vision_api' ? 'selected' : '' }}>Google Vision API</option>
                                </select>
                            </div>

                            <div id="ai_key_section" class="{{ $profile->document_ai_provider == 'none' ? 'd-none' : '' }}">
                                <label class="form-label text-slate-700 fw-bold">API Key</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-slate-50 border-slate-200 rounded-start-3">
                                        <i class="fas fa-key text-slate-400"></i>
                                    </span>
                                    <input type="password" name="document_ai_key" value="{{ $profile->document_ai_key }}"
                                        class="form-control form-control-lg bg-slate-50 border-slate-200 rounded-end-3" 
                                        placeholder="Masukkan API Key Anda">
                                </div>
                                <div class="form-text text-[11px] text-slate-400 mt-2">
                                    <i class="fas fa-lock me-1"></i> Kunci ini akan disimpan secara terenkripsi di server.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Side: SOP & Procedures --}}
                <div class="col-xl-6">
                    <div class="card border-0 shadow-premium rounded-4 overflow-hidden h-100">
                        <div class="card-header bg-white py-4 px-4 border-bottom border-light">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 bg-emerald-50 text-emerald-600 rounded-3">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">SOP Verifikasi Petugas</h5>
                                    <p class="text-[11px] text-slate-400 mb-0">Instruksi wajib yang muncul saat petugas memvalidasi berkas.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label text-slate-700 fw-bold">Teks SOP Pelayanan</label>
                                <textarea name="validation_sop_text" class="form-control bg-slate-50 border-slate-200 rounded-4 p-3" 
                                    rows="10" placeholder="Tuliskan langkah-langkah verifikasi di sini... Example:
1. Cek NIK di SIAK
2. Klarifikasi ke pihak Desa
3. Pastikan foto berkas tajam dan tidak editan">{{ old('validation_sop_text', $profile->validation_sop_text) }}</textarea>
                            </div>
                            <div class="alert alert-emerald border-0 rounded-4">
                                <small class="fw-bold"><i class="fas fa-lightbulb me-1"></i> Tips:</small>
                                <small class="d-block mt-1">Teks ini akan muncul sebagai pengingat di bagian atas setiap berkas masuk agar operator selalu disiplin mengikuti aturan.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill fw-bold shadow-lg py-3">
                        <i class="fas fa-save me-2"></i> Simpan Pengaturan Layanan
                    </button>
                </div>
            </div>
        </form>

        {{-- Shortcuts to other service menus --}}
        <div class="row mt-5 g-4">
            <div class="col-md-4">
                <a href="{{ route('kecamatan.pelayanan.layanan.index') }}" class="card border-0 shadow-sm rounded-4 text-decoration-none hover-up overflow-hidden">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-4">
                            <i class="fas fa-list-ul fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-slate-900">Kelola Daftar Layanan</h6>
                            <p class="text-[11px] text-slate-400 mb-0">Atur syarat & jenis surat.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('kecamatan.pelayanan.faq.index') }}" class="card border-0 shadow-sm rounded-4 text-decoration-none hover-up overflow-hidden">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-amber-50 text-amber-600 rounded-4">
                            <i class="fas fa-question-circle fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-slate-900">Kelola FAQ</h6>
                            <p class="text-[11px] text-slate-400 mb-0">Atur jawaban otomatis warga.</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('kecamatan.pelayanan.visitor.index') }}" class="card border-0 shadow-sm rounded-4 text-decoration-none hover-up overflow-hidden">
                    <div class="card-body p-4 d-flex align-items-center gap-3">
                        <div class="p-3 bg-rose-50 text-rose-600 rounded-4">
                            <i class="fas fa-book fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-slate-900">Buku Tamu Fisik</h6>
                            <p class="text-[11px] text-slate-400 mb-0">Catatan kehadiran kantor.</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('select[name="document_ai_provider"]').addEventListener('change', function() {
            const section = document.getElementById('ai_key_section');
            if (this.value === 'none') {
                section.classList.add('d-none');
            } else {
                section.classList.remove('d-none');
            }
        });
    </script>

    <style>
        .hover-up:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        }
        .shadow-premium {
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
        }
    </style>
@endsection
