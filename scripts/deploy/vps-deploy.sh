#!/bin/bash
set -e

# ============================================================
# VPS Deploy Script — Optimized for 10GB Storage
# Usage: ./scripts/deploy/vps-deploy.sh [--with-automation]
# ============================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
WITH_AUTOMATION=false

# Parse args
for arg in "$@"; do
    case $arg in
        --with-automation) WITH_AUTOMATION=true ;;
    esac
done

cd "$ROOT_DIR"

echo ""
echo "🚀 SILAP — VPS Deployment Script"
echo "=================================="
echo "📅 $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# ── STEP 0: Install Docker if missing ──────────────────────
if ! command -v docker &> /dev/null; then
    echo "📦 [0/7] Docker tidak ditemukan. Menginstal Docker..."
    sudo apt-get update
    sudo apt-get install -y ca-certificates curl gnupg
    sudo install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    sudo chmod a+r /etc/apt/keyrings/docker.gpg
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    sudo apt-get update
    sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    sudo systemctl start docker
    sudo systemctl enable docker
    echo "  ✅ Docker & Compose terinstal"
fi

# ── STEP 1: Pre-flight ─────────────────────────────────────
echo "🔍 [1/7] Pemeriksaan awal..."

# Cek .env
[ ! -f .env ] && { echo "❌ .env tidak ada. Jalankan: cp .env.example .env lalu isi nilainya."; exit 1; }
[ ! -f app/.env ] && { echo "❌ app/.env tidak ada."; exit 1; }

# Cek acme.json permissions (KRITIS untuk Traefik)
if [ -f gateway/acme.json ]; then
    chmod 600 gateway/acme.json
    echo "  ✅ acme.json permissions: 600"
else
    touch gateway/acme.json && chmod 600 gateway/acme.json
    echo "  ✅ acme.json dibuat baru dengan permissions: 600"
fi

# Cek storage VPS
FREE_KB=$(df -k . | awk 'NR==2 {print $4}')
FREE_GB=$(echo "scale=1; $FREE_KB/1048576" | bc)
echo "  💾 Storage tersedia: ${FREE_GB} GB"
if [ "$FREE_KB" -lt 3145728 ]; then  # < 3GB
    echo "  ⚠️  WARNING: Storage < 3GB. Membersihkan sebelum deploy..."
    docker builder prune -f --keep-storage 2G 2>/dev/null || true
    docker image prune -f 2>/dev/null || true
fi

# ── STEP 2: Bersihkan resource tidak terpakai ───────────────
echo ""
echo "🧹 [2/7] Membersihkan Docker cache (optimasi storage)..."
docker container prune -f 2>/dev/null && echo "  ✅ Stopped containers dihapus"
docker builder prune -f 2>/dev/null && echo "  ✅ Build cache dihapus"
FREE_KB_AFTER=$(df -k . | awk 'NR==2 {print $4}')
FREE_GB_AFTER=$(echo "scale=1; $FREE_KB_AFTER/1048576" | bc)
echo "  💾 Storage setelah cleanup: ${FREE_GB_AFTER} GB"

# ── STEP 3: Pull images ────────────────────────────────────
echo ""
echo "📦 [3/7] Pull base images (sequential, hemat RAM)..."
CORE_IMAGES=("traefik:v2.10" "postgres:17-alpine" "redis:7-alpine" "nginx:alpine")
for img in "${CORE_IMAGES[@]}"; do
    echo "  ⬇  $img"
    docker pull "$img" --quiet || echo "  ⚠️  Gagal pull $img, akan pakai cache"
done

if [ "$WITH_AUTOMATION" = true ]; then
    echo "  ⬇  devlikeapro/waha:latest (besar, ~3GB...)"
    docker pull devlikeapro/waha:latest --quiet || true
    echo "  ⬇  n8nio/n8n:latest (~1.6GB...)"
    docker pull n8nio/n8n:latest --quiet || true
fi

# ── STEP 4: Build app ──────────────────────────────────────
echo ""
echo "🏗️  [4/7] Build aplikasi Laravel..."
docker compose build --no-cache app scheduler 2>&1 | tail -5
echo "  ✅ Build selesai"

# Hapus build cache SEGERA setelah build (hemat storage)
docker builder prune -f 2>/dev/null && echo "  ✅ Build cache dihapus setelah build"

# ── STEP 5: Deploy services ────────────────────────────────
echo ""
echo "🚀 [5/7] Menjalankan services..."

COMPOSE_CMD="docker compose"
PROFILE_CMD=""
if [ "$WITH_AUTOMATION" = true ]; then
    PROFILE_CMD="--profile automation"
    echo "  📡 Mode: CORE + AUTOMATION (waha & n8n)"
else
    echo "  📡 Mode: CORE saja (hemat storage)"
fi

$COMPOSE_CMD $PROFILE_CMD up -d --remove-orphans

# ── STEP 6: Tunggu & Verifikasi ────────────────────────────
echo ""
echo "⏳ [6/7] Menunggu services siap..."
sleep 15

echo "  Checking Traefik..."
for i in $(seq 1 10); do
    if docker inspect traefik-gateway --format='{{.State.Health.Status}}' 2>/dev/null | grep -q "healthy"; then
        echo "  ✅ Traefik: HEALTHY"
        break
    fi
    sleep 5
    [ "$i" -eq 10 ] && echo "  ⚠️  Traefik belum healthy, cek: docker logs traefik-gateway"
done

echo "  Checking Database..."
for i in $(seq 1 10); do
    if docker inspect kecamatan-db --format='{{.State.Health.Status}}' 2>/dev/null | grep -q "healthy"; then
        echo "  ✅ Database: HEALTHY"
        break
    fi
    sleep 5
    [ "$i" -eq 10 ] && echo "  ⚠️  Database belum healthy, cek: docker logs kecamatan-db"
done

# ── STEP 7: Ringkasan ──────────────────────────────────────
echo ""
echo "📊 [7/7] Ringkasan deployment:"
echo "─────────────────────────────────────────────────────"
docker compose $PROFILE_CMD ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"

FREE_FINAL=$(df -h . | awk 'NR==2 {print $4}')
echo ""
echo "─────────────────────────────────────────────────────"
echo "💾 Storage tersisa: $FREE_FINAL"
echo ""
echo "🌐 URL Akses:"
DOMAIN=$(grep DOMAIN .env | cut -d= -f2 | head -1)
echo "   Dashboard : https://$DOMAIN"
if [ "$WITH_AUTOMATION" = true ]; then
    echo "   WAHA      : https://$DOMAIN/waha/dashboard"
    echo "   n8n       : https://$DOMAIN/n8n/"
fi
echo ""
echo "📋 Log Commands:"
echo "   docker logs traefik-gateway -f"
echo "   docker logs kecamatan-app -f"
echo ""
echo "✅ DEPLOYMENT SELESAI!"
