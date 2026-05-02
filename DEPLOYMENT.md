# Panduan Deployment KecamatanSAE (GitHub -> VPS)

Dokumen ini berisi langkah-langkah untuk melakukan deployment perubahan terbaru dari repositori GitHub ke server VPS Produksi.

## 1. Persiapan Repositori (Local -> GitHub)

Pastikan semua perubahan terbaru sudah di-push ke GitHub:

```bash
# Tambahkan semua file baru dan perubahan
git add .

# Buat commit dengan pesan yang jelas
git commit -m "feat: integrasi navigasi dinamis, sinkronisasi demografi, dan perbaikan sidebar"

# Push ke main branch
git push origin main
```

## 2. Persiapan Server VPS

Hubungkan ke VPS via SSH:
```bash
ssh root@<IP_VPS_ANDA>
```

Masuk ke direktori projek:
```bash
cd /root/KecamatanSAEversiProduksi
```

## 3. Update Kode di VPS

Tarik perubahan terbaru dari GitHub:
```bash
git pull origin main
```

## 4. Jalankan Docker (Production Mode)

Gunakan `docker-compose.vps.yml` untuk lingkungan produksi:

```bash
# Restart container untuk memuat perubahan kode dan env
docker compose -f docker-compose.vps.yml up -d --build
```

## 5. Finalisasi di VPS

Setelah container berjalan, lakukan langkah pembersihan dan migrasi:

```bash
# Jalankan migrasi database
docker compose -f docker-compose.vps.yml exec app php artisan migrate --force

# Sinkronisasi Menu Navigasi
docker compose -f docker-compose.vps.yml exec app php artisan db:seed --class=NavigationSeeder --force

# Sinkronisasi Data Demografi (17 Desa)
docker compose -f docker-compose.vps.yml exec app php artisan desa:sync-demografi

# Bersihkan Cache
docker compose -f docker-compose.vps.yml exec app php artisan optimize:clear
```

## 6. Monitoring

Cek status container:
```bash
docker compose -f docker-compose.vps.yml ps
```

Cek log jika terjadi error:
```bash
docker compose -f docker-compose.vps.yml logs -f app
```

---
**Catatan Penting:**
- Pastikan file `/root/KecamatanSAEversiProduksi/app/.env` di VPS sudah memiliki konfigurasi produksi yang benar.
- Domain `kecamatanbesuk.web.id` sudah diarahkan ke IP VPS.
