#!/bin/bash
# ============================================================
# Generate ADMIN_AUTH untuk Traefik BasicAuth
# Usage: ./scripts/generate-auth.sh [username] [password]
# ============================================================

set -e

USERNAME=${1:-admin}
PASSWORD=${2:-}

if [ -z "$PASSWORD" ]; then
    echo ""
    echo "🔐 SILAP — Traefik BasicAuth Generator"
    echo "======================================="
    read -s -p "Masukkan password untuk user '$USERNAME': " PASSWORD
    echo ""
    read -s -p "Konfirmasi password: " PASSWORD2
    echo ""
    if [ "$PASSWORD" != "$PASSWORD2" ]; then
        echo "❌ Password tidak cocok!"
        exit 1
    fi
fi

# Check jika htpasswd tersedia
if ! command -v htpasswd &>/dev/null; then
    echo "📦 htpasswd tidak ditemukan. Menggunakan Docker untuk generate..."
    HASH=$(docker run --rm httpd:alpine htpasswd -nb "$USERNAME" "$PASSWORD" | sed 's/\$/\$\$/g')
else
    HASH=$(htpasswd -nb "$USERNAME" "$PASSWORD" | sed 's/\$/\$\$/g')
fi

echo ""
echo "✅ ADMIN_AUTH berhasil dibuat!"
echo ""
echo "Salin baris berikut ke file .env kamu:"
echo "─────────────────────────────────────────"
echo "ADMIN_AUTH=$HASH"
echo "─────────────────────────────────────────"
echo ""

# Tawaran update otomatis
read -p "Perbarui .env sekarang secara otomatis? (y/N): " CONFIRM
if [[ "$CONFIRM" =~ ^[Yy]$ ]]; then
    ENV_FILE="$(dirname "$0")/../.env"
    if [ -f "$ENV_FILE" ]; then
        # Update atau tambah ADMIN_AUTH
        if grep -q "^ADMIN_AUTH=" "$ENV_FILE"; then
            sed -i "s|^ADMIN_AUTH=.*|ADMIN_AUTH=$HASH|" "$ENV_FILE"
        else
            echo "ADMIN_AUTH=$HASH" >> "$ENV_FILE"
        fi
        echo "✅ .env berhasil diperbarui!"
    else
        echo "⚠️  File .env tidak ditemukan di: $ENV_FILE"
    fi
fi

echo ""
echo "ℹ️  Setelah update .env, restart automation services:"
echo "   docker compose --profile automation up -d --force-recreate waha n8n"
