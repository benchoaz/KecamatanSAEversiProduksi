# Analisis Sistem Ekonomi & Pembangunan
## Dashboard Kecamatan - Government Resource Planning (GRP)

---

## 1. STRUKTUR DATABASE DAN RELASI

### 1.1 Model Utama Ekonomi & Pembangunan

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        MODUL EKONOMI                                        │
├─────────────────────────────────────────────────────────────────────────────┤
│  Umkm (UMKM Rakyat)                                                         │
│  ├── id (UUID)                                                              │
│  ├── nama_usaha, nama_pemilik, no_wa                                       │
│  ├── desa, jenis_usaha, deskripsi                                          │
│  ├── lat, lng (koordinat GIS)                                              │
│  ├── foto_usaha                                                            │
│  ├── status (pending/aktif/nonaktif)                                        │
│  ├── source (self-service/admin/whatsapp)                                   │
│  └── Relations: products, verifications, logs                               │
│                                                                             │
│  UmkmLocal (UMKM Lokal/Produk Desa)                                        │
│  ├── produk, produsen, deskripsi                                            │
│  ├── harga, kategori                                                        │
│  └── Relations: umkm                                                       │
│                                                                             │
│  WorkDirectory (Jasa & Pekerjaan)                                           │
│  ├── display_name, job_category, job_type, job_title                        │
│  ├── price, service_area, service_time                                       │
│  ├── contact_phone, owner_pin                                               │
│  ├── consent_public, status                                                 │
│  └── NEW: price (sudah ditambahkan via migration)                          │
│                                                                             │
│  JobVacancy / Loker (Lowongan Pekerjaan)                                    │
│  ├── judul, deskripsi, persyaratan, perusahaan/lokasi                       │
│  ├── tipe (penuh/waktu-paruh)                                              │
│  └── status                                                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                        MODUL PEMBANGUNAN                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│  PembangunanDesa                                                            │
│  ├── desa_id, master_kegiatan_id                                            │
│  ├── nama_kegiatan, lokasi, tahun_anggaran                                   │
│  ├── pagu_anggaran, realized_anggaran (decimal)                            │
│  ├── komponen_belanja (JSON array)                                          │
│  ├── progress_fisik (0-100%)                                               │
│  ├── progress_keuangan (0-100%)                                            │
│  ├── tanggal_mulai, tanggal_selesai                                          │
│  ├── status (planning/executing/completed)                                 │
│  └── Relations: desa, master_kegiatan, dokumen_spjs, logbooks             │
│                                                                             │
│  PembangunanDokumenSpj (Dokumen Pertanggungjawaban)                         │
│  ├── pembangunan_desa_id, master_dokumen_spj_id                             │
│  ├── is_wajib, status (pending/uploaded/verified)                          │
│  └── file_path, uploaded_at                                                │
│                                                                             │
│  PembangunanLogbook (Buku Harian Proyek)                                    │
│  ├── tanggal, kegiatan, masalah, solusi                                      │
│  ├── progress_fisik, progress_keuangan                                     │
│  └── dokumentasi (foto)                                                     │
│                                                                             │
│  MasterBidang / MasterSubBidang (Master Data Bidang)                        │
│  ├── kode_bidang, nama_bidang, kategori                                     │
│  └── Relations: sub_bidangs                                                │
│                                                                             │
│  MasterKomponenBelanja (Master Komponen Belanja)                           │
│  ├── kode_komponen, nama_komponen, kategori                                 │
│  ├── harga_referensi, is_active                                             │
│  └── Relations: ssh, sbu, kegiatans                                        │
│                                                                             │
│  Master kegiatan (Master Kegiatan APBDes)                                  │
│  ├── nama_kegiatan, kode_rekening, jenis_kegiatan                          │
│  ├── sumber_dana, tahun                                                    │
│  └── Relations: komponen_belanja                                           │
│                                                                             │
│  UsulanMusrenbang (Usulan Musyawarah Perencanaan)                         │
│  ├── desa_id, nama_usulan, deskripsi                                       │
│  ├── skala (desa/kecamatan/kabupaten)                                      │
│  ├── status (usulan/terverifikasi/ditolak/dianggarkan)                    │
│  └── Relations: desa                                                       │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 1.2 Relasi dan foreign_keys

| Tabel Child | Tabel Parent | Foreign Key |
|-------------|--------------|-------------|
| Umkm | - | desa_id (nullable) |
| UmkmLocal | Umkm | umkm_id |
| WorkDirectory | - | - |
| PembangunanDesa | Desa | desa_id |
| PembangunanDesa | Master_kegiatans | master_kegiatan_id |
| PembangunanDokumenSpj | Pembangunan_desa | pembangunan_desa_id |
| PembangunanDokumenSpj | Master_dokumens | master_dokumen_spj_id |
| PembangunanLogbook | Pembangunan_desa | pembangunan_desa_id |
| Master_kegiatans | Master_bidang | master_bidang_id |
| UsulanMusrenbang | Desa | desa_id |

---

## 2. WORKFLOW ANALISIS

### 2.1 Workflow Ekonomi (UMKM & Jasa)

```
┌──────────────┐    ┌─────────────────┐    ┌────────────────────┐    ┌──────────────┐
│   WARGA      │    │  ADMIN DESA     │    │  OPERATOR KEC.    │    │  SUPER ADMIN │
│  (Input Data)│    │   (Verifikasi)  │    │   (Validasi)      │    │   (Approve)  │
└──────┬───────┘    └────────┬────────┘    └─────────┬──────────┘    └──────┬───────┘
       │                     │                      │                     │
       ▼                     ▼                      ▼                     ▼
  ┌─────────┐           ┌──────────┐          ┌────────────┐          ┌───────────┐
  │ Self-   │           │ Review   │          │ Final      │          │ Publish   │
  │ Service │──────────▶│ Data     │─────────▶│ Validation │─────────▶│ to Public │
  │ (Web/   │           │ & Edit   │          │ & Enrich   │          │ Dashboard │
  │ WhatsApp)│          │          │          │            │          │           │
  └─────────┘           └──────────┘          └────────────┘          └───────────┘
```

### 2.2 Workflow Pembangunan (APBDes)

```
┌──────────────┐    ┌─────────────────┐    ┌────────────────────┐    ┌──────────────┐
│   MUSRENBANG │    │  ADMIN DESA     │    │  OPERATOR KEC.    │    │  KABUPATEN  │
│  (Usulan)    │    │  (RAPBDes)     │    │   (Verifikasi)    │    │  (APBD)     │
└──────┬───────┘    └────────┬────────┘    └─────────┬──────────┘    └──────┬───────┘
       │                     │                      │                     │
       ▼                     ▼                      ▼                     ▼
  ┌─────────┐           ┌──────────┐          ┌────────────┐          ┌───────────┐
  │ Input   │           │ Compile  │          │ Validasi   │          │ Final     │
  │ Usulan  │──────────▶│ Rencana  │─────────▶│ & Prioritas│─────────▶│ Approval  │
  │ Desa    │           │ Kegiatan │          │ Kegiatan   │          │           │
  └─────────┘           └──────────┘          └────────────┘          └───────────┘
                                                                              │
                                                                              ▼
                                                                        ┌───────────┐
                                                                        │ Pelaksanaan│
                                                                        │ Pembangunan│
                                                                        │ + SPJ      │
                                                                        └───────────┘
```

---

## 3. REVIEW STRUKTUR DAN REKOMENDASI

### 3.1 Kekuatan Struktur Saat Ini

| Aspek | Status | Keterangan |
|-------|--------|------------|
| Normalisasi dasar | ✅ Baik | Relasi antar tabel sudah sesuai |
| UUID untuk UUID-sensitive tables | ✅ Baik | Umkm, dll menggunakan UUID |
| JSON fields | ✅ Baik | komponen_belanja di PembangunanDesa |
| Master data terpisah | ✅ Baik | MasterBidang, MasterKomponenBelanja, dll |
| Soft deletes | ⚠️ Perlu | Belum ada di semua tabel |
| Timestamp standar | ✅ Baik | created_at, updated_at |

### 3.2 Kekurangan dan Optimasi

| No | Masalah | Impact | Rekomendasi |
|----|---------|--------|-------------|
| 1 | **Indeks kurang** | Query lambat untuk laporan | Tambah indeks pada `desa_id`, `status`, `tahun_anggaran` |
| 2 | **No aggregate tables** | Hitung ulang PDRB lambat | Tambah view/materialized table untuk agregat |
| 3 | **Geom data terpisah** | Sulit overlay GIS | Gabungkan lat/lng ke tabel utama |
| 4 | **SpjRuleEngine manual** | Error-prone | Pakai database-driven rules |
| 5 | **No real-time** | Lambat deteksi anomali | Tambah event-driven notifications |

---

## 4. OPTIMASI DATABASE

### 4.1 Indeks yang Direkomendasikan

```sql
-- Untuk query ekonomi
CREATE INDEX idx_umkm_desa_status ON umkm(desa_id, status);
CREATE INDEX idx_umkm_jenis ON umkm(jenis_usaha);
CREATE INDEX idx_workdirectory_category ON work_directory(job_category, status);

-- Untuk query pembangunan
CREATE INDEX idx_pembangunan_desa_tahun ON pembangunan_desa(desa_id, tahun_anggaran);
CREATE INDEX idx_pembangunan_progress ON pembangunan_desa(progress_fisik, progress_keuangan);
CREATE INDEX idx_pembangunan_status ON pembangunan_desa(status);

-- Untuk agregasi
CREATE INDEX idx_master_kegiatans_tahun ON master_kegiatans(tahun);
CREATE INDEX idx_master_komponen_kategori ON master_komponen_belanja(kategori, is_active);
```

### 4.2 Agregate Tables untuk Real-time Reporting

```sql
-- View untuk ringkasan ekonomi kecamatan
CREATE VIEW v_ekonomi_kecamatan_summary AS
SELECT 
    d.id as desa_id,
    d.nama_desa,
    COUNT(u.id) as total_umkm,
    COUNT(CASE WHEN u.status = 'aktif' THEN 1 END) as umkm_aktif,
    COUNT(wd.id) as total_jasa,
    COUNT(l.id) as total_lowongan
FROM desa d
LEFT JOIN umkm u ON u.desa_id = d.id
LEFT JOIN work_directory wd ON wd.desa_id = d.id
LEFT JOIN loker l ON l.desa_id = d.id
GROUP BY d.id, d.nama_desa;

-- View untuk serapan anggaran pembangunan
CREATE VIEW v_pembangunan_anggaran_summary AS
SELECT 
    d.id as desa_id,
    d.nama_desa,
    SUM(pd.pagu_anggaran) as total_pagu,
    SUM(pd.realisasi_anggaran) as total_realisasi,
    AVG(pd.progress_fisik) as avg_progress_fisik,
    COUNT(pd.id) as total_kegiatn
FROM desa d
LEFT JOIN pembangunan_desa pd ON pd.desa_id = d.id
GROUP BY d.id, d.nama_desa;
```

---

## 5. ENTRY POINT STRATEGIS

### 5.1 Titik Intervensi Kritis

Berdasarkan prinsip real-time monitoring, berikut entry point strategis:

```
Priority 1: Entry Point Real-Time (KRITIS)
┌─────────────────────────────────────────────────────────────┐
│  1. Input Usulan Musrenbang (Desa Level)                    │
│     - Trigger: Saat musrenbang berlangsung                   │
│     - Dampak: Keputusan anggaran tahun berikutnya            │
│     - Otomatisasi: Notifikasi ke kecamatan jika >7 hari      │
│     - API: POST /api/musrenbang/submit                      │
│                                                              │
│  2. Update Progress Pembangunan (Logbook)                    │
│     - Trigger: Tiap mingguan oleh perangkat desa             │
│     - Dampak: Monitoring realisasi APBDes                    │
│     - Otomatisasi: Alert jika progress < expected           │
│     - API: POST /api/pembangunan/{id}/logbook               │
│                                                              │
│  3. Input Invoice/SPJ (Realisasi Anggaran)                 │
│     - Trigger: Saat pencairan anggaran                      │
│     - Damping: Serapan anggaran реальн_TIME                 │
│     - Otomatisasi: Auto-update progress_keuangan            │
│     - API: POST /api/pembangunan/{id}/spj                   │
└─────────────────────────────────────────────────────────────┘

Priority 2: Entry Point Batch (PENTING)
┌─────────────────────────────────────────────────────────────┐
│  1. Validasi Data UMKM (Mingguan/Bulanan)                  │
│  2. Update Harga Komponen Belanja (Tahunan)               │
│  3. Sinkronisasi Data with Kabupaten (Realisasi APBDes)  │
└─────────────────────────────────────────────────────────────┘
```

### 5.2 Early Warning System

```php
// Contoh notifikasi otomatis
class PembangunanAnomalyDetector
{
    public function checkProgressAnomaly(PembangunanDesa $pembangunan)
    {
        $expectedProgress = $this->calculateExpectedProgress($pembangunan);
        $actualProgress = $pembangunan->progress_fisik;
        
        if ($actualProgress < $expectedProgress * 0.7) {
            // Kirim notifikasi ke Operator Kecamatan
            Notification::send($this->getKecamatanOperators(), new ProgressAlert($pembangunan));
        }
        
        if ($pembangan->progress_keuangan > $pembangan->progress_fisik + 20) {
            // Anomali: Keuangan mendahului fisik
            Notification::send($this->getSuperAdmins(), new AnomalyAlert($pembangunan));
        }
    }
}
```

---

## 6. REKOMENDASI WORKFLOW

### 6.1 Optimasi Alur Birokrasi

| Tahap Saat Ini | Masalah | Rekomendasi Otomatisasi |
|---------------|---------|------------------------|
| Input Musrenbang manual | Tertunda, error-prone | Form digital dengan validasi real-time |
| Verifikasi Kecamatan | BOTTLEKNECK | Auto-routing berdasarkan kategori |
| Approval APBDes | Lambat | Dashboard tracking dengan SLA |
| Pelaksanaan Pembangunan | Tidak transparan | Public dashboard progress |
| SPJ/Pertanggungjawaban | Terlambat | Reminder otomatis 7 hari sebelum deadline |

### 6.2 Konversi Data Otomatis ke Indikator

```
Pembangunan ─────────────────────────────────────────────▶ Indikator Ekonomi
    │                                                        │
    ▼                                                        ▼
┌──────────────────┐    ┌──────────────────┐    ┌──────────────────────┐
│ Progress Fisik  │    │ Realisasi        │    │ PDRB Mikro           │
│ + Output fisik  │───▶│ Anggaran         │───▶│ (Nilai proyek ×      │
│ (m2, unit, dll) │    │ (APBDes terserap)│    │  multiplier effect) │
└──────────────────┘    └──────────────────┘    └──────────────────────┘
```

---

## 7. SARAN TEKNOLOGI

### 7.1 Integrasi API yang Dibutuhkan

| API | Tujuan | Priority |
|-----|--------|----------|
| **BPS API** | Data statistik ekonomi mikro | Tinggi |
| **DJPK Kemenkeu** | Data transfer DAU/DDA | Sedang |
| **Google Maps/Mapbox** | Visualisasi GIS | Tinggi |
| **WhatsApp Business** | Notifikasi & pelaporan | Tinggi |
| **SIKNAS/SIMKES** | Data kesehatan untuk PBD | Sedang |

### 7.2 Sistem Peringatan Dini (EWS)

```yaml
# Early Warning Rules
rules:
  # Pembangunan
  - name: "Serapan Anggaran Lambat"
    condition: "progress_keuangan < (hari_berlalu / hari_total) * 0.8"
    alert_level: "warning"
    notify: "kecamatan_operator"
    
  - name: "Progress Fisik Mandek"
    condition: "progress_fisik_tidak_berubah_30_hari"
    alert_level: "critical"
    notify: "kecamatan_operator,super_admin"
    
  # Ekonomi
  - name: "UMKM Tidak Aktif Tinggi"
    condition: "ratio(umkm_nonaktif / umkm_total) > 0.3"
    alert_level: "info"
    notify: "kecamatan_operator"
    
  - name: "Lowongan Kerja Expired"
    condition: "tanggal_tutup < today AND status = 'open'"
    alert_level: "info"
    notify: "system"
```

### 7.3 Dashboard Visualisasi

1. **Peta Interaktif**: Sebaran UMKM, Proyek Pembangunan
2. **Grafik Real-time**: Serapan Anggaran, Progress Fisik vs Keuangan
3. **Indikator Macro**: PDRB Mikro Kecamatan, IPM Mikro
4. **Heatmap**: Konsentrasi ekonomi, zona pembangunan

---

## 8. RINGKASAN EXECUTIVE

| Kategori | Status | Action Item |
|----------|--------|-------------|
| **Struktur Database** | Baik, perlu indeks | Tambah indeks pada query frequently-used |
| **Integritas Data** | Baik | Tambah unique constraints |
| **Real-time Monitoring** | Lemah | Implementasi EWS |
| **Workflow** | Manual-heavy | Automasi notifikasi & routing |
| **GIS** | Basic | Enhanced mapping integration |
| **Reporting** | On-demand | Pre-calculated aggregates |

---

*Analisis ini disusun berdasarkan review struktur file dan database aplikasi Dashboard Kecamatan.*
*Versi: 1.0 | Tanggal: 2026-03-02*
