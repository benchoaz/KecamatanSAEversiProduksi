@extends('layouts.desa')

@section('title', 'Edit Data Personil')

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

                <!-- FORM CARD -->
                <div class="card border-0 shadow-layered rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-gradient-premium border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-white rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px;">
                                <i class="fas fa-user-edit text-primary-600"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold text-white mb-0" style="font-size: 1.1rem;">Edit Data
                                    {{ $kategori == 'perangkat' ? 'Perangkat Desa' : 'Anggota BPD' }}
                                </h5>
                                <small class="text-white opacity-75" style="font-size: 0.75rem;">Status: {{ ucfirst($personil->status) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('desa.administrasi.personil.update', $personil->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @php $readonly = !$personil->isEditable(); @endphp

                            <!-- 1. INFORMASI DASAR -->
                            <div class="mb-5">
                                <div class="section-header-premium mb-4">
                                    <div class="accent-bar"></div>
                                    <div>
                                        <h6 class="fw-bold text-slate-800 mb-1"><i
                                                class="fas fa-id-card me-2 text-primary-500"></i> Informasi Dasar</h6>
                                        <small class="text-slate-500">Data identitas, foto, dan kontak</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-4 text-center">
                                        <label class="form-label fw-bold text-slate-700 d-block">Pas Foto</label>
                                        <div class="mx-auto mb-2 bg-light rounded-3 d-flex align-items-center justify-content-center border" 
                                            style="width: 140px; height: 180px; border: 2px dashed #cbd5e1 !important;">
                                            @if($personil->foto)
                                                <img src="{{ route('desa.administrasi.file.personil', ['id' => $personil->id, 'type' => 'foto']) }}" 
                                                    class="img-fluid rounded-2" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <i class="fas fa-camera fa-3x text-slate-300"></i>
                                            @endif
                                        </div>
                                        @if(!$readonly)
                                            <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                                            <small class="text-slate-400 x-small mt-1 d-block">Ganti foto (Maks: 1MB)</small>
                                        @endif
                                    </div>
                                    <div class="col-md-8">
                                        <x-desa.form.input label="Nama Lengkap" name="nama" :value="$personil->nama"
                                            :readonly="$readonly" placeholder="Nama sesuai KTP" required="true" />

                                        <div class="mb-3">
                                            <label class="form-label fw-bold text-slate-700">NIK <span class="text-danger">*</span></label>
                                            <input type="text" name="nik" id="nikInput" class="form-control rounded-3 shadow-sm border-slate-300" 
                                                value="{{ $personil->nik }}" {{ $readonly ? 'readonly' : '' }}
                                                placeholder="16 Digit Angka" maxlength="16" required>
                                            <div id="nikCounter" class="x-small mt-1 fw-bold text-danger">0 / 16 digit</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <x-desa.form.input label="Tempat Lahir" name="tempat_lahir" :value="$personil->tempat_lahir"
                                            :readonly="$readonly" placeholder="Kota/Kab" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-desa.form.input label="Tanggal Lahir" name="tanggal_lahir" 
                                            type="date" :value="$personil->tanggal_lahir ? $personil->tanggal_lahir->format('Y-m-d') : ''" 
                                            :readonly="$readonly" required="true" />
                                    </div>
                                    <div class="col-md-4">
                                        <x-desa.form.input label="Nomor HP / WA" name="no_hp" :value="$personil->no_hp"
                                            :readonly="$readonly" placeholder="081xxx" />
                                    </div>
                                </div>

                                <div class="mb-4 mt-3">
                                    <label class="form-label fw-bold text-slate-700">Jabatan <span class="text-danger">*</span></label>
                                    <select name="jabatan" id="jabatanSelect" class="form-select rounded-3 border-slate-300 shadow-sm"
                                        @if($readonly) disabled @endif required>
                                        <option value="">Pilih Jabatan...</option>
                                        @if($kategori == 'perangkat')
                                            <option value="Kepala Desa" {{ $personil->jabatan == 'Kepala Desa' ? 'selected' : '' }}>Kepala Desa</option>
                                            <option value="Sekretaris Desa" {{ $personil->jabatan == 'Sekretaris Desa' ? 'selected' : '' }}>Sekretaris Desa</option>
                                            <option value="Kaur Keuangan" {{ $personil->jabatan == 'Kaur Keuangan' ? 'selected' : '' }}>Kaur Keuangan</option>
                                            <option value="Kaur Perencanaan" {{ $personil->jabatan == 'Kaur Perencanaan' ? 'selected' : '' }}>Kaur Perencanaan</option>
                                            <option value="Kaur Umum" {{ $personil->jabatan == 'Kaur Umum' ? 'selected' : '' }}>Kaur Umum</option>
                                            <option value="Kasi Pemerintahan" {{ $personil->jabatan == 'Kasi Pemerintahan' ? 'selected' : '' }}>Kasi Pemerintahan</option>
                                            <option value="Kasi Kesejahteraan" {{ $personil->jabatan == 'Kasi Kesejahteraan' ? 'selected' : '' }}>Kasi Kesejahteraan</option>
                                            <option value="Kasi Pelayanan" {{ $personil->jabatan == 'Kasi Pelayanan' ? 'selected' : '' }}>Kasi Pelayanan</option>
                                            <option value="Kepala Dusun" {{ $personil->jabatan == 'Kepala Dusun' ? 'selected' : '' }}>Kepala Dusun</option>
                                        @else
                                            <option value="Ketua BPD" {{ $personil->jabatan == 'Ketua BPD' ? 'selected' : '' }}>Ketua BPD</option>
                                            <option value="Wakil Ketua BPD" {{ $personil->jabatan == 'Wakil Ketua BPD' ? 'selected' : '' }}>Wakil Ketua BPD</option>
                                            <option value="Sekretaris BPD" {{ $personil->jabatan == 'Sekretaris BPD' ? 'selected' : '' }}>Sekretaris BPD</option>
                                            <option value="Anggota BPD" {{ $personil->jabatan == 'Anggota BPD' ? 'selected' : '' }}>Anggota BPD</option>
                                        @endif
                                    </select>
                                    @if($readonly) <input type="hidden" name="jabatan" value="{{ $personil->jabatan }}"> @endif
                                </div>

                                <div id="dusunWrapper" style="{{ $personil->jabatan == 'Kepala Dusun' ? 'display: block;' : 'display: none;' }}">
                                    <x-desa.form.input label="Nama Dusun" name="nama_dusun" :value="$personil->nama_dusun"
                                        :readonly="$readonly" placeholder="Contoh: Dusun Krajan" />
                                </div>
                            </div>

                            <hr class="border-light my-5">

                            <!-- 2. INFORMASI JABATAN & KEUANGAN -->
                            <div class="mb-5">
                                <div class="section-header-premium mb-4">
                                    <div class="accent-bar" style="background: #10b981;"></div>
                                    <div>
                                        <h6 class="fw-bold text-slate-800 mb-1"><i
                                                class="fas fa-money-bill-wave me-2 text-success"></i> Jabatan & Keuangan</h6>
                                        <small class="text-slate-500">Masa jabatan, Siltap, dan rekening bank</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <x-desa.form.input label="Mulai Menjabat (TMT)" name="masa_jabatan_mulai"
                                            type="date" :value="$personil->masa_jabatan_mulai ? $personil->masa_jabatan_mulai->format('Y-m-d') : ''" 
                                            :readonly="$readonly" required="true" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-desa.form.input label="Nama Bank" name="nama_bank" :value="$personil->nama_bank"
                                            :readonly="$readonly" placeholder="Contoh: Bank Jatim" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-desa.form.input label="Nomor Rekening Pribadi" name="rekening_bank" :value="$personil->rekening_bank"
                                            :readonly="$readonly" placeholder="Masukkan nomor rekening bank" />
                                    </div>
                                </div>
                            </div>

                            <hr class="border-light my-5">

                            <!-- 3. LEGALITAS (SK) -->
                            <div class="mb-5">
                                <div class="section-header-premium mb-4">
                                    <div class="accent-bar" style="background: #f59e0b;"></div>
                                    <div>
                                        <h6 class="fw-bold text-slate-800 mb-1"><i
                                                class="fas fa-file-signature me-2 text-warning"></i> Legalitas (SK)</h6>
                                        <small class="text-slate-500">Nomor SK dan lampiran dokumen PDF</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <x-desa.form.input label="Nomor SK" name="nomor_sk" :value="$personil->nomor_sk"
                                            :readonly="$readonly" placeholder="Contoh: 188/01/426.313.11/2024" required="true" />
                                    </div>
                                    <div class="col-md-6">
                                        <x-desa.form.input label="Tanggal SK" name="tanggal_sk" type="date"
                                            :value="$personil->tanggal_sk ? $personil->tanggal_sk->format('Y-m-d') : ''" 
                                            :readonly="$readonly" required="true" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-slate-700">Lampiran SK (PDF)</label>
                                    <input type="file" name="file_sk"
                                        class="form-control rounded-3 border-slate-300 shadow-sm" accept="application/pdf"
                                        @if($readonly) disabled @endif>
                                    @if($personil->file_sk)
                                        <div class="mt-2">
                                            <a href="{{ route('desa.administrasi.file.personil', $personil->id) }}" target="_blank" 
                                                class="btn btn-xs btn-outline-primary rounded-pill px-3">
                                                <i class="fas fa-file-pdf me-1"></i> Lihat SK Saat Ini
                                            </a>
                                        </div>
                                    @endif
                                    <small class="text-slate-500 mt-1 d-block"><i
                                            class="fas fa-info-circle me-1"></i> Kosongkan jika tidak ingin mengubah file.</small>
                                </div>
                            </div>

                            <div class="pt-4 border-top">
                                <div class="d-flex justify-content-end gap-2">
                                    @if($personil->isEditable())
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // NIK Counter Logic
            const nikInput = document.getElementById('nikInput');
            const nikCounter = document.getElementById('nikCounter');

            if (nikInput && nikCounter) {
                function updateNikCounter() {
                    const len = nikInput.value.length;
                    nikCounter.innerText = `${len} / 16 digit`;
                    if (len === 16) {
                        nikCounter.classList.remove('text-danger');
                        nikCounter.classList.add('text-success');
                        nikCounter.innerText += ' (Pas)';
                    } else {
                        nikCounter.classList.remove('text-success');
                        nikCounter.classList.add('text-danger');
                    }
                }

                nikInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    updateNikCounter();
                });
                
                updateNikCounter();
            }

            // Dusun Wrapper Logic
            const jabatanSelect = document.getElementById('jabatanSelect');
            const dusunWrapper = document.getElementById('dusunWrapper');

            if (jabatanSelect && dusunWrapper) {
                jabatanSelect.addEventListener('change', function() {
                    if (this.value === 'Kepala Dusun') {
                        dusunWrapper.style.display = 'block';
                    } else {
                        dusunWrapper.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endpush