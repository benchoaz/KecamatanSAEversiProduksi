@extends('layouts.kecamatan')

@php
    $profile = appProfile();
@endphp

@section('title', 'Pengaturan WhatsApp Bot')

@section('content')
    <div class="container-fluid px-4 py-4">
        <!-- Header -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <div class="flex-shrink-0 bg-success bg-opacity-10 rounded-3 p-3">
                <i class="fab fa-whatsapp text-success fs-3"></i>
            </div>
            <div>
                <h1 class="fw-bold fs-4 mb-0">Pengaturan WhatsApp Bot</h1>
                <p class="text-muted small mb-0">Update nomor WhatsApp bot yang ditampilkan di halaman depan.</p>
            </div>
            <div class="ms-auto d-flex gap-2">
                <button type="button" onclick="syncMemory()" class="btn btn-outline-indigo btn-sm">
                    <i class="fas fa-sync me-1"></i> Sinkronkan Memori AI
                </button>
                <a href="{{ route('kecamatan.settings.waha-n8n.provider') }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plug me-1"></i> Konfigurasi Provider WA
                </a>
            </div>
        </div>

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: "{{ session('success') }}",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                });
            </script>
        @endif

        <!-- Bot Settings Form -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-4 px-4 border-bottom border-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-mobile-alt text-success"></i>
                    <h5 class="mb-0 fw-bold">Nomor WhatsApp Bot</h5>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('kecamatan.settings.waha-n8n.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-slate-700 fw-semibold">
                                <i class="fab fa-whatsapp text-success me-1"></i>
                                Nomor WhatsApp Bot
                            </label>
                            <input type="text" name="bot_number"
                                value="{{ old('bot_number', $settings->bot_number ? '0' . substr(preg_replace('/^62/', '', $settings->bot_number), 0) : '') }}"
                                class="form-control bg-white border-slate-200 rounded-3 @error('bot_number') is-invalid @enderror"
                                placeholder="08xxxxxxxxxx">
                            @error('bot_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-slate-400">
                                Format: 08xxxxxxxxxx (akan dikonversi ke 628xxx)
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-700 fw-semibold">
                                <i class="fas fa-user-shield text-primary me-1"></i>
                                Nomor WhatsApp Operator (Admin)
                            </label>
                            <input type="text" name="operator_number"
                                value="{{ old('operator_number', $settings->operator_number ? '0' . substr(preg_replace('/^62/', '', $settings->operator_number), 0) : '') }}"
                                class="form-control bg-white border-slate-200 rounded-3 @error('operator_number') is-invalid @enderror"
                                placeholder="08xxxxxxxxxx">
                            @error('operator_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-slate-400">
                                Nomor ini akan menerima notifikasi setiap ada warga yang mengajukan layanan/pengaduan. (Mengambil nomor dari Profil jika kosong)
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-700 fw-semibold d-block">Status Bot</label>
                            <div class="d-flex align-items-center gap-3 mt-1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="bot_enabled" value="1" {{ $settings->bot_enabled ? 'checked' : '' }} style="width: 50px; height: 25px;">
                                </div>
                                <span class="text-slate-600">
                                    {{ $settings->bot_enabled ? 'Bot Aktif' : 'Bot Nonaktif' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <hr class="my-4 text-light">
                        </div>

                        <!-- Konfigurasi Asisten AI -->
                        <div class="col-md-12 mt-2 mb-2" id="ai-settings-container">
                            <div class="p-4 border border-indigo-100 bg-indigo-50 bg-opacity-30 rounded-4 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom border-indigo-200 pb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-brain text-indigo-600 fs-5"></i>
                                        <h6 class="mb-0 fw-bold text-indigo-900">Otak Asisten AI (Otomatis Menjawab Pesan)</h6>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <label class="form-check-label small fw-bold me-2" for="is_ai_active">Aktifkan AI</label>
                                        <input class="form-check-input" type="checkbox" name="is_ai_active" id="is_ai_active" value="1" {{ ($profile->is_ai_active ?? true) ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Nama Bot AI (Persona)</label>
                                        <input type="text" name="ai_bot_name" value="{{ old('ai_bot_name', $profile->ai_bot_name ?? 'Kecamatan SAE Assistant') }}" class="form-control bg-white border-slate-200 rounded-3 text-sm" placeholder="Contoh: Admin Cantik, Si Pintar, dll">
                                        <div class="form-text text-slate-400 small">Nama ini akan digunakan AI untuk memperkenalkan diri.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Instruksi Tambahan (System Prompt)</label>
                                        <textarea name="ai_bot_instruction" class="form-control bg-white border-slate-200 rounded-3 text-sm" rows="1" placeholder="Contoh: Gunakan bahasa yang sangat sopan dan ramah.">{{ old('ai_bot_instruction', $profile->ai_bot_instruction) }}</textarea>
                                        <div class="form-text text-slate-400 small">Instruksi khusus untuk mengatur gaya bicara AI.</div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label text-slate-700 fw-semibold">Pilih Provider AI</label>
                                        <select name="ai_provider" class="form-select bg-white border-slate-200 rounded-3 text-sm mb-4">
                                            <option value="gemini" {{ ($profile->ai_provider ?? 'gemini') == 'gemini' ? 'selected' : '' }}>Google Gemini (Rekomendasi - Cepat & Murah)</option>
                                            <option value="openai" {{ ($profile->ai_provider ?? '') == 'openai' ? 'selected' : '' }}>OpenAI (ChatGPT)</option>
                                            <option value="anthropic" {{ ($profile->ai_provider ?? '') == 'anthropic' ? 'selected' : '' }}>Anthropic (Claude)</option>
                                            <option value="deepseek" {{ ($profile->ai_provider ?? '') == 'deepseek' ? 'selected' : '' }}>DeepSeek</option>
                                            <option value="xai" {{ ($profile->ai_provider ?? '') == 'xai' ? 'selected' : '' }}>xAI (Grok)</option>
                                            <option value="openrouter" {{ ($profile->ai_provider ?? '') == 'openrouter' ? 'selected' : '' }}>OpenRouter</option>
                                            <option value="dashscope" {{ ($profile->ai_provider ?? '') == 'dashscope' ? 'selected' : '' }}>Alibaba DashScope (Qwen)</option>
                                        </select>

                                        <!-- STATUS KUOTA AI (VERSI ELEGAN & RAPI) -->
                                        <div class="card border-0 rounded-4 overflow-hidden mb-4 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid #e2e8f0 !important;">
                                            <div class="card-body p-0">
                                                <div class="row g-0">
                                                    <div class="col-md-auto bg-primary d-flex align-items-center justify-content-center" style="width: 80px; background: linear-gradient(to bottom, #4f46e5, #6366f1) !important;">
                                                        <div class="text-center">
                                                            <i class="fas fa-microchip text-white fs-3 mb-1 d-block animate__animated animate__pulse animate__infinite"></i>
                                                            <span class="text-white fw-bold" style="font-size: 0.6rem; opacity: 0.8; letter-spacing: 1px;">AI CORE</span>
                                                        </div>
                                                    </div>
                                                    <div class="col p-4">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <div>
                                                                <h6 class="text-slate-800 fw-bold mb-1">Status Kapasitas AI</h6>
                                                                <p class="text-slate-500 small mb-0">Estimasi ketersediaan token untuk merespon warga.</p>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                                                                    <i class="fas fa-check-circle me-1"></i> Sistem Optimal
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="progress rounded-pill bg-white shadow-inner mb-2" style="height: 14px; border: 1px solid #f1f5f9;">
                                                            <div class="progress-bar bg-gradient-primary rounded-pill progress-bar-striped progress-bar-animated" role="progressbar" style="width: 85%; background: linear-gradient(45deg, #4f46e5, #818cf8) !important;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                                            <div class="d-flex gap-4">
                                                                <div>
                                                                    <span class="text-slate-400 d-block small mb-1">Sisa Estimasi</span>
                                                                    <span class="text-slate-800 fw-bold">~850.240 <span class="text-slate-400 fw-normal small">Token</span></span>
                                                                </div>
                                                                <div class="border-start ps-4">
                                                                    <span class="text-slate-400 d-block small mb-1">Total Respon</span>
                                                                    <span class="text-slate-800 fw-bold">1.240 <span class="text-slate-400 fw-normal small">Pesan</span></span>
                                                                </div>
                                                            </div>
                                                            <div class="text-end">
                                                                <a href="https://aistudio.google.com/app/billing" target="_blank" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" style="font-size: 0.75rem;">
                                                                    <i class="fas fa-shopping-cart me-2"></i> Beli Kuota Baru
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 small fw-bold"><i class="fab fa-google text-danger"></i> Google API Key (Gemini)</label>
                                        <div class="input-group">
                                            <input type="password" id="gemini_api_key" name="google_api_key" value="{{ old('google_api_key', $profile->google_api_key) }}" class="form-control bg-white border-slate-200 text-sm" placeholder="AIzaSy...">
                                            <button type="button" class="btn btn-outline-success" onclick="testApiKey('gemini')"><i class="fas fa-check-circle"></i> Test</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 small fw-bold">OpenAI API Key (ChatGPT)</label>
                                        <div class="input-group">
                                            <input type="password" id="openai_api_key" name="openai_api_key" value="{{ old('openai_api_key', $profile->openai_api_key) }}" class="form-control bg-white border-slate-200 text-sm" placeholder="sk-...">
                                            <button type="button" class="btn btn-outline-success" onclick="testApiKey('openai')"><i class="fas fa-check-circle"></i> Test</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 small fw-bold">DeepSeek API Key</label>
                                        <div class="input-group">
                                            <input type="password" id="deepseek_api_key" name="deepseek_api_key" value="{{ old('deepseek_api_key', $profile->deepseek_api_key) }}" class="form-control bg-white border-slate-200 text-sm" placeholder="sk-...">
                                            <button type="button" class="btn btn-outline-success" onclick="testApiKey('deepseek')"><i class="fas fa-check-circle"></i> Test</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 small fw-bold">xAI API Key (Grok)</label>
                                        <div class="input-group">
                                            <input type="password" id="xai_api_key" name="xai_api_key" value="{{ old('xai_api_key', $profile->xai_api_key) }}" class="form-control bg-white border-slate-200 text-sm" placeholder="xai-...">
                                            <button type="button" class="btn btn-outline-success" onclick="testApiKey('xai')"><i class="fas fa-check-circle"></i> Test</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <hr class="my-4 text-light">
                        </div>

                        {{-- =====================================================
                             BOT WHATSAPP SETTINGS (Moved from Profile)
                        ===================================================== --}}
                        <div class="col-md-12">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-link text-success fs-5"></i>
                                <h6 class="mb-0 fw-bold text-success">Menu Bot & Tautan Publik</h6>
                            </div>
                            <p class="text-slate-500 small mb-4">Konfigurasi link publik dan menu yang akan dikirim bot ke pengguna WhatsApp. Pastikan URL yang diinput sudah dapat diakses dari internet.</p>

                            {{-- Public URL --}}
                            <div class="mb-4">
                                <label class="form-label text-slate-700 fw-semibold">
                                    <i class="fas fa-globe text-success me-1"></i> URL Publik Aplikasi
                                </label>
                                <input type="text" name="public_url"
                                    value="{{ old('public_url', $profile->public_url) }}"
                                    class="form-control bg-white border-success border-opacity-25 rounded-3 @error('public_url') is-invalid @enderror"
                                    placeholder="Contoh: https://kecamatanbesuk.my.id:8443">
                                @error('public_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-success text-opacity-75 small mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    URL ini akan digunakan bot di semua link yang dikirim ke pengguna (produk, layanan, pengaduan, dll). Jika kosong, sistem menggunakan APP_URL dari .env.
                                </div>
                            </div>

                            {{-- Menu Bot Editor --}}
                            <div>
                                <label class="form-label text-slate-700 fw-semibold d-flex align-items-center gap-2">
                                    <i class="fas fa-list-ul text-success"></i> Menu Bot WhatsApp
                                    <span class="badge bg-success bg-opacity-15 text-success small fw-normal">Dapat Dikustomisasi</span>
                                </label>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="text-slate-500 small mb-0">Atur urutan, label, deskripsi, dan visibilitas menu yang ditampilkan bot.</p>
                                    <div class="d-flex gap-2">
                                        <button type="button" onclick="resetToDefault()" class="btn btn-outline-secondary btn-sm rounded-3">
                                            <i class="fas fa-undo me-1"></i> Reset ke Default
                                        </button>
                                        <button type="button" onclick="addMenuRow()" class="btn btn-success btn-sm rounded-3">
                                            <i class="fas fa-plus me-1"></i> Tambah Item Menu
                                        </button>
                                    </div>
                                </div>

                                        <div id="bot-menu-container">
                                            @php
                                                $availableActions = [
                                                    'administrasi'  => '🏛️ Layanan Administrasi',
                                                    'umkm_produk'   => '🛍️ Produk UMKM',
                                                    'jasa'          => '🔧 Cari Jasa',
                                                    'pengaduan'     => '📢 Pengaduan Warga',
                                                    'kelola_profil' => '👤 Kelola Profil',
                                                    'submenu'       => '📂 Buka Sub-Menu Baru (Berjenjang)',
                                                    'custom'        => '🔗 Tautan Luar / Link',
                                                ];

                                                $defaultMenu = [
                                                    ['number'=>'1','label'=>'ADMINISTRASI','description'=>'Cek Syarat dan Status Berkas','action'=>'administrasi','enabled'=>true],
                                                    ['number'=>'2','label'=>'PRODUK UMKM','description'=>'Belanja Produk & Olahan Warga Lokal','action'=>'umkm_produk','enabled'=>true],
                                                    ['number'=>'3','label'=>'CARI JASA','description'=>'Tukang, ART, Ojek, Tenaga Harian','action'=>'jasa','enabled'=>true],
                                                    ['number'=>'4','label'=>'PENGADUAN','description'=>'Aspirasi dan Laporan Warga','action'=>'pengaduan','enabled'=>true],
                                                    ['number'=>'5','label'=>'KELOLA PROFIL','description'=>'Kelola Data Jasa / Toko UMKM Anda','action'=>'kelola_profil','enabled'=>true],
                                                ];
                                                $menuItems = $profile->whatsapp_bot_menu;
                                                if (is_string($menuItems)) {
                                                    $menuItems = json_decode($menuItems, true);
                                                }
                                                $menuItems = $menuItems ?: $defaultMenu;
                                            @endphp

                                            @foreach($menuItems as $i => $item)
                                            <div class="menu-item-row bg-slate-50 border border-slate-200 rounded-3 p-3 mb-3 d-flex flex-wrap align-items-center gap-3">
                                                {{-- Layout: Row 1 --}}
                                                <div class="d-flex align-items-center gap-3 w-100 mb-2">
                                                    {{-- Drag handle --}}
                                                    <span class="text-slate-300" style="cursor:grab;"><i class="fas fa-grip-vertical"></i></span>

                                                    {{-- Nomor --}}
                                                    <span class="badge bg-success text-white fw-bold px-3 py-2" style="font-size:1rem; min-width:2.2rem;">{{ $i+1 }}</span>

                                                    {{-- Label --}}
                                                    <input type="text"
                                                        name="whatsapp_bot_menu[{{ $i }}][label]"
                                                        value="{{ old('whatsapp_bot_menu.'.$i.'.label', $item['label'] ?? '') }}"
                                                        class="form-control form-control-sm fw-bold text-slate-800"
                                                        style="max-width:180px;"
                                                        placeholder="Contoh: ADMINISTRASI" required>

                                                    {{-- Description --}}
                                                    <input type="text"
                                                        name="whatsapp_bot_menu[{{ $i }}][description]"
                                                        value="{{ old('whatsapp_bot_menu.'.$i.'.description', $item['description'] ?? '') }}"
                                                        class="form-control form-control-sm text-slate-600 flex-grow-1"
                                                        placeholder="Penjelasan singkat menu">

                                                    {{-- Enabled toggle --}}
                                                    <div class="form-check form-switch mb-0" title="Aktif/Nonaktif">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="whatsapp_bot_menu[{{ $i }}][enabled]"
                                                            value="1"
                                                            {{ ($item['enabled'] ?? true) ? 'checked' : '' }}
                                                            style="width:2em; height:1.1em;">
                                                    </div>

                                                    {{-- Delete Button --}}
                                                    <button type="button" onclick="removeMenuRow(this)" class="btn btn-link text-danger p-0 ms-1" title="Hapus Item">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </div>

                                                {{-- Layout: Row 2 (Action Choice) --}}
                                                <div class="d-flex align-items-center gap-2 ps-5 w-100">
                                                    <label class="small text-slate-500 fw-semibold" style="min-width: 80px;">Tipe Aksi:</label>
                                                    <select name="whatsapp_bot_menu[{{ $i }}][action]" class="form-select form-select-sm border-slate-200 text-slate-700" style="max-width: 250px;" onchange="handleActionChange(this)">
                                                        @foreach($availableActions as $val => $text)
                                                            <option value="{{ $val }}" {{ ($item['action'] ?? 'custom') == $val ? 'selected' : '' }}>{{ $text }}</option>
                                                        @endforeach
                                                    </select>

                                                    {{-- Submenu button (only if action is submenu) --}}
                                                    <div class="submenu-container {{ ($item['action'] ?? '') == 'submenu' ? '' : 'd-none' }}">
                                                        <button type="button" onclick="editSubmenu(this)" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                                            <i class="fas fa-sitemap me-1"></i> Atur Sub-Menu
                                                        </button>
                                                        {{-- Hidden storage for submenu JSON --}}
                                                        <input type="hidden" name="whatsapp_bot_menu[{{ $i }}][children]" value="{{ json_encode($item['children'] ?? []) }}" class="submenu-data">
                                                    </div>

                                                    {{-- Custom Link Input (only if action is custom) --}}
                                                    <div class="link-container flex-grow-1 {{ ($item['action'] ?? 'custom') == 'custom' ? '' : 'd-none' }}">
                                                        <input type="text" name="whatsapp_bot_menu[{{ $i }}][url]" value="{{ $item['url'] ?? '' }}" class="form-control form-control-sm border-slate-200" placeholder="https://...">
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>

                                        {{-- Live Preview --}}
                                        <div class="row mt-4">
                                            <div class="col-md-12">
                                                <div class="p-3 bg-dark rounded-4 text-white small shadow-lg border border-slate-700" style="font-family: 'Inter', sans-serif; white-space: pre-wrap; font-size:0.85rem; max-height: 250px; overflow-y:auto; line-height: 1.5;" id="bot-preview">
                                                    <span class="text-slate-400">// Preview menu bot akan muncul di sini...</span>
                                                </div>
                                                <div class="form-text text-slate-400 small mt-1">
                                                    <i class="fas fa-eye me-1"></i> Preview otomatis menyesuaikan perubahan di atas.
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="col-md-12 text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-4 fw-bold shadow-sm">
                                        <i class="fas fa-save me-1"></i> Simpan Pengaturan Bot
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Submenu Editor Modal --}}
                <div class="modal fade" id="submenuModal" tabindex="-1" aria-labelledby="submenuModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header border-light p-4">
                                <h5 class="modal-title fw-bold" id="submenuModalLabel">Atur Sub-Menu</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4 bg-slate-50">
                                <p class="text-muted small mb-3">Tentukan pilihan yang muncul saat pengguna memilih menu ini di WhatsApp.</p>
                                
                                <div id="submenu-items-container">
                                    {{-- Generated via JS --}}
                                </div>

                                <button type="button" onclick="addSubmenuRow()" class="btn btn-outline-success btn-sm w-100 mt-2 py-2 border-dashed">
                                    <i class="fas fa-plus me-1"></i> Tambah Pilihan Baru
                                </button>
                            </div>
                            <div class="modal-footer border-light p-3">
                                <button type="button" class="btn btn-slate-200 text-slate-600 fw-bold px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="button" onclick="saveSubmenuChanges()" class="btn btn-success fw-bold px-4">Selesai & Simpan</button>
                            </div>
                        </div>
                    </div>
                </div>

        <!-- Preview Banner -->
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-white py-3 px-4 border-bottom border-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-eye text-primary"></i>
                    <h6 class="mb-0 fw-bold">Preview Banner</h6>
                </div>
            </div>
            <div class="card-body p-4">
                @if($settings->bot_number && $settings->bot_enabled)
                    <div class="alert alert-success d-flex align-items-center gap-3">
                        <i class="fab fa-whatsapp fa-2x"></i>
                        <div>
                            <strong>WhatsApp Bot Aktif</strong>
                            <div class="mb-0 text-dark">
                                Hubungi kami:
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings->bot_number) }}"
                                    target="_blank" class="btn btn-success btn-sm ms-2">
                                    <i class="fab fa-whatsapp me-1"></i>
                                    Hubungi via WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-secondary d-flex align-items-center gap-3">
                        <i class="fab fa-whatsapp fa-2x text-muted"></i>
                        <div>
                            <strong>Bot Nonaktif</strong>
                            <div class="mb-0 text-muted">Nomor belum dikonfigurasi atau bot dimatikan.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Catatan -->
        <div class="mt-4 p-3 bg-info bg-opacity-10 rounded-3">
            <div class="d-flex gap-2">
                <i class="fas fa-info-circle text-info mt-1"></i>
                <div>
                    <strong>Catatan:</strong>
                    <ul class="mb-0 small text-muted">
                        <li>Nomor bot ini akan ditampilkan di halaman landing sebagai tombol "Hubungi via WhatsApp"</li>
                        <li>Pengaturan webhook dan koneksi WAHA/n8n dikonfigurasi langsung di dashboard masing-masing</li>
                        <li>Untuk update webhook, silakan akses dashboard WAHA secara langsung</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const defaultBotMenu = [
    {number: '1', label: 'ADMINISTRASI', description: 'Cek Syarat dan Status Berkas', action: 'administrasi', enabled: true},
    {number: '2', label: 'PRODUK UMKM', description: 'Belanja Produk & Olahan Warga Lokal', action: 'umkm_produk', enabled: true},
    {number: '3', label: 'CARI JASA', description: 'Tukang, ART, Ojek, Tenaga Harian', action: 'jasa', enabled: true},
    {number: '4', label: 'PENGADUAN', description: 'Aspirasi dan Laporan Warga', action: 'pengaduan', enabled: false},
    {number: '5', label: 'KELOLA PROFIL', description: 'Kelola Data Jasa / Toko UMKM Anda', action: 'kelola_profil', enabled: true}
];

let activeSubmenuInput = null;

function updateBotPreview() {
    const regionName = '{{ strtoupper($profile->region_name ?? 'KECAMATAN') }}';
    const rows = document.querySelectorAll('#bot-menu-container > .menu-item-row');
    let num = 1;
    let preview = `MENU LAYANAN KECAMATAN ${regionName}\n\nSilakan pilih layanan (Ketik angka):\n\n`;
    
    rows.forEach((row, index) => {
        // Update the visible badge number
        const badge = row.querySelector('.badge');
        if (badge) badge.innerText = index + 1;
        
        // Update name attributes for Laravel array input
        row.querySelectorAll('[name^="whatsapp_bot_menu"]').forEach(input => {
            const currentName = input.getAttribute('name');
            const newName = currentName.replace(/whatsapp_bot_menu\[\d+\]/, `whatsapp_bot_menu[${index}]`);
            input.setAttribute('name', newName);
        });

        // Build preview
        const enabled = row.querySelector('input[name*="[enabled]"]')?.checked ?? true;
        const label = row.querySelector('input[name*="[label]"]')?.value || '';
        const desc = row.querySelector('input[name*="[description]"]')?.value || '';
        
        if (enabled && label) {
            preview += `${num}. ${label} - ${desc}\n`;
            num++;
        }
    });

    preview += `\nKetik MENU kapan saja untuk kembali.`;
    document.getElementById('bot-preview').innerText = preview;
}

function handleActionChange(select) {
    const row = select.closest('.menu-item-row');
    const submenuContainer = row.querySelector('.submenu-container');
    const linkContainer = row.querySelector('.link-container');
    
    if (select.value === 'submenu') {
        submenuContainer.classList.remove('d-none');
        linkContainer.classList.add('d-none');
    } else if (select.value === 'custom') {
        submenuContainer.classList.add('d-none');
        linkContainer.classList.remove('d-none');
    } else {
        submenuContainer.classList.add('d-none');
        linkContainer.classList.add('d-none');
    }
}

function addMenuRow(data = null) {
    const container = document.getElementById('bot-menu-container');
    const index = container.querySelectorAll('.menu-item-row').length;
    
    const label = data ? data.label : '';
    const desc = data ? data.description : '';
    const action = data ? data.action : 'custom';
    const enabled = data ? (data.enabled ? 'checked' : '') : 'checked';
    const url = data ? (data.url || '') : '';
    const children = data ? (data.children || []) : [];
    
    const html = `
    <div class="menu-item-row bg-slate-50 border border-slate-200 rounded-3 p-3 mb-3 d-flex flex-wrap align-items-center gap-3 animate__animated animate__fadeInUp">
        <div class="d-flex align-items-center gap-3 w-100 mb-2">
            <span class="text-slate-300" style="cursor:grab;"><i class="fas fa-grip-vertical"></i></span>
            <span class="badge bg-success text-white fw-bold px-3 py-2" style="font-size:1rem; min-width:2.2rem;">${index + 1}</span>
            
            <input type="text" name="whatsapp_bot_menu[${index}][label]" value="${label}" 
                class="form-control form-control-sm fw-bold text-slate-800" style="max-width:180px;" placeholder="Contoh: ADMINISTRASI" required>
            
            <input type="text" name="whatsapp_bot_menu[${index}][description]" value="${desc}" 
                class="form-control form-control-sm text-slate-600 flex-grow-1" placeholder="Penjelasan singkat">
            
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" name="whatsapp_bot_menu[${index}][enabled]" value="1" ${enabled} style="width:2em; height:1.1em;">
            </div>
            
            <button type="button" onclick="removeMenuRow(this)" class="btn btn-link text-danger p-0 ms-1" title="Hapus Item">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>

        <div class="d-flex align-items-center gap-2 ps-5 w-100">
            <label class="small text-slate-500 fw-semibold" style="min-width: 80px;">Tipe Aksi:</label>
            <select name="whatsapp_bot_menu[${index}][action]" class="form-select form-select-sm border-slate-200 text-slate-700" style="max-width: 250px;" onchange="handleActionChange(this)">
                <option value="administrasi" ${action === 'administrasi' ? 'selected' : ''}>🏛️ Layanan Administrasi</option>
                <option value="umkm_produk" ${action === 'umkm_produk' ? 'selected' : ''}>🛍️ Produk UMKM</option>
                <option value="jasa" ${action === 'jasa' ? 'selected' : ''}>🔧 Cari Jasa</option>
                <option value="pengaduan" ${action === 'pengaduan' ? 'selected' : ''}>📢 Pengaduan Warga</option>
                <option value="kelola_profil" ${action === 'kelola_profil' ? 'selected' : ''}>👤 Kelola Profil</option>
                <option value="submenu" ${action === 'submenu' ? 'selected' : ''}>📂 Buka Sub-Menu Baru (Berjenjang)</option>
                <option value="custom" ${action === 'custom' ? 'selected' : ''}>🔗 Tautan Luar / Link</option>
            </select>

            <div class="submenu-container ${action === 'submenu' ? '' : 'd-none'}">
                <button type="button" onclick="editSubmenu(this)" class="btn btn-outline-success btn-sm rounded-pill px-3">
                    <i class="fas fa-sitemap me-1"></i> Atur Sub-Menu
                </button>
                <input type="hidden" name="whatsapp_bot_menu[${index}][children]" value='${JSON.stringify(children)}' class="submenu-data">
            </div>

            <div class="link-container flex-grow-1 ${action === 'custom' ? '' : 'd-none'}">
                <input type="text" name="whatsapp_bot_menu[${index}][url]" value="${url}" class="form-control form-control-sm border-slate-200" placeholder="https://...">
            </div>
        </div>
    </div>`;
    
    container.insertAdjacentHTML('beforeend', html);
    updateBotPreview();
}

function removeMenuRow(btn) {
    if (confirm('Hapus item menu ini?')) {
        const row = btn.closest('.menu-item-row');
        row.classList.add('animate__fadeOutDown');
        setTimeout(() => {
            row.remove();
            updateBotPreview();
        }, 300);
    }
}

// --- SUBMENU MODAL LOGIC ---

function editSubmenu(btn) {
    activeSubmenuInput = btn.closest('.submenu-container').querySelector('.submenu-data');
    const container = document.getElementById('submenu-items-container');
    container.innerHTML = '';
    
    let children = [];
    try {
        children = JSON.parse(activeSubmenuInput.value || '[]');
    } catch(e) { children = []; }
    
    if (children.length === 0) {
        // Add 2 default empty rows if none exist
        addSubmenuRow();
        addSubmenuRow();
    } else {
        children.forEach(child => addSubmenuRow(child));
    }
    
    new bootstrap.Modal(document.getElementById('submenuModal')).show();
}

function addSubmenuRow(data = null) {
    const container = document.getElementById('submenu-items-container');
    const index = container.querySelectorAll('.submenu-item-row').length;
    
    const label = data ? data.label : '';
    const desc = data ? data.description : '';
    const action = data ? data.action : 'custom';
    const url = data ? (data.url || '') : '';
    
    const html = `
    <div class="submenu-item-row bg-white border border-slate-200 rounded-3 p-3 mb-2 d-flex align-items-center gap-2 shadow-sm">
        <span class="badge bg-light text-slate-400 fw-bold">${index + 1}</span>
        
        <input type="text" class="form-control form-control-sm fw-bold sub-label" value="${label}" placeholder="Label (Spt: STATUS)" style="max-width: 140px;">
        <input type="text" class="form-control form-control-sm sub-desc" value="${desc}" placeholder="Deskripsi singkat" class="flex-grow-1">
        
        <select class="form-select form-select-sm sub-action" style="max-width: 150px;" onchange="handleSubActionChange(this)">
            <option value="custom" ${action === 'custom' ? 'selected' : ''}>🔗 Link/Aksi</option>
            <option value="back" ${action === 'back' ? 'selected' : ''}>🔙 Kembali</option>
            <option value="administrasi" ${action === 'administrasi' ? 'selected' : ''}>🏛️ Admin</option>
            <option value="umkm_produk" ${action === 'umkm_produk' ? 'selected' : ''}>🛍️ UMKM</option>
            <option value="jasa" ${action === 'jasa' ? 'selected' : ''}>🔧 Jasa</option>
            <option value="pengaduan" ${action === 'pengaduan' ? 'selected' : ''}>📢 Pengaduan</option>
        </select>
        
        <input type="text" class="form-control form-control-sm sub-url ${action === 'custom' ? '' : 'd-none'}" value="${url}" placeholder="URL" style="max-width: 120px;">
        
        <button type="button" onclick="this.closest('.submenu-item-row').remove()" class="btn btn-link text-danger p-0 ms-1">
            <i class="fas fa-times"></i>
        </button>
    </div>`;
    
    container.insertAdjacentHTML('beforeend', html);
}

function handleSubActionChange(select) {
    const urlInput = select.closest('.submenu-item-row').querySelector('.sub-url');
    if (select.value === 'custom') {
        urlInput.classList.remove('d-none');
    } else {
        urlInput.classList.add('d-none');
    }
}

function saveSubmenuChanges() {
    const container = document.getElementById('submenu-items-container');
    const rows = container.querySelectorAll('.submenu-item-row');
    const children = [];
    
    rows.forEach(row => {
        const label = row.querySelector('.sub-label').value;
        if (!label) return;
        
        children.push({
            label: label,
            description: row.querySelector('.sub-desc').value,
            action: row.querySelector('.sub-action').value,
            url: row.querySelector('.sub-url').value,
            enabled: true
        });
    });
    
    activeSubmenuInput.value = JSON.stringify(children);
    bootstrap.Modal.getInstance(document.getElementById('submenuModal')).hide();
    updateBotPreview();
}

function resetToDefault() {
    Swal.fire({
        title: 'Reset Menu?',
        text: "Semua menu yang Anda buat akan diganti dengan 5 menu standar aplikasi.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Reset Sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const container = document.getElementById('bot-menu-container');
            container.innerHTML = '';
            defaultBotMenu.forEach(item => addMenuRow(item));
        }
    });
}

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
    updateBotPreview();

    // Listen to changes on root menu items
    const menuContainer = document.getElementById('bot-menu-container');
    if (menuContainer) {
        menuContainer.addEventListener('input', (e) => {
            if (e.target.closest('.menu-item-row')) updateBotPreview();
        });
        menuContainer.addEventListener('change', (e) => {
            if (e.target.closest('.menu-item-row')) updateBotPreview();
        });
    }
});

