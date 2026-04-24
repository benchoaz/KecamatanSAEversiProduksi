#!/bin/bash
# ============================================================
# Docker Storage Cleanup Script — SILAP
# Usage: ./scripts/cleanup.sh [--force]
# ============================================================

FORCE=false
[ "$1" = "--force" ] && FORCE=true

echo ""
echo "🧹 SILAP — Docker Storage Cleanup"
echo "==================================="

# Tampilkan kondisi sebelum
echo ""
echo "📊 Storage Docker SEBELUM cleanup:"
docker system df

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "Apa yang AMAN dihapus:"
echo "  [1] ✅ Stopped containers       : aman"
echo "  [2] ✅ Build cache (249 layers) : aman (~11 GB)"
echo "  [3] ✅ Dangling images          : aman"
echo "  [4] ⚠️  Unused images (pilih)   : cek dulu"
echo "  [5] ⚠️  Orphan volumes          : cek dulu (DATA!)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

confirm() {
    if [ "$FORCE" = true ]; then return 0; fi
    read -p "❓ $1 (y/N): " ans
    [[ "$ans" =~ ^[Yy]$ ]]
}

# Step 1: Stopped containers
if confirm "Hapus stopped containers? (AMAN)"; then
    docker container prune -f
    echo "  ✅ Done"
fi

# Step 2: Build cache
if confirm "Hapus BUILD CACHE? (AMAN — hemat ~11GB)"; then
    docker builder prune -f
    echo "  ✅ Done"
fi

# Step 3: Dangling images
if confirm "Hapus dangling images (untagged)?"; then
    docker image prune -f
    echo "  ✅ Done"
fi

# Step 4: Unused images (hati-hati)
echo ""
echo "🖼️  Images yang TIDAK dipakai container aktif:"
docker images --format "table {{.Repository}}:{{.Tag}}\t{{.Size}}\t{{.CreatedSince}}" | grep -v "REPOSITORY"
echo ""
echo "⚠️  Images besar yang BISA dihapus (project lama):"
echo "   dashboard-kecamatan-app    (~703MB) — diganti kecamatansaeversikabupaten-app"
echo "   dashboard-kecamatan-scheduler (~703MB) — sama"
echo "   mysql:8.0                  (~1.08GB) — tidak dipakai"
echo "   kindest/node:v1.34.3       (~1.35GB) — Kubernetes dev"
echo "   postgres:15                (~633MB)  — duplikat, ada 15-alpine"

if confirm "Hapus images project lama yang sudah diganti? (cek list di atas dulu)"; then
    for img in \
        "dashboard-kecamatan-app:latest" \
        "dashboard-kecamatan-scheduler:latest" \
        "mysql:8.0" \
        "kindest/node:v1.34.3" \
        "docker/desktop-cloud-provider-kind:v0.5.0" \
        "envoyproxy/envoy:v1.36.4" \
        "ngrok/ngrok-docker-extension:1.0.0" \
        "traefik:v3.1" \
        "postgres:15"; do
        docker rmi "$img" 2>/dev/null && echo "  🗑️  $img" || echo "  ⏭️  $img (tidak ada/sedang dipakai)"
    done
fi

# Step 5: Orphan volumes (SANGAT hati-hati — data bisa hilang!)
echo ""
echo "📦 Volumes yang kemungkinan orphan (project lama):"
ORPHAN_VOLUMES=(
    "dashboard-kecamatan_dbdata"
    "dashboard-kecamatan_laravel_storage"
    "dashboard-kecamatan_n8n_data"
    "dashboard-kecamatan_n8n_data_dashboard"
    "dashboard-kecamatan_nginx_cache"
    "dashboard-kecamatan_redis_data"
    "dashboard-kecamatan_waha_sessions"
    "kabupaten_kabupaten_storage"
    "kabupaten_mysql_data"
    "kabupaten_redis_data"
    "n8n_data"
    "whatsapp_n8n_data"
    "whatsapp_waha_sessions"
)
for vol in "${ORPHAN_VOLUMES[@]}"; do
    if docker volume inspect "$vol" &>/dev/null; then
        echo "  📦 $vol"
    fi
done

echo ""
echo "⚠️  PERHATIAN: Hapus volume = DATA HILANG PERMANEN!"
echo "   Pastikan sudah backup sebelum menghapus!"
if confirm "Hapus orphan volumes dari project lama? (BACKUP DULU!)"; then
    for vol in "${ORPHAN_VOLUMES[@]}"; do
        docker volume rm "$vol" 2>/dev/null && echo "  🗑️  $vol" || echo "  ⏭️  $vol (skip)"
    done
fi

# Hasil akhir
echo ""
echo "📊 Storage Docker SESUDAH cleanup:"
docker system df
echo ""
echo "✅ Cleanup selesai!"
