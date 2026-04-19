#!/bin/bash
# ============================================================
# SILAP Data Migration Script: Cloud (Neon) -> Local (Docker)
# ============================================================

# Load existing environment variables
if [ ! -f app/.env ]; then
    echo "❌ Error: app/.env not found!"
    exit 1
fi

# Extract Neon Credentials
export $(grep -v '^#' app/.env | xargs)

NEON_URL="postgresql://$DB_USERNAME:$DB_PASSWORD@$DB_HOST/$DB_DATABASE?sslmode=require"
LOCAL_DB="dashboard_kecamatan"
LOCAL_USER="user"
LOCAL_PASS="password"

echo "📡 Step 1: Exporting data from Neon Cloud (via Docker)..."
# Menggunakan kontainer lokal untuk melakukan dump dari Neon
docker exec -e PGPASSWORD=$DB_PASSWORD kecamatan-db pg_dump --no-owner --no-privileges --clean --if-exists -h $DB_HOST -U $DB_USERNAME -d $DB_DATABASE > neon_dump.sql

echo "📥 Step 2: Importing data to Local Docker Postgres..."
# Memasukkan data ke dalam kontainer Docker
cat neon_dump.sql | docker exec -i kecamatan-db psql -U $LOCAL_USER -d $LOCAL_DB

echo "🔄 Step 3: Updating app/.env to use Local Database..."
# Mengubah koneksi di .env ke kontainer DB lokal
sed -i 's/^DB_HOST=.*/DB_HOST=db/' app/.env
sed -i 's/^DB_PORT=.*/DB_PORT=5432/' app/.env
sed -i 's/^DB_DATABASE=.*/DB_DATABASE=dashboard_kecamatan/' app/.env
sed -i 's/^DB_USERNAME=.*/DB_USERNAME=user/' app/.env
sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=password/' app/.env

echo "🧹 Step 4: Cleaning up..."
rm neon_dump.sql

echo "✨ MIGRATION SUCCESSFUL!"
echo "Your app is now running on the local database."