function testApiKey(provider) {
    const keyInput = document.getElementById(`${provider}_api_key`);
    const key = keyInput ? keyInput.value : '';
    
    if (!key) {
        Swal.fire('Oopss!', 'Silakan masukkan API Key terlebih dahulu sebelum menguji.', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Mengecek Koneksi...',
        text: 'Sedang menghubungi server ' + provider.toUpperCase(),
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch('/api/settings/ai/test', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ provider: provider, api_key: key })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Berhasil!', `Koneksi ke ${provider.toUpperCase()} sukses! API Key siap digunakan.`, 'success');
        } else {
            Swal.fire('Gagal', data.message || 'API Key tidak valid.', 'error');
        }
    })
    .catch(e => {
        Swal.fire('Error', 'Gagal menghubungi server lokal.', 'error');
    });
}

function syncMemory() {
    Swal.fire({
        title: 'Sinkronkan Memori AI?',
        text: "Ini akan membersihkan cache instruksi lama dan memaksa AI untuk menggunakan data terbaru (seperti nama wilayah atau aturan baru).",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Sinkronkan!',
        cancelButtonText: 'Batal',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch("{{ route('kecamatan.settings.waha-n8n.sync-memory') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error(response.statusText);
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`Request failed: ${error}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Sinkronisasi Berhasil',
                text: result.value.message,
                footer: result.value.n8n_status === 'success' ? 
                    '<span class="text-success"><i class="fas fa-check-circle me-1"></i> n8n terintegrasi & memori disegarkan.</span>' : 
                    '<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> Cache lokal bersih, namun n8n tidak merespon (cek konfigurasi webhook).</span>'
            });
        }
    });
}
</script>
@endpush