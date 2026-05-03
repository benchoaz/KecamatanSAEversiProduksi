@extends('layouts.public')
@section('title', $layanan->nama_layanan . ' — Panduan Layanan')

@section('content')
@php
    $waRaw = preg_replace('/[^0-9]/', '', appProfile()->whatsapp_bot_number ?? '628123456789');
    $waLink = str_starts_with($waRaw,'0') ? '62'.substr($waRaw,1) : $waRaw;
@endphp

<div class="sn-wrapper">

    {{-- ═══════════════════════════════════════════════════
         HERO HEADER
    ═══════════════════════════════════════════════════ --}}
    <div class="sn-hero">
        <div class="sn-hero-inner">
            {{-- Back --}}
            <a href="{{ route('layanan') }}" class="sn-back-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Semua Layanan</span>
            </a>

            {{-- Icon + Title --}}
            <div class="sn-hero-title-row">
                <div class="sn-hero-icon" style="background:{{ str_replace('bg-','',str_replace('-50','',$layanan->warna_bg??'')) }}">
                    <i class="fas {{ $layanan->ikon ?? 'fa-file-alt' }}"></i>
                </div>
                <div>
                    <h1 class="sn-hero-h1">{{ $layanan->nama_layanan }}</h1>
                    @if($layanan->estimasi_waktu)
                    <span class="sn-hero-eta">
                        <i class="far fa-clock"></i> Estimasi {{ $layanan->estimasi_waktu }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Breadcrumb --}}
            <div class="sn-breadcrumb" id="snBreadcrumb">
                <span class="sn-bc-item active">Pilih Jenis</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         STEP PROGRESS BAR
    ═══════════════════════════════════════════════════ --}}
    <div class="sn-progress-bar" id="snProgressBar">
        <div class="sn-progress-fill" id="snProgressFill" style="width:0%"></div>
    </div>

    {{-- ═══════════════════════════════════════════════════
         MAIN CONTENT AREA
    ═══════════════════════════════════════════════════ --}}
    <div class="sn-main">
        <div class="sn-container">

            {{-- ─── PANEL: Node Selector (tampil saat belum di leaf) ─── --}}
            <div id="snNodePanel">
                <p class="sn-panel-hint" id="snPanelHint">Silakan pilih jenis layanan yang Anda butuhkan:</p>
                <div class="sn-node-grid" id="snNodeGrid">
                    @foreach($rootNodes as $node)
                    <button class="sn-node-card sn-root-node"
                            data-id="{{ $node->id }}"
                            data-name="{{ $node->name }}"
                            data-leaf="{{ $node->is_leaf ? 'true' : 'false' }}"
                            data-identity="{{ $node->show_identity_form ? 'true' : 'false' }}"
                            data-sop="{{ $node->requirement_text ?? '' }}">
                        <div class="sn-node-card-icon">
                            <i class="fas {{ $node->ikon ?? 'fa-folder' }}"></i>
                        </div>
                        <div class="sn-node-card-text">
                            <span class="sn-node-card-label">{{ $node->name }}</span>
                            @if($node->description)
                            <span class="sn-node-card-desc">{{ $node->description }}</span>
                            @endif
                        </div>
                        <div class="sn-node-card-arrow">
                            @if($node->is_leaf)
                                <span class="sn-leaf-badge">Ajukan</span>
                            @else
                                <i class="fas fa-chevron-right"></i>
                            @endif
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- ─── PANEL: Leaf Form (tampil saat node is_leaf=true) ─── --}}
            <div id="snLeafPanel" class="hidden">

                {{-- Expert SOP Box --}}
                <div id="snSopBox" class="sn-sop-box hidden">
                    <div class="sn-sop-label">
                        <i class="fas fa-info-circle"></i> Petunjuk & Persyaratan
                    </div>
                    <div id="snSopText" class="sn-sop-content"></div>
                </div>

                {{-- Requirements Summary --}}
                <div class="sn-req-summary" id="snReqSummary"></div>

                {{-- Form --}}
                <form id="snForm" class="sn-form" novalidate>
                    @csrf
                    <input type="hidden" name="node_id" id="snNodeId">
                    <input type="hidden" name="master_layanan_id" value="{{ $layanan->id }}">
                    <input type="hidden" name="jenis_layanan" id="snJenisLayanan" value="{{ $layanan->nama_layanan }}">

                    {{-- ── Bagian 1: Identitas (Dinamis: Anak atau Pemohon Utama) ── --}}
                    <div class="sn-form-section" id="snIdentitySection">
                        <div class="sn-form-section-label" id="snMainIdLabel">
                            <i class="fas fa-user-circle"></i> Data Pemohon
                        </div>
                        <div class="sn-form-grid">
                            <div class="sn-field col-span-2">
                                <label class="sn-label" id="snMainNameLabel">Nama Lengkap <span>*</span></label>
                                <input type="text" name="nama_pemohon" class="sn-input" placeholder="Sesuai KTP" required>
                            </div>
                            <div class="sn-field">
                                <label class="sn-label" id="snMainNikLabel">NIK (16 digit) <span>*</span></label>
                                <input type="tel" name="nik" class="sn-input" placeholder="35XXXXXXXXXXXXXX"
                                       minlength="16" maxlength="16" pattern="\d{16}" required>
                            </div>
                            <div class="sn-field">
                                <label class="sn-label">WhatsApp Aktif <span>*</span></label>
                                <div class="sn-input-prefix">
                                    <span>+62</span>
                                    <input type="tel" name="whatsapp" class="sn-input" placeholder="8123456789" required>
                                </div>
                            </div>
                            <div class="sn-field col-span-2">
                                <label class="sn-label">Desa Domisili <span>*</span></label>
                                <select name="desa_id" class="sn-input sn-select" required>
                                    <option value="">— Pilih Desa —</option>
                                    @foreach($desas as $d)
                                    <option value="{{ $d->id }}">{{ $d->nama_desa }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ── Bagian Tambahan: Data Pemohon (jika Identitas Utama adalah Anak) ── --}}
                    <div class="sn-form-section hidden" id="snApplicantSection">
                        <div class="sn-form-section-label">
                            <i class="fas fa-id-card-alt"></i> Data Orang Tua / Wali (Pemohon)
                        </div>
                        <p class="sn-panel-hint" style="margin-top:-10px; margin-bottom:15px; font-size:12px;">Silakan isi data diri Anda sebagai orang tua/wali yang mengajukan permohonan untuk anak tersebut.</p>
                        <div class="sn-form-grid">
                            <div class="sn-field">
                                <label class="sn-label">Nama Orang Tua / Wali <span>*</span></label>
                                <input type="text" name="applicant_name" id="snApplicantName" class="sn-input" placeholder="Nama Lengkap Anda">
                            </div>
                            <div class="sn-field">
                                <label class="sn-label">NIK Orang Tua / Wali <span>*</span></label>
                                <input type="tel" name="applicant_nik" id="snApplicantNik" class="sn-input" placeholder="NIK 16 Digit Anda" maxlength="16">
                            </div>
                        </div>
                    </div>

                    {{-- ── Bagian 2: Upload Berkas (dinamis per node) ── --}}
                    <div class="sn-form-section" id="snUploadSection">
                        <div class="sn-form-section-label">
                            <i class="fas fa-paperclip"></i> Upload Berkas Pendukung
                        </div>
                        <div id="snUploadSlots"></div>
                    </div>

                    {{-- ── Bagian 3: Catatan & Konfirmasi ── --}}
                    <div class="sn-form-section">
                        <div class="sn-form-section-label">
                            <i class="fas fa-comment-dots"></i> Catatan Tambahan
                        </div>
                        <textarea name="uraian" class="sn-input sn-textarea"
                                  placeholder="Opsional — tuliskan detail atau permintaan khusus Anda..."></textarea>
                    </div>

                    {{-- Pernyataan --}}
                    <div class="sn-form-section">
                        <div class="sn-form-section-label">
                            <i class="fas fa-file-signature"></i> Konfirmasi & Pernyataan
                        </div>
                        <div class="sn-agreement-wrapper">
                            <label class="sn-agreement-label">
                                <input type="checkbox" name="is_agreed" required class="sn-checkbox">
                                <div class="sn-agreement-text">
                                    Saya menyatakan bahwa seluruh data dan dokumen yang saya lampirkan adalah <strong>benar, sah, dan sesuai dengan aslinya</strong>.
                                    <button type="button" class="sn-link-detail" onclick="openStatementDetail()">
                                        Baca Detail Pernyataan <i class="fas fa-external-link-alt"></i>
                                    </button>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="sn-submit-btn" id="snSubmitBtn">
                        <i class="fas fa-paper-plane"></i>
                        <span>Kirim Permohonan</span>
                    </button>
                </form>
            </div>

        </div>{{-- /sn-container --}}
    </div>{{-- /sn-main --}}

    {{-- ─── Floating Help Button (Mobile) ─── --}}
    <a href="https://wa.me/{{ $waLink }}" target="_blank" class="sn-wa-float">
        <i class="fab fa-whatsapp"></i>
        <span>Butuh Bantuan?</span>
    </a>

</div>{{-- /sn-wrapper --}}

{{-- ═══════════════════════════════════════════════════
     STATEMENT DETAIL MODAL
═══════════════════════════════════════════════════ --}}
<div class="sn-modal-overlay" id="snStatementModal">
    <div class="sn-modal statement">
        <div class="sn-modal-icon info">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h2 class="sn-modal-h2">Pakta Integritas Pemohon</h2>
        <div class="sn-statement-scroll">
            <div class="sn-statement-item">
                <div class="sn-statement-num">1</div>
                <p>Saya menjamin bahwa setiap informasi tulisan dan dokumen elektronik (scan/foto) yang saya unggah adalah <strong>asli</strong> dan tidak dimanipulasi.</p>
            </div>
            <div class="sn-statement-item">
                <div class="sn-statement-num">2</div>
                <p>Saya memahami bahwa pemalsuan dokumen atau pemberian data palsu dapat berakibat pada pembatalan pengajuan dan dapat diproses sesuai hukum yang berlaku.</p>
            </div>
            <div class="sn-statement-item">
                <div class="sn-statement-num">3</div>
                <p>Saya memberikan izin kepada petugas untuk memverifikasi data saya dengan sistem kependudukan atau instansi terkait lainnya.</p>
            </div>
            <div class="sn-statement-item">
                <div class="sn-statement-num">4</div>
                <p>Saya bertanggung jawab penuh atas segala akibat hukum yang timbul jika di kemudian hari ditemukan ketidaksesuaian data yang saya kirimkan.</p>
            </div>
        </div>
        <button type="button" class="sn-modal-btn primary" onclick="closeStatementDetail()">Saya Mengerti</button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     SUCCESS MODAL
═══════════════════════════════════════════════════ --}}
<div class="sn-modal-overlay" id="snSuccessModal">
    <div class="sn-modal">
        <div class="sn-modal-icon success">
            <i class="fas fa-check"></i>
        </div>
        <h2 class="sn-modal-h2">Permohonan Terkirim!</h2>
        <p class="sn-modal-p">Simpan PIN berikut untuk melacak status berkas Anda melalui halaman Lacak Status.</p>
        <div class="sn-pin-box">
            <div class="sn-pin-label">PIN LACAK BERKAS</div>
            <div class="sn-pin-code" id="snPinDisplay">——————</div>
            <button class="sn-pin-copy" id="snPinCopy" onclick="copyPin()">
                <i class="fas fa-copy"></i> Salin PIN
            </button>
        </div>
        <div id="quickFeedbackSection" style="background:#fffbeb; border:1px solid #fef3c7; border-radius:16px; padding:16px; margin:20px 0;">
            <p style="font-size:10px; font-weight:800; color:#b45309; text-transform:uppercase; margin-bottom:10px;">Bagaimana pengalaman pengajuan Anda?</p>
            <div style="display:flex; justify-content:center; gap:8px; margin-bottom:12px;">
                @for($i=1; $i<=5; $i++)
                <button type="button" onclick="setQuickRating({{ $i }})" class="quick-star" data-val="{{ $i }}" style="width:36px; height:36px; border-radius:10px; border:none; background:#fff; color:#cbd5e1; box-shadow:0 1px 2px rgba(0,0,0,0.05); cursor:pointer;">
                    <i class="fas fa-star"></i>
                </button>
                @endfor
            </div>
            <div id="feedbackCommentSection">
                <textarea id="quick_feedback_comment" placeholder="Ada saran atau masukan? (Opsional)" 
                    style="width:100%; border:1px solid #fde68a; border-radius:12px; padding:10px; font-size:12px; margin-bottom:10px; background:rgba(255,255,255,0.5); font-family:inherit; outline:none;"></textarea>
                <button type="button" id="btnSendQuickFeedback" onclick="submitQuickFeedback()" style="width:100%; padding:8px; background:#f59e0b; color:#fff; border:none; border-radius:10px; font-size:11px; font-weight:800; text-transform:uppercase; cursor:pointer;">
                    Kirim Penilaian <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div id="quickFeedbackSuccess" style="display:none; text-align:center;">
                <i class="fas fa-check-circle" style="color:#10b981; font-size:20px; margin-bottom:4px;"></i>
                <p style="font-size:10px; font-weight:800; color:#059669; text-transform:uppercase; margin:0;">Terima kasih!</p>
            </div>
        </div>

        <a href="{{ route('public.tracking') }}" id="snRedirectBtn" class="sn-modal-btn primary">
            Cek Status Sekarang <i class="fas fa-arrow-right"></i>
        </a>
        <a href="{{ route('layanan') }}" class="sn-modal-btn ghost">Kembali ke Beranda</a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     STYLES
═══════════════════════════════════════════════════ --}}
@push('styles')
<style>
/* ── Root & Wrapper ───────────────────────────────── */
:root {
    --primary: #0f766e;
    --primary-light: #ccfbf1;
    --primary-dark: #064e3b;
    --accent: #6366f1;
    --accent-light: #eef2ff;
    --surface: #ffffff;
    --surface-2: #f8fafc;
    --border: #e2e8f0;
    --text-main: #0f172a;
    --text-muted: #64748b;
    --text-light: #94a3b8;
    --danger: #ef4444;
    --radius-sm: 10px;
    --radius-md: 16px;
    --radius-lg: 24px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
    --shadow-md: 0 4px 16px rgba(0,0,0,.08);
    --shadow-lg: 0 10px 40px rgba(0,0,0,.12);
}

.hidden { display: none !important; }

* { box-sizing: border-box; }

.sn-wrapper {
    min-height: 100vh;
    background: var(--surface-2);
    font-family: 'Poppins', system-ui, sans-serif;
    padding-bottom: 120px;
}

/* ── Hero ─────────────────────────────────────────── */
.sn-hero {
    background: linear-gradient(135deg, #0f766e 0%, #0e7490 60%, #1e40af 100%);
    padding: 80px 0 48px;
    position: relative;
    overflow: hidden;
}
.sn-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='24'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.sn-hero-inner {
    max-width: 720px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
}
.sn-back-btn {
    display: inline-flex; align-items: center; gap: 8px;
    color: rgba(255,255,255,.75);
    text-decoration: none;
    font-size: 13px; font-weight: 600;
    margin-bottom: 24px;
    transition: color .2s;
}
.sn-back-btn:hover { color: #fff; }

.sn-hero-title-row {
    display: flex; align-items: center; gap: 16px;
    margin-bottom: 20px;
}
.sn-hero-icon {
    width: 56px; height: 56px; border-radius: 16px;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
    flex-shrink: 0;
    backdrop-filter: blur(4px);
    border: 1.5px solid rgba(255,255,255,.25);
}
.sn-hero-h1 {
    font-size: clamp(1.25rem,4vw,1.75rem);
    font-weight: 800; color: #fff; margin: 0;
    line-height: 1.25;
}
.sn-hero-eta {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600;
    color: rgba(255,255,255,.7);
    margin-top: 4px;
}

/* Breadcrumb */
.sn-breadcrumb {
    display: flex; align-items: center; flex-wrap: wrap;
    gap: 4px;
    font-size: 12px; font-weight: 700;
    color: rgba(255,255,255,.5);
}
.sn-bc-item { transition: color .2s; }
.sn-bc-item.active { color: rgba(255,255,255,.9); }
.sn-bc-sep { opacity: .4; }
.sn-bc-item:not(.active) { cursor: pointer; }
.sn-bc-item:not(.active):hover { color: rgba(255,255,255,.85); }

/* ── Progress Bar ─────────────────────────────────── */
.sn-progress-bar {
    height: 4px;
    background: rgba(99,102,241,.15);
    position: sticky; top: 0; z-index: 100;
}
.sn-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--accent));
    transition: width .5s cubic-bezier(.4,0,.2,1);
    border-radius: 0 4px 4px 0;
}

/* ── Main ─────────────────────────────────────────── */
.sn-main { padding: 28px 16px; }
.sn-container { max-width: 720px; margin: 0 auto; }

.sn-panel-hint {
    font-size: 14px; color: var(--text-muted); font-weight: 500;
    margin-bottom: 16px;
}

/* ── Node Grid ────────────────────────────────────── */
.sn-node-grid {
    display: flex; flex-direction: column; gap: 10px;
}
.sn-node-card {
    width: 100%; background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 16px 18px;
    display: flex; align-items: center; gap: 14px;
    cursor: pointer; text-align: left;
    transition: all .2s ease;
    box-shadow: var(--shadow-sm);
    -webkit-tap-highlight-color: transparent;
}
.sn-node-card:hover, .sn-node-card:active {
    border-color: var(--primary);
    background: linear-gradient(135deg, #f0fdfa, #eff6ff);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.sn-node-card-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: var(--primary-light);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
    transition: all .2s;
}
.sn-node-card:hover .sn-node-card-icon {
    background: var(--primary);
    color: #fff;
}
.sn-node-card-text {
    flex: 1; min-width: 0;
}
.sn-node-card-label {
    display: block;
    font-size: 15px; font-weight: 700; color: var(--text-main);
    line-height: 1.3;
}
.sn-node-card-desc {
    display: block;
    font-size: 12px; color: var(--text-muted);
    margin-top: 3px; font-weight: 500;
}
.sn-node-card-arrow {
    color: var(--text-light);
    font-size: 13px; flex-shrink: 0;
}
.sn-leaf-badge {
    display: inline-block;
    background: var(--accent-light);
    color: var(--accent);
    font-size: 11px; font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid #c7d2fe;
    white-space: nowrap;
}

/* ── Requirement Summary ──────────────────────────── */
.sn-req-summary {
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 18px 20px;
    margin-bottom: 20px;
}
.sn-req-summary h3 {
    font-size: 13px; font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase; letter-spacing: .5px;
    margin: 0 0 12px;
}
.sn-req-list { display: flex; flex-direction: column; gap: 8px; margin: 0; padding: 0; list-style: none; }
.sn-req-list li {
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 13px; color: var(--text-main); font-weight: 500;
}
.sn-req-list li .sn-req-dot {
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--primary-light);
    color: var(--primary);
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; flex-shrink: 0; margin-top: 1px;
}
.sn-req-list li .sn-req-dot.opt { background: #fef9c3; color: #ca8a04; }

/* ── Form ─────────────────────────────────────────── */
.sn-form { display: flex; flex-direction: column; gap: 20px; }

.sn-form-section {
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 20px;
}
.sn-form-section-label {
    display: flex; align-items: center; gap: 8px;
    font-size: 12px; font-weight: 800;
    text-transform: uppercase; letter-spacing: .6px;
    color: var(--text-muted);
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}
.sn-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.sn-field { display: flex; flex-direction: column; gap: 6px; }
.sn-field.col-span-2 { grid-column: span 2; }

.sn-label {
    font-size: 12px; font-weight: 700; color: var(--text-main);
}
.sn-label span { color: var(--danger); }

.sn-input {
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 11px 14px;
    font-size: 14px; font-weight: 500;
    color: var(--text-main);
    background: var(--surface-2);
    outline: none;
    width: 100%;
    transition: all .2s;
    font-family: inherit;
    -webkit-appearance: none;
}
.sn-input:focus {
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(15,118,110,.1);
}
.sn-select { cursor: pointer; }
.sn-textarea { min-height: 90px; resize: vertical; }

.sn-input-prefix {
    display: flex; align-items: stretch;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface-2);
    overflow: hidden;
    transition: all .2s;
}
.sn-input-prefix:focus-within {
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(15,118,110,.1);
}
.sn-input-prefix span {
    display: flex; align-items: center;
    padding: 0 12px;
    font-size: 13px; font-weight: 700;
    color: var(--text-muted);
    background: var(--border);
    border-right: 1.5px solid var(--border);
    white-space: nowrap;
}
.sn-input-prefix .sn-input {
    border: none; border-radius: 0;
    background: transparent; box-shadow: none;
    flex: 1;
}

/* ── Upload Slots ─────────────────────────────────── */
.sn-upload-slot {
    border: 1.5px dashed var(--border);
    border-radius: var(--radius-sm);
    padding: 14px 16px;
    margin-bottom: 10px;
    background: var(--surface-2);
    transition: border-color .2s;
}
.sn-upload-slot.has-file { border-color: var(--primary); background: #f0fdfa; }
.sn-upload-slot-header {
    display: flex; align-items: flex-start; gap: 10px;
    margin-bottom: 10px;
}
.sn-upload-slot-icon {
    width: 36px; height: 36px;
    border-radius: 10px; background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
    border: 1.5px solid var(--border);
}
.sn-upload-slot-info { flex: 1; }
.sn-upload-slot-label {
    font-size: 13px; font-weight: 700; color: var(--text-main);
    display: flex; align-items: center; gap: 6px;
}
.sn-required-tag {
    font-size: 10px; font-weight: 700; color: var(--danger);
    background: #fee2e2; padding: 1px 7px; border-radius: 20px;
}
.sn-optional-tag {
    font-size: 10px; font-weight: 700; color: #92400e;
    background: #fef3c7; padding: 1px 7px; border-radius: 20px;
}
.sn-upload-slot-hint {
    font-size: 11px; color: var(--text-muted);
    margin-top: 2px; font-weight: 500;
}

/* Custom File Input Button */
.sn-file-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px;
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: 8px;
    font-size: 12px; font-weight: 700; color: var(--primary);
    cursor: pointer; position: relative; overflow: hidden;
    transition: all .2s;
}
.sn-file-btn:hover { background: var(--primary-light); border-color: var(--primary); }
.sn-file-btn input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer;
}
.sn-file-chosen {
    font-size: 12px; color: var(--primary); font-weight: 600;
    margin-top: 6px; display: none;
}

/* ── Agreement ────────────────────────────────────── */
.sn-agreement {
    display: flex; align-items: flex-start; gap: 12px;
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    padding: 16px 18px;
    cursor: pointer;
    transition: border-color .2s;
}
.sn-agreement:hover { border-color: var(--primary); }
.sn-agreement input[type=checkbox] {
    width: 18px; height: 18px; flex-shrink: 0;
    margin-top: 1px; cursor: pointer; accent-color: var(--primary);
}
.sn-agreement span {
    font-size: 12px; line-height: 1.6; color: var(--text-muted); font-weight: 500;
}

/* ── Submit Button ────────────────────────────────── */
.sn-submit-btn {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    width: 100%; padding: 16px;
    background: linear-gradient(135deg, var(--primary), #0e7490);
    color: #fff; border: none; border-radius: var(--radius-md);
    font-size: 16px; font-weight: 800;
    cursor: pointer; font-family: inherit;
    box-shadow: 0 8px 24px rgba(15,118,110,.3);
    transition: all .2s;
    -webkit-tap-highlight-color: transparent;
}
.sn-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(15,118,110,.4); }
.sn-submit-btn:active { transform: translateY(0); }
.sn-submit-btn:disabled { opacity: .7; cursor: not-allowed; transform: none; }

/* ── Agreement Section ────────────────────────────── */
.sn-agreement-wrapper {
    padding: 12px 0;
}
.sn-agreement-label {
    display: flex;
    gap: 14px;
    cursor: pointer;
    background: var(--surface-2);
    padding: 16px;
    border-radius: 12px;
    border: 1.5px solid var(--border);
    transition: all .2s;
}
.sn-agreement-label:hover {
    border-color: var(--primary);
    background: #fff;
}
.sn-checkbox {
    width: 20px;
    height: 20px;
    margin-top: 2px;
    accent-color: var(--primary);
    cursor: pointer;
}
.sn-agreement-text {
    font-size: 13px;
    line-height: 1.6;
    color: var(--text-main);
    font-weight: 500;
}
.sn-link-detail {
    display: block;
    margin-top: 8px;
    color: var(--accent);
    font-weight: 700;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .5px;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    text-align: left;
}
.sn-link-detail:hover {
    text-decoration: underline;
    color: var(--primary);
}

/* ── Statement Modal Detail ───────────────────────── */
.sn-modal.statement {
    max-width: 500px;
}
.sn-modal-icon.info {
    background: var(--accent-light);
    color: var(--accent);
}
.sn-statement-scroll {
    text-align: left;
    margin-bottom: 24px;
    max-height: 300px;
    overflow-y: auto;
    padding: 0 4px;
}
.sn-statement-item {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
}
.sn-statement-num {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--primary-light);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 800;
    flex-shrink: 0;
}
.sn-statement-item p {
    font-size: 13px;
    color: var(--text-muted);
    line-height: 1.5;
    margin: 0;
}

/* ── Floating WA Button ───────────────────────────── */
.sn-wa-float {
    position: fixed; bottom: 24px; right: 20px; z-index: 50;
    display: flex; align-items: center; gap: 8px;
    background: #25d366; color: #fff;
    padding: 12px 18px 12px 14px;
    border-radius: 50px;
    text-decoration: none;
    font-size: 13px; font-weight: 700;
    box-shadow: 0 6px 20px rgba(37,211,102,.4);
    transition: all .25s;
}
.sn-wa-float:hover { transform: scale(1.05); color: #fff; }
.sn-wa-float i { font-size: 20px; }
@media(max-width:480px) { .sn-wa-float span { display: none; } .sn-wa-float { padding: 14px; border-radius: 50%; } }

/* ── Modal ────────────────────────────────────────── */
.sn-modal-overlay {
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(15,23,42,.6);
    backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
    opacity: 0; pointer-events: none;
    transition: opacity .3s;
}
.sn-modal-overlay.show { opacity: 1; pointer-events: auto; }
.sn-modal {
    background: #fff; border-radius: var(--radius-lg);
    padding: 40px 32px;
    max-width: 400px; width: 100%;
    text-align: center;
    box-shadow: var(--shadow-lg);
    transform: scale(.9);
    transition: transform .3s cubic-bezier(.34,1.56,.64,1);
}
.sn-modal-overlay.show .sn-modal { transform: scale(1); }
.sn-modal-icon {
    width: 72px; height: 72px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; margin: 0 auto 20px;
}
.sn-modal-icon.success { background: var(--primary-light); color: var(--primary); }
.sn-modal-h2 { font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0 0 8px; }
.sn-modal-p  { font-size: 14px; color: var(--text-muted); margin: 0 0 24px; line-height: 1.6; }

.sn-pin-box {
    background: var(--surface-2);
    border-radius: var(--radius-md);
    padding: 20px;
    margin-bottom: 24px;
}
.sn-pin-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--text-light); margin-bottom: 8px; }
.sn-pin-code  { font-size: 36px; font-weight: 900; letter-spacing: .4em; color: var(--primary); padding-left: .4em; }
.sn-pin-copy  {
    display: inline-flex; align-items: center; gap: 6px;
    margin-top: 12px; padding: 6px 14px;
    background: #fff; border: 1.5px solid var(--border);
    border-radius: 8px; font-size: 12px; font-weight: 700; color: var(--primary);
    cursor: pointer; font-family: inherit; transition: all .15s;
}
.sn-pin-copy:hover { background: var(--primary-light); border-color: var(--primary); }

