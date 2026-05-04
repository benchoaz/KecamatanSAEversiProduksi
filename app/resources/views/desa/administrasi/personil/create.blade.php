@extends('layouts.desa')

@section('title', 'Input Data Personil')

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="mb-4">
                    <a href="{{ route('desa.administrasi.personil.index', ['kategori' => $kategori]) }}"
                        class="text-decoration-none text-slate-500 fw-medium">
                        <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
                    </a>
                </div>

                <div class="card border-0 shadow-layered rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-gradient-premium border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-white rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px;">
                                <i class="fas fa-user-plus text-primary-600"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-white mb-0" style="font-size: 1.1rem;">Input Data
                                    {{ $kategori == 'perangkat' ? 'Perangkat Desa' : 'Anggota BPD' }}
                                </h5>
                                <small class="text-white opacity-75" style="font-size: 0.75rem;">Pastikan data yang diinput
                                    sesuai dengan dokumen
                                    resmi.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('desa.administrasi.personil.store') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="kategori" value="{{ $kategori }}">

                            <!-- 1. INFORMASI DASAR -->
                            <div class="mb-4">
                                <div class="section-header-premium mb-3">
                                    <div class="accent-bar"></div>
                                    <div>
                                        <h6 class="fw-bold text-slate-800 mb-1"><i
                                                class="fas fa-id-card me-2 text-primary-500"></i> Informasi Dasar</h6>
                                        <small class="text-slate-500">Data identitas dan tempat tanggal lahir</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-slate-700">Pas Foto</label>
                                        <div class="mb-2 bg-light rounded-3 d-flex align-items-center justify-content-center border" 
                                            style="width: 120px; height: 160px; border: 2px dashed #cbd5e1 !important;">
                                            <i class="fas fa-camera fa-2x text-slate-300"></i>
                                        </div>
                                        <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                                        <small class="text-slate-400 x-small">Maks: 1MB (JPG/PNG)</small>
                                    </div>
                                    <div class="col-md-8">
                                        <x-desa.form.input label="Nama Lengkap" name="nama" placeholder="Nama sesuai KTP"
                                            required="true" />

                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-slate-700">NIK <span class="text-danger">*</span></label>
                                            <input type="text" name="nik" id="nikInput" class="form-control rounded-3" 
                                                placeholder="16 Digit Angka" maxlength="16" required>
                                            <div id="nikCounter" class="x-small mt-1 fw-bold text-danger">0 / 16 digit</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <x-desa.form.input label="Tempat Lahir" name="tempat_lahir" placeholder="Kota/Kab"
                                            required="true" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-desa.form.input label="Tanggal Lahir" name="tanggal_lahir" type="date"
                                            required="true" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-desa.form.input label="Nomor HP / WA" name="no_hp" placeholder="081xxx" />
                                    </div>
                                </div>

                                <!-- Custom Select for Jabatan (Since logic varies) -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-slate-700">Jabatan <span
                                            class="text-danger">*</span></label>
                                    <select name="jabatan" id="jabatanSelect" class="form-select rounded-3 border-slate-300 shadow-sm"
                                        required>
                                        <option value="">Pilih Jabatan...</option>
                                        @if($kategori == 'perangkat')
                                            <option value="Kepala Desa">Kepala Desa</option>
                                            <option value="Sekretaris Desa">Sekretaris Desa</option>
                                            <option value="Kaur Keuangan">Kaur Keuangan</option>
                                            <option value="Kaur Perencanaan">Kaur Perencanaan</option>
                                            <option value="Kaur Umum">Kaur Umum</option>
                                            <option value="Kasi Pemerintahan">Kasi Pemerintahan</option>
                                            <option value="Kasi Kesejahteraan">Kasi Kesejahteraan</option>
                                            <option value="Kasi Pelayanan">Kasi Pelayanan</option>
                                            <option value="Kepala Dusun">Kepala Dusun</option>
                                        @else
                                            <option value="Ketua BPD">Ketua BPD</option>
                                            <option value="Wakil Ketua BPD">Wakil Ketua BPD</option>
                                            <option value="Sekretaris BPD">Sekretaris BPD</option>
                                            <option value="Anggota BPD">Anggota BPD</option>
                                        @endif
                                    </select>
                                </div>

                                <div id="dusunWrapper" style="display: none;">
                                    <x-desa.form.input label="Nama Dusun" name="nama_dusun" placeholder="Contoh: Dusun Krajan" />
                                </div>

                                @if($kategori == 'perangkat')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-desa.form.input label="Mulai Menjabat (TMT)" name="masa_jabatan_mulai"
                                                type="date" required="true" />
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <hr class="border-light my-5">

                            <!-- 3. DOKUMEN LEGALITAS -->
                            <div class="mb-4">
                                <div class="section-header-premium mb-4">
                                    <div class="accent-bar"></div>
                                    <div>
                                        <h6 class="fw-bold text-slate-800 mb-1"><i
                                                class="fas fa-file-contract me-2 text-primary-500"></i> Dokumen Legalitas
                                        </h6>
                                        <small class="text-slate-500">SK Pengangkatan dan file pendukung</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <x-desa.form.input label="Nomor SK Pengangkatan" name="nomor_sk"
                                            placeholder="Nomor Surat Keputusan" required="true" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-desa.form.input label="Tanggal SK" name="tanggal_sk" type="date"
                                            required="true" />
                                    </div>
                                </div>

                                <x-desa.form.upload label="File SK (Scan PDF)" name="file_sk"
                                    helper="Lampirkan scan asli SK Pengangkatan. Pastikan tulisan terbaca jelas."
                                    required="true" />
                            </div>

                            @if($kategori == 'perangkat')
                                <hr class="border-light my-5">

                                <!-- 2. INFORMASI KEUANGAN & PERBANKAN -->
                                <div class="mb-4">
                                    <div class="section-header-premium mb-4">
                                        <div class="accent-bar"></div>
                                        <div>
                                            <h6 class="fw-bold text-slate-800 mb-1"><i
                                                    class="fas fa-money-bill-wave me-2 text-primary-500"></i> Informasi Keuangan & Perbankan
                                            </h6>
                                            <small class="text-slate-500">Data penghasilan tetap dan rekening bank</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <x-desa.form.input label="Siltap Pokok (Rp)" name="siltap_pokok" type="number" placeholder="Contoh: 2400000" />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <x-desa.form.input label="Nama Bank" name="nama_bank" placeholder="Contoh: Bank Jatim" />
                                        </div>
                                        <div class="col-md-6">
                                            <x-desa.form.input label="Nomor Rekening Pribadi" name="rekening_bank" placeholder="Nomor Rekening Bank" />
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- 3. AKSI -->
                            <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                                <a href="{{ route('desa.administrasi.personil.index', ['kategori' => $kategori]) }}"
                                    class="btn btn-light rounded-pill px-4">Batal</a>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm fw-bold">
                                    <i class="fas fa-save me-2"></i> Simpan Draft
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. JABATAN & DUSUN TOGGLE ---
        const jabatanSelect = document.getElementById('jabatanSelect');
        const dusunWrapper = document.getElementById('dusunWrapper');
        const dusunInput = dusunWrapper ? dusunWrapper.querySelector('input') : null;
        
        function toggleDusun() {
            if (jabatanSelect && jabatanSelect.value === 'Kepala Dusun') {
                dusunWrapper.style.display = 'block';
                if(dusunInput) dusunInput.setAttribute('required', 'required');
            } else if(dusunWrapper) {
                dusunWrapper.style.display = 'none';
                if(dusunInput) {
                    dusunInput.removeAttribute('required');
                    dusunInput.value = '';
                }
            }
        }

        if(jabatanSelect) {
            jabatanSelect.addEventListener('change', toggleDusun);
            toggleDusun();
        }

        // --- 2. NIK COUNTER ---
        const nikInput = document.querySelector('input[name="nik"]');
        if (nikInput) {
            // Create counter element
            const counterDiv = document.createElement('div');
            counterDiv.id = 'nikCounter';
            counterDiv.style.cssText = 'font-size: 0.65rem; font-weight: bold; margin-top: 2px; transition: color 0.3s;';
            nikInput.parentNode.appendChild(counterDiv);

            function updateCounter() {
                const len = nikInput.value.length;
                counterDiv.innerText = `${len} / 16 digit`;
                if (len === 16) {
                    counterDiv.style.color = '#10b981'; // Green-500
                    counterDiv.innerText += ' (Pas)';
                } else {
                    counterDiv.style.color = '#ef4444'; // Red-500
                }
            }

            nikInput.addEventListener('input', updateCounter);
            updateCounter(); // Initial check
        }
    });
</script>
@endpush
@endsection