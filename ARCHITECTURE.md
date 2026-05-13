# 🏛️ ARSITEKTUR PROYEK: KecamatanSAE
> **Dokumen Wajib Dibaca oleh AI Sebelum Melakukan Perubahan Apapun**
> Versi Terakhir: 11 Mei 2026 | Diaudit langsung dari VPS Produksi oleh Antigravity AI

---

## 🖥️ STATUS VPS PRODUKSI (Diverifikasi Langsung)

**Server**: `43.134.166.153` | **Domain**: `kecamatanbesuk.web.id`

### Container yang Berjalan (Docker):
| Container | Status | Port | Fungsi |
|---|---|---|---|
| `traefik-gateway` | ✅ Up 5 days (healthy) | 80, 443 | Reverse Proxy & SSL |
| `kecamatan-nginx` | ✅ Up 5 days | 80 (internal) | Web Server |
| `kecamatan-app` | ✅ Up 28 hours (healthy) | 9000 (PHP-FPM) | Laravel Application |
| `kecamatan-db` | ✅ Up 5 days (healthy) | 5432 | PostgreSQL 17 |
| `kecamatan-redis` | ✅ Up 5 days (healthy) | 6379 | Cache & Session |
| `kecamatan-scheduler` | ✅ Up 2 days (healthy) | 9000 | Laravel Scheduler |

### Volume Mapping Nginx (Kritis!):
```
/home/ubuntu/kecamatanSAE/app/public   → /var/www/public   (read-only)
/home/ubuntu/kecamatanSAE/app/storage  → /var/www/storage  (read-only di nginx)
/home/ubuntu/kecamatanSAE/app/docker/nginx/conf.d → /etc/nginx/conf.d
```

> [!IMPORTANT]
> **Storage Link yang Terverifikasi di VPS:**
> `public/storage` → `../storage/app/public` (RELATIF ✅)
> Path ini sudah benar. JANGAN diubah ke absolute path.

### Struktur Storage Aktual di VPS (`storage/app/public/`):
| Folder | Pemilik | Fungsi |
|---|---|---|
| `public_services/` | `www-data (82:82)` | Berkas lampiran pengajuan warga |
| `backgrounds/` | `ubuntu` | Gambar latar aplikasi |
| `logos/` | `ubuntu` | Logo kecamatan |
| `media/` | `ubuntu` | Media umum |
| `umkm/` | `ubuntu` | Foto produk UMKM |
| `users/` | `ubuntu` | Foto profil user |

> [!WARNING]
> Folder `public_services/` dimiliki oleh `www-data (uid 82)` sedangkan folder lain dimiliki `ubuntu`. Ini normal karena file tersebut dibuat oleh container Laravel (php-fpm berjalan sebagai www-data). **JANGAN ubah ownership folder ini** atau upload berkas warga akan gagal.

### Catatan Nginx Config (Temuan Audit):
Ditemukan duplikasi baris `fastcgi_param HTTPS on;` di `default.conf`. Ini tidak berbahaya tapi perlu dibersihkan di sesi mendatang.

---

---

## 🎯 TUJUAN DOKUMEN INI

Dokumen ini adalah **"Peta Navigasi"** dan **"Kitab Aturan"** bagi siapapun (manusia atau AI) yang akan melakukan pengembangan atau perbaikan pada proyek ini. Dilarang keras melakukan perubahan kode tanpa membaca dan memahami dokumen ini terlebih dahulu.

---

## 📐 GAMBARAN BESAR SISTEM

```
[WARGA / PUBLIK]
      │
      ▼
[Domain: kecamatanbesuk.web.id]
      │
      ▼
[Traefik Gateway (Docker)] ──SSL──► [Nginx Container]
                                          │
                              ┌───────────┴────────────┐
                              │                        │
                       [Laravel App]            [Static Files]
                       (PHP-FPM)                (/public, /storage)
                              │
                    ┌─────────┴──────────┐
                    │                    │
              [PostgreSQL]            [Redis]
              (Database)         (Cache & Session)
```

---

## 🗂️ STRUKTUR FOLDER UTAMA

```
KecamatanSAEversiProduksi/
├── app/                     ← Seluruh kode Laravel (MAIN APPLICATION)
│   ├── app/                 ← Logika inti aplikasi
│   │   ├── Console/         ← Artisan commands & scheduler
│   │   ├── Filament/        ← [LEGACY] Sisa konfigurasi Filament (tidak aktif)
│   │   ├── Helpers/         ← Helper function global
│   │   ├── Http/
│   │   │   ├── Controllers/ ← Semua controller (lihat detail di bawah)
│   │   │   └── Middleware/  ← Guard & filter request
│   │   ├── Models/          ← 72 Model database (lihat detail di bawah)
│   │   ├── Services/        ← Business Logic Layer (18+ service)
│   │   │   └── WhatsApp/    ← Seluruh logika bot WhatsApp
│   │   └── Traits/          ← Shared behavior (Auditable, dsb)
│   ├── database/
│   │   ├── migrations/      ← Perubahan struktur database
│   │   └── seeders/         ← Data awal & konfigurasi default
│   ├── resources/views/     ← Blade templates (UI)
│   ├── routes/              ← Definisi URL aplikasi
│   │   ├── web.php          ← Route landing page & auth
│   │   ├── kecamatan.php    ← Route dashboard kecamatan
│   │   ├── desa.php         ← Route dashboard desa
│   │   └── public/          ← Route publik (layanan warga)
│   └── storage/app/public/  ← File upload warga (berkas, foto)
│
├── gateway/                 ← Konfigurasi Traefik reverse proxy
├── docker-compose.vps.yml   ← Konfigurasi Docker PRODUKSI (VPS)
├── docker-compose.local.yml ← Konfigurasi Docker LOKAL
└── ARCHITECTURE.md          ← FILE INI (Panduan Arsitektur)
```

---

## 🎭 LAPISAN ARSITEKTUR (LAYER)

### Layer 1: Routes (Pintu Masuk)
Setiap URL memiliki kelompoknya masing-masing. **WAJIB** ditempatkan di file yang benar:

| File Route | Fungsi | Contoh URL |
|---|---|---|
| `web.php` | Landing page publik & login | `/`, `/login` |
| `public/layanan.php` | Form pengajuan layanan warga | `/layanan`, `/apply` |
| `kecamatan.php` | Seluruh dashboard kecamatan | `/kecamatan/pelayanan` |
| `desa.php` | Seluruh dashboard desa | `/desa/pembangunan` |
| `api.php` | Endpoint API (bot WA, integrasi) | `/api/webhook` |

### Layer 2: Controllers (Pengarah Lalu Lintas)
Controller dikelompokkan berdasarkan peran pengguna:

```
app/Http/Controllers/
├── Public/          ← Akses publik tanpa login (LayananController, dsb)
├── Kecamatan/       ← Dashboard operator kecamatan (auth required)
├── Desa/            ← Dashboard operator desa (auth required)
├── Pemerintahan/    ← Modul pemerintahan kecamatan
├── Master/          ← Data master (MasterLayanan, dsb)
└── Api/             ← Endpoint untuk integrasi eksternal
```

> [!WARNING]
> **ATURAN KERAS**: Logika bisnis (Business Logic) **TIDAK BOLEH** ditulis di dalam Controller. Controller hanya boleh menerima request, memanggil Service, dan mengembalikan response.

### Layer 3: Services (Otak Bisnis)
Semua logika perhitungan, pemrosesan data, dan integrasi eksternal harus ada di sini:

| Service | Tanggung Jawab |
|---|---|
| `ApplicationProfileService` | Mengelola profil & identitas aplikasi (nama daerah, logo) |
| `NavigationService` | Mengatur menu sidebar dinamis |
| `ModuleSettingsService` | Mengaktifkan/menonaktifkan modul |
| `WahaN8nService` | Integrasi dengan WAHA (WA API) & n8n |
| `WeatherService` | Data cuaca dari BMKG |
| `WhatsApp/AiHandler` | Logika AI Chatbot WhatsApp |
| `WhatsApp/IntentHandler` | Mendeteksi maksud pesan WA (layanan, pengaduan) |
| `WhatsApp/StatusHandler` | Cek status pengajuan via WA |

### Layer 4: Models (Data)
Model dikelompokkan berdasarkan domain:

**Domain Utama (Core):**
- `User`, `Role` → Manajemen pengguna & hak akses
- `AppProfile` → **SINGLE RECORD** - Identitas & konfigurasi aplikasi
- `ModuleSetting` → Status aktif/nonaktif setiap modul

**Domain Pelayanan Publik:**
- `MasterLayanan` → Daftar jenis layanan (KTP, KK, dll)
- `ServiceNode` → Sub-layanan (navigasi multi-langkah)
- `ServiceRequirement` → Syarat dokumen tiap layanan
- `PublicService` → Pengajuan berkas warga yang masuk
- `PublicServiceAttachment` → File berkas yang diunggah warga
- `PublicServiceHistory` → Riwayat status pengajuan

**Domain WhatsApp:**
- `WhatsappSession` → Sesi & status percakapan WA
- `WhatsappLog` → Log semua pesan masuk & keluar
- `AiMemory` → Memori nama & konteks user per nomor WA
- `WahaN8nSetting` → Konfigurasi koneksi WAHA & n8n

**Domain Desa:**
- `Desa` → Data pokok desa
- `PembangunanDesa`, `PerencanaanDesa` → Manajemen anggaran desa
- `PersonilDesa`, `AparaturDesa` → SDM perangkat desa

**Domain UMKM & Ekonomi:**
- `Umkm`, `UmkmProduct`, `UmkmOrder` → Marketplace UMKM
- `Loker`, `JobVacancy` → Lowongan kerja

---

## 🔐 SISTEM HAK AKSES (USER MANAGEMENT)

### Hierarki Role (Kasta Pengguna):

```
[super_admin_kabupaten]  ← LEVEL 0 - Akses ke SEMUA (Masa Depan: Gateway)
         │
    [Super Admin]        ← LEVEL 1 - Akses ke semua fitur kecamatan ini
         │
  [Operator Kecamatan]   ← LEVEL 2 - Akses dashboard kecamatan
         │
    [Verifikator]        ← LEVEL 3 - Verifikasi berkas masuk
         │
   [Operator Desa]       ← LEVEL 4 - Akses dashboard desa
         │
  [Module Admins]        ← LEVEL 5 - Akses modul spesifik saja
  (pelayanan_admin,
   trantibum_admin,
   umkm_admin,
   loker_admin)
```

### Aturan Role di Middleware:
- `ModuleRoleMiddleware` → Mengatur akses per modul
- `CheckRole` → Mengecek role secara umum
- User `admin` atau `super_admin_kabupaten` selalu **bypass** semua pengecekan

> [!IMPORTANT]
> Jika menambahkan role baru, WAJIB update di:
> 1. `app/Http/Middleware/ModuleRoleMiddleware.php`
> 2. `app/Models/User.php` (Constants & helper methods)
> 3. Seeder terkait di `database/seeders/`

---

## 🤖 ARSITEKTUR WHATSAPP BOT

```
[Pesan Masuk dari Warga]
        │
        ▼
[WhatsAppReplyController] ← Webhook dari WAHA
        │
        ▼
[WhatsApp/WhatsAppManager] ← Distributor Utama
        │
   ┌────┴────────────────────────────────┐
   │                                     │
[IntentHandler]                    [AiHandler]
(Pesan Terstruktur:                (Pesan Bebas:
 MENU, STATUS, dll)                 AI Gemini/OpenAI)
   │                                     │
   ├── StateHandler (Menu interaktif)    └── AiMemory (Ingat nama user)
   ├── StatusHandler (Cek status)
   ├── SyaratHandler (Info syarat)
   ├── ComplaintHandler (Pengaduan)
   └── UmkmHandler (Marketplace)
```

### Aturan Kritis AiMemory:
- Nama user **HANYA** boleh berubah jika ada tag `[SET_NAME:nama]` di respons AI
- **DILARANG** menyimpan nama berdasarkan tebakan dari teks respons AI (sudah dinonaktifkan)
- Jika `user_name` kosong di DB, WAJIB tampilkan sebagai `'Belum diketahui'`

---

## 💾 PENYIMPANAN FILE

```
storage/app/
├── public/                  ← File yang bisa diakses publik via URL
│   ├── public_services/     ← Berkas lampiran warga (foto KTP, dll)
│   │   └── {service_id}/    ← Dikelompokkan per ID pengajuan
│   └── results/             ← Hasil/output dari operator
└── private/                 ← File rahasia (tidak bisa diakses publik)
```

> [!IMPORTANT]
> **ATURAN WAJIB**: Semua file yang diunggah warga HARUS disimpan ke disk `public` agar bisa diakses oleh operator melalui URL. Gunakan `Storage::disk('public')->put(...)`.
> **DILARANG** menggunakan `Storage::put(...)` (yang default ke disk `local`/private).

### Storage Link di Docker:
Di VPS, Nginx hanya bisa melihat folder `./app/storage`. Storage link (`public/storage`) harus dibuat secara **RELATIF** (bukan absolute) agar tidak putus saat container restart:
```bash
# BENAR (relatif):
ln -s ../storage/app/public public/storage

# SALAH (absolute - akan putus):
ln -s /var/www/storage/app/public /var/www/public/storage
```

---

## 🗃️ DATABASE

- **Driver**: PostgreSQL 17
- **Session**: Redis (BUKAN file PHP default)
- **Cache**: Redis

### Konvensi Penamaan Tabel:
| Pola | Contoh |
|---|---|
| Entitas tunggal (snake_case) | `public_services`, `master_layanans` |
| Pivot table | `role_has_permissions` |
| Log/History | `public_service_histories`, `whatsapp_logs` |

> [!WARNING]
> Saat membuat Migration baru, **SELALU** perhatikan:
> - Apakah kolom yang ditambah sudah ada? Cek dulu dengan `Schema::hasColumn()`
> - Jangan lupa `->nullable()` jika kolom bersifat opsional agar data lama tidak error

---

## 🚀 RENCANA MASA DEPAN: KABUPATEN GATEWAY

Ini adalah fitur yang **BELUM dibangun** namun sudah direncanakan. Folder dan kode yang berkaitan akan tinggal di:

```
app/app/Services/Hub/        ← [AKAN DIBUAT] Logika pusat kabupaten
app/app/Models/Hub/          ← [AKAN DIBUAT] Model data kabupaten
app/app/Http/Middleware/Hub/ ← [AKAN DIBUAT] Middleware tenant isolation
```

### Tabel Database yang Akan Dibuat (HUB):
| Tabel | Fungsi |
|---|---|
| `districts` | Daftar 24 kecamatan beserta koneksi DB-nya |
| `wa_gateway_sessions` | Router pesan WA ke kecamatan yang tepat |
| `hub_users` | Manajemen Super Admin Kabupaten |
| `global_ai_configs` | Instruksi AI yang berlaku untuk semua kecamatan |
| `integration_webhooks` | Endpoint integrasi pihak ketiga (Kominfo, Dukcapil) |

> [!NOTE]
> **Prinsip Isolasi**: Setiap kecamatan akan memiliki database sendiri. Gateway hanya menyimpan "Peta Jalan" (Routing Table), bukan data warga itu sendiri.

---

## ⚠️ ATURAN WAJIB UNTUK AI (CODING RULES)

Setiap AI yang mengerjakan proyek ini **WAJIB** mengikuti aturan berikut:

1.  **Baca Dulu, Koding Kedua**: Sebelum mengubah file apapun, baca file tersebut terlebih dahulu untuk memahami konteksnya.
2.  **Satu File per Request**: Jangan mengubah banyak file sekaligus jika tidak diperlukan. Perubahan kecil dan terarah lebih aman.
3.  **Jangan Hapus Komentar**: Pertahankan semua komentar yang ada di kode, kecuali ada instruksi eksplisit untuk menghapusnya.
4.  **Periksa Foreign Key**: Saat membuat migration baru yang berhubungan dengan tabel lain, pastikan tabel referensinya sudah ada.
5.  **Validasi Dulu, Simpan Kemudian**: Semua input dari pengguna (warga maupun admin) WAJIB divalidasi di Controller sebelum data disimpan ke database.
6.  **Pesan Error Spesifik**: Jangan pernah menampilkan pesan error "Kesalahan Sistem" yang generik. Tampilkan pesan yang membantu pengguna memahami apa yang salah.
7.  **Cek Aturan Storage**: Semua file upload HARUS ke disk `public`. Lihat bagian Penyimpanan File di atas.
8.  **Hormati Hierarki Role**: Jangan mengubah logika role tanpa memperbarui semua file yang terdampak (lihat bagian User Management).
9.  **Git Commit Kecil**: Setiap perubahan yang sudah disetujui segera di-commit dengan pesan yang jelas sebelum melanjutkan ke perubahan berikutnya.
10. **Tanya Jika Ragu**: Jika ada interpretasi yang ambigu, tanyakan kepada Bapak sebelum mengeksekusi. Lebih baik terlambat bertanya daripada salah langkah.