.sn-modal-btn {
    display: block; width: 100%;
    padding: 14px;
    border-radius: var(--radius-md);
    font-size: 15px; font-weight: 800;
    text-decoration: none; margin-bottom: 10px;
    transition: all .2s;
}
.sn-modal-btn.primary { background: var(--text-main); color: #fff; }
.sn-modal-btn.primary:hover { background: #1e293b; color: #fff; }
.sn-modal-btn.ghost { color: var(--text-muted); font-size: 13px; background: none; }

/* ── Animations ───────────────────────────────────── */
@keyframes snSlideIn {
    from { opacity:0; transform:translateY(14px); }
    to   { opacity:1; transform:translateY(0); }
}
.sn-anim { animation: snSlideIn .35s cubic-bezier(.4,0,.2,1) both; }

/* ── Responsive ───────────────────────────────────── */
@media(max-width:640px) {
    .sn-hero { padding: 60px 0 32px; }
    .sn-hero-title-row { flex-direction: column; text-align: center; gap: 12px; }
    .sn-hero-icon { margin: 0 auto; }
    .sn-breadcrumb { justify-content: center; overflow-x: auto; white-space: nowrap; padding-bottom: 8px; }
    
    .sn-main { padding: 20px 12px; }
    .sn-node-card { padding: 14px; gap: 12px; }
    .sn-node-card-icon { width: 36px; height: 36px; font-size: 16px; }
    .sn-node-card-label { font-size: 14px; }
    
    .sn-form-grid { grid-template-columns: 1fr; }
    .sn-field.col-span-2 { grid-column: span 1; }
    
    .sn-modal { padding: 32px 20px; border-radius: 20px; }
    .sn-pin-code { font-size: 26px; }
}
/* SOP Box */
.sn-sop-box {
    background: #fdf2f2;
    border: 1px solid #fee2e2;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}
.sn-sop-label {
    font-size: 13px;
    font-weight: 700;
    color: #991b1b;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.sn-sop-content {
    font-size: 14px;
    color: #b91c1c;
    line-height: 1.6;
    white-space: pre-wrap;
}
</style>
@endpush

{{-- ═══════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════ --}}
@push('scripts')
<script>
// ── State ──────────────────────────────────────────────────
const LAYANAN_ID  = {{ $layanan->id }};
const TOTAL_STEPS = 5; // estimasi kedalaman max
let history = []; // [{id, name}] stack navigasi
let currentNodeId = null;

// ── Render root nodes saat init ────────────────────────────
const rootNodes = @json($rootNodes);
const directSubmission = @json($directSubmission ?? false);
const directRequirements = @json($requirements ?? []);
const masterRequirements = @json($masterRequirements ?? []); // dari attachment_requirements admin

if (directSubmission) {
    initDirectSubmission();
} else {
    updateProgress(0);
}

// ── Event delegation untuk node clicks ─────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.sn-node-card');
    if (!btn) return;
    
    const id = btn.dataset.id;
    const name = btn.dataset.name;
    const isLeaf = btn.dataset.leaf === 'true' || btn.dataset.leaf === '1';
    const showIdentity = btn.dataset.identity !== 'false';
    const sop = btn.dataset.sop || '';
    
    if (id && name) {
        selectNode(parseInt(id), name, isLeaf, showIdentity, sop);
    }
});

// Helper untuk escape data node agar aman di atribut HTML onclick
function escapeNodeData(str) {
    if (!str) return "";
    return encodeURIComponent(str).replace(/'/g, "%27");
}

function unescapeNodeData(str) {
    if (!str) return "";
    try {
        return decodeURIComponent(str);
    } catch(e) {
        return str;
    }
}

function initDirectSubmission() {
    currentNodeId = null; // node_id is null for direct
    const layananName = @json($layanan->nama_layanan);
    document.getElementById('snJenisLayanan').value = layananName;
    
    // Render requirements from array
    renderRequirements(directRequirements);
    
    updateProgress(75);
    showLeafPanel();
}

function renderRequirements(reqs) {
    // Requirement summary card
    const summaryEl = document.getElementById('snReqSummary');
    summaryEl.innerHTML = `
        <h3>Berkas yang Diperlukan</h3>
        <ul class="sn-req-list">
            ${reqs.map(r => `
                <li>
                    <div class="sn-req-dot ${!r.is_required ? 'opt' : ''}">
                        <i class="fas ${r.type==='file_upload' ? 'fa-paperclip' : r.type==='checkbox' ? 'fa-check' : 'fa-info'}"></i>
                    </div>
                    <div>
                        <div style="font-weight:700">${r.label}
                            ${r.is_required
                                ? '<span class="sn-required-tag">WAJIB</span>'
                                : '<span class="sn-optional-tag">Opsional</span>'}
                        </div>
                        ${r.description ? `<div style="font-size:11px;color:#64748b;margin-top:2px">${r.description}</div>` : ''}
                    </div>
                </li>
            `).join('')}
        </ul>
    `;

    // Upload slots
    const slotsEl = document.getElementById('snUploadSlots');
    const fileReqs = reqs.filter(r => r.type === 'file_upload');

    if (fileReqs.length === 0) {
        slotsEl.innerHTML = `<p style="font-size:13px;color:#64748b">Tidak ada berkas yang perlu diunggah untuk layanan ini.</p>`;
    } else {
        slotsEl.innerHTML = fileReqs.map((r, i) => `
            <div class="sn-upload-slot" id="slot_${i}">
                <div class="sn-upload-slot-header">
                    <div class="sn-upload-slot-icon">
                        <i class="fas fa-file-upload" style="color:#0f766e"></i>
                    </div>
                    <div class="sn-upload-slot-info">
                        <div class="sn-upload-slot-label">
                            ${r.label}
                            ${r.is_required
                                ? '<span class="sn-required-tag">WAJIB</span>'
                                : '<span class="sn-optional-tag">Opsional</span>'}
                        </div>
                        <div class="sn-upload-slot-hint">.${(r.accepted_types||'jpg,png,pdf').replace(/,/g,', .')} — Maks ${r.max_size_mb||5}MB</div>
                    </div>
                </div>
                <label class="sn-file-btn">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Pilih File</span>
                    <input type="file"
                           name="attachments[]"
                           accept=".${(r.accepted_types||'jpg,png,pdf').split(',').join(',.')}"
                           ${r.is_required ? 'required' : ''}
                           data-req-id="${r.id}"
                           data-label="${r.label}"
                           onchange="handleFile(this, ${i})">
                </label>
                <input type="hidden" name="attachment_labels[]" value="${r.label}">
                <input type="hidden" name="attachment_req_ids[]" value="${r.id}">
                <div class="sn-file-chosen" id="chosen_${i}"></div>
            </div>
        `).join('');
    }
}

// ── Pilih node ────────────────────────────────────────────
async function selectNode(nodeId, encodedNodeName, isLeaf, showIdentity = true, encodedSopText = '') {
    const nodeName = unescapeNodeData(encodedNodeName);
    const sopText = unescapeNodeData(encodedSopText);

    history.push({ id: nodeId, name: nodeName });
    updateBreadcrumb();

    if (isLeaf) {
        currentNodeId = nodeId;
        await loadLeafForm(nodeId, nodeName, showIdentity, sopText);
    } else {
        await loadChildren(nodeId);
    }
}

// ── Load children via AJAX ────────────────────────────────
async function loadChildren(nodeId) {
    showLoading();
    const res  = await fetch(`/api/public/layanan/nodes/${nodeId}/children`);
    const data = await res.json();

    updateProgress(history.length / TOTAL_STEPS * 60);

    const grid = document.getElementById('snNodeGrid');
    grid.innerHTML = data.children.map(n => `
        <button class="sn-node-card sn-anim sn-dynamic-node"
                data-id="${n.id}"
                data-name="${n.name}"
                data-leaf="${n.is_leaf}"
                data-identity="${n.show_identity_form}"
                data-sop="${n.requirement_text || ''}">
            <div class="sn-node-card-icon">
                <i class="fas ${n.ikon || 'fa-folder'}"></i>
            </div>
            <div class="sn-node-card-text">
                <span class="sn-node-card-label">${n.name}</span>
                ${n.description ? `<span class="sn-node-card-desc">${n.description}</span>` : ''}
            </div>
            <div class="sn-node-card-arrow">
                ${n.is_leaf
                    ? '<span class="sn-leaf-badge">Ajukan</span>'
                    : '<i class="fas fa-chevron-right"></i>'}
            </div>
        </button>
    `).join('');

    document.getElementById('snPanelHint').textContent =
        'Pilih jenis yang sesuai:';
    showNodePanel();
    hideLoading();
}

// ── Load leaf form ────────────────────────────────────────
async function loadLeafForm(nodeId, nodeName, showIdentity = true, sopText = '') {
    showLoading();
    const res  = await fetch(`/api/public/layanan/nodes/${nodeId}/requirements`);
    const data = await res.json();
    let reqs = data.requirements || [];

    // Gabungkan dengan masterRequirements (dari attachment_requirements admin)
    // Pastikan tidak duplikat berdasarkan label
    if (masterRequirements.length > 0) {
        const existingLabels = reqs.map(r => r.label.toLowerCase());
        const additionalReqs = masterRequirements.filter(
            r => !existingLabels.includes(r.label.toLowerCase())
        );
        reqs = [...reqs, ...additionalReqs];
    }

    document.getElementById('snNodeId').value = nodeId;
    document.getElementById('snJenisLayanan').value = nodeName;

    // Toggle SOP Box
    const sopBox = document.getElementById('snSopBox');
    const sopEl  = document.getElementById('snSopText');
    if (sopText) {
        sopBox.classList.remove('hidden');
        sopEl.textContent = sopText;
    } else {
        sopBox.classList.add('hidden');
    }

    // Dynamic Labels for Anak
    const isAnak = (nodeName || "").toLowerCase().includes('anak') || (nodeName || "").toLowerCase().includes('lahir');
    const idSectionEl = document.getElementById('snIdentitySection');
    const applicantSectionEl = document.getElementById('snApplicantSection');
    
    if (idSectionEl) {
        const idLabel = document.getElementById('snMainIdLabel');
        const nameLabel = document.getElementById('snMainNameLabel');
        const nikLabel = document.getElementById('snMainNikLabel');
        const nameInput = document.querySelector('input[name="nama_pemohon"]');
        
        const applicantNameInput = document.getElementById('snApplicantName');
        const applicantNikInput = document.getElementById('snApplicantNik');
        
        if (isAnak) {
            // Utama adalah Anak
            if (idLabel) idLabel.innerHTML = '<i class="fas fa-child"></i> Data Anak (Subjek Layanan)';
            if (nameLabel) nameLabel.innerHTML = 'Nama Lengkap Anak <span>*</span>';
            if (nameInput) nameInput.placeholder = 'Nama Lengkap Anak';
            if (nikLabel) nikLabel.innerHTML = 'NIK Anak (jika ada) / NIK Kepala Keluarga <span>*</span>';
            
            // Munculkan bagian Pemohon (Orang Tua)
            if (applicantSectionEl) {
                applicantSectionEl.classList.remove('hidden');
                if (applicantNameInput) applicantNameInput.setAttribute('required', 'required');
                if (applicantNikInput) applicantNikInput.setAttribute('required', 'required');
            }
        } else {
            // Utama adalah Pemohon
            if (idLabel) idLabel.innerHTML = '<i class="fas fa-user-circle"></i> Data Pemohon';
            if (nameLabel) nameLabel.innerHTML = 'Nama Lengkap <span>*</span>';
            if (nameInput) nameInput.placeholder = 'Sesuai KTP';
            if (nikLabel) nikLabel.innerHTML = 'NIK (16 digit) <span>*</span>';
            
            // Sembunyikan bagian Pemohon tambahan
            if (applicantSectionEl) {
                applicantSectionEl.classList.add('hidden');
                if (applicantNameInput) applicantNameInput.removeAttribute('required');
                if (applicantNikInput) applicantNikInput.removeAttribute('required');
            }
        }
    }

    // Toggle Identity Form
    const idSection = document.getElementById('snIdentitySection');
    const idInputs  = idSection?.querySelectorAll('input, select');
    if (showIdentity) {
        idSection?.classList.remove('hidden');
        idInputs?.forEach(input => input.setAttribute('required', ''));
    } else {
        idSection?.classList.add('hidden');
        idInputs?.forEach(input => input.removeAttribute('required'));
    }

    // Render requirements using shared function
    renderRequirements(reqs);

    updateProgress(72);
    showLeafPanel();
    hideLoading();
}

function handleFile(input, idx) {
    const slot    = document.getElementById(`slot_${idx}`);
    const chosen  = document.getElementById(`chosen_${idx}`);
    const file    = input.files[0];
    if (!file) return;
    slot.classList.add('has-file');
    chosen.style.display = 'block';
    chosen.innerHTML = `<i class="fas fa-check-circle" style="color:#0f766e"></i> ${file.name} (${(file.size/1024/1024).toFixed(2)} MB)`;
}

// ── Breadcrumb ────────────────────────────────────────────
function updateBreadcrumb() {
    const el = document.getElementById('snBreadcrumb');
    const items = [{ id: null, name: 'Pilih Jenis' }, ...history];
    el.innerHTML = items.map((item, i) => {
        const isLast = i === items.length - 1;
        return `
            ${i > 0 ? '<span class="sn-bc-sep">›</span>' : ''}
            <span class="sn-bc-item ${isLast ? 'active' : ''}"
                  ${!isLast ? `onclick="goBack(${i})"` : ''}>${item.name}</span>
        `;
    }).join('');
}

function goBack(toIndex) {
    history = history.slice(0, toIndex);
    updateBreadcrumb();

    if (toIndex === 0) {
        // Kembali ke root
        const grid = document.getElementById('snNodeGrid');
        grid.innerHTML = rootNodes.map(n => `
            <button class="sn-node-card sn-anim sn-dynamic-node"
                    data-id="${n.id}"
                    data-name="${n.name}"
                    data-leaf="${n.is_leaf}"
                    data-identity="${n.show_identity_form}"
                    data-sop="${n.requirement_text || ''}">
                <div class="sn-node-card-icon"><i class="fas ${n.ikon}"></i></div>
                <div class="sn-node-card-text">
                    <span class="sn-node-card-label">${n.name}</span>
                    ${n.description ? `<span class="sn-node-card-desc">${n.description}</span>` : ''}
                </div>
                <div class="sn-node-card-arrow">
                    ${n.is_leaf ? '<span class="sn-leaf-badge">Ajukan</span>' : '<i class="fas fa-chevron-right"></i>'}
                </div>
            </button>
        `).join('');
        showNodePanel(); updateProgress(0);
    } else {
        const parentNode = history[toIndex - 1];
        loadChildren(parentNode.id);
    }
}

// ── Panel Toggle ──────────────────────────────────────────
function showNodePanel() {
    document.getElementById('snNodePanel').classList.remove('hidden');
    document.getElementById('snLeafPanel').classList.add('hidden');
}
function showLeafPanel() {
    document.getElementById('snNodePanel').classList.add('hidden');
    document.getElementById('snLeafPanel').classList.remove('hidden');
    document.getElementById('snLeafPanel').classList.add('sn-anim');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function updateProgress(pct) {
    document.getElementById('snProgressFill').style.width = pct + '%';
}

// ── Loading ───────────────────────────────────────────────
function showLoading() {
    document.getElementById('snNodeGrid').innerHTML = `
        <div style="text-align:center;padding:40px">
            <div style="display:inline-block;width:36px;height:36px;border:3px solid #e2e8f0;border-top-color:#0f766e;border-radius:50%;animation:spin .8s linear infinite"></div>
        </div>`;
}
function hideLoading() {}

// ── Form Submit ───────────────────────────────────────────
document.getElementById('snForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('snSubmitBtn');

    // ── Validasi frontend sebelum kirim ─────────────────────
    // 1. Cek field teks/select yang wajib diisi
    const identitySection = document.getElementById('snIdentitySection');
    if (identitySection && !identitySection.classList.contains('hidden')) {
        const requiredInputs = identitySection.querySelectorAll('input[required], select[required]');
        for (const input of requiredInputs) {
            if (!input.value || !input.value.trim()) {
                const label = input.closest('.sn-field-group')?.querySelector('label')?.textContent?.trim()
                           || input.placeholder
                           || input.name;
                alert(`⚠️ Field "${label}" wajib diisi!`);
                input.focus();
                return;
            }
        }
    }

    // 2. Cek field agreement (checkbox)
    const agreeCheck = this.querySelector('input[name="is_agreed"]');
    if (agreeCheck && !agreeCheck.checked) {
        alert('⚠️ Anda harus menyetujui pernyataan sebelum mengirim permohonan.');
        agreeCheck.focus();
        return;
    }

    // 3. Cek file upload yang wajib
    const requiredFileInputs = document.querySelectorAll('#snUploadSlots input[type="file"][required]');
    for (const fileInput of requiredFileInputs) {
        if (!fileInput.files || fileInput.files.length === 0) {
            const label = fileInput.dataset.label || 'Berkas pendukung';
            alert(`⚠️ "${label}" wajib diunggah!`);
            fileInput.closest('.sn-upload-slot')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
    }

    // ── Kirim ke server ──────────────────────────────────────
    btn.disabled = true;
    btn.innerHTML = `<span style="display:inline-block;width:18px;height:18px;border:2.5px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite"></span> <span>Mengirim...</span>`;

    try {
        const fd = new FormData(this);
        const res = await fetch('{{ route("apply.node.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            window.submittedUuid = data.uuid;
            document.getElementById('snPinDisplay').textContent = data.tracking_code;
            
            // Auto-fill redirect link
            const phone = this.querySelector('input[name="whatsapp"]').value;
            document.getElementById('snRedirectBtn').href = `{{ route('public.tracking') }}?identifier=${data.tracking_code}&whatsapp_verify=${phone}`;
            
            updateProgress(100);
            const modal = document.getElementById('snSuccessModal');
            modal.classList.add('show');
        } else {
            alert(data.message || 'Gagal mengirim. Silakan coba lagi.');
        }
    } catch(err) {
        console.error(err);
        alert('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi petugas.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = `<i class="fas fa-paper-plane"></i> <span>Kirim Permohonan</span>`;
    }
});

let quickRating = 0;
window.setQuickRating = (r) => {
    quickRating = r;
    document.querySelectorAll('.quick-star').forEach(btn => {
        const val = parseInt(btn.getAttribute('data-val'));
        btn.style.color = val <= r ? '#f59e0b' : '#cbd5e1';
    });
}

window.submitQuickFeedback = async () => {
    if(!quickRating || !window.submittedUuid) return;
    
    const btn = document.getElementById('btnSendQuickFeedback');
    const comment = document.getElementById('quick_feedback_comment').value;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';

    try {
        const response = await fetch(`/service/feedback/${window.submittedUuid}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                rating: quickRating, 
                citizen_feedback: comment || 'Pengisian form awal (Navigator)' 
            })
        });

        if(response.ok) {
            document.getElementById('feedbackCommentSection').style.display = 'none';
            document.getElementById('quickFeedbackSuccess').style.display = 'block';
        } else {
            const errData = await response.json();
            alert(errData.message || 'Gagal mengirim penilaian.');
        }
    } catch (e) {
        console.error(e);
        alert('Gagal mengirim penilaian. Silakan cek koneksi Anda.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Kirim Penilaian <i class="fas fa-paper-plane"></i>';
    }
}

function copyPin() {
    const pin = document.getElementById('snPinDisplay').textContent;
    navigator.clipboard.writeText(pin).then(() => {
        const btn = document.getElementById('snPinCopy');
        btn.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
        setTimeout(() => btn.innerHTML = '<i class="fas fa-copy"></i> Salin PIN', 1500);
    });
}

function openStatementDetail() {
    document.getElementById('snStatementModal').classList.add('show');
}
function closeStatementDetail() {
    document.getElementById('snStatementModal').classList.remove('show');
}

// ── Global keyframe spin ───────────────────────────────────
const style = document.createElement('style');
style.textContent = `@keyframes spin { to { transform: rotate(360deg) } }`;
document.head.appendChild(style);
</script>
@endpush
@endsection
