#!/bin/bash
# ============================================================
# KecamatanSAE - Container Health Monitor
# Lokasi: /home/ubuntu/kecamatanSAE/monitor.sh
# Jalankan via cron: */5 * * * * /home/ubuntu/kecamatanSAE/monitor.sh
# ============================================================

COMPOSE_FILE="/home/ubuntu/kecamatanSAE/docker-compose.vps.yml"
LOG_FILE="/home/ubuntu/kecamatanSAE/monitor.log"
CONTAINERS=("kecamatan-app" "kecamatan-db" "kecamatan-redis" "kecamatan-nginx" "traefik-gateway")
MAX_LOG_LINES=500

# Timestamp
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# Cek setiap container
for CONTAINER in "${CONTAINERS[@]}"; do
    STATUS=$(sudo docker inspect --format='{{.State.Status}}' "$CONTAINER" 2>/dev/null)
    HEALTH=$(sudo docker inspect --format='{{.State.Health.Status}}' "$CONTAINER" 2>/dev/null)

    if [ "$STATUS" != "running" ]; then
        echo "[$TIMESTAMP] ⚠️  ALERT: Container '$CONTAINER' tidak berjalan (Status: $STATUS). Mencoba restart..." >> "$LOG_FILE"
        sudo docker compose -f "$COMPOSE_FILE" restart "$CONTAINER" >> "$LOG_FILE" 2>&1
        echo "[$TIMESTAMP] ✅ Restart '$CONTAINER' selesai." >> "$LOG_FILE"
    elif [ "$HEALTH" = "unhealthy" ]; then
        echo "[$TIMESTAMP] ⚠️  ALERT: Container '$CONTAINER' UNHEALTHY. Mencoba restart..." >> "$LOG_FILE"
        sudo docker compose -f "$COMPOSE_FILE" restart "$CONTAINER" >> "$LOG_FILE" 2>&1
        echo "[$TIMESTAMP] ✅ Restart '$CONTAINER' selesai." >> "$LOG_FILE"
    fi
done

# Batasi ukuran log agar tidak membengkak
LINES=$(wc -l < "$LOG_FILE" 2>/dev/null || echo 0)
if [ "$LINES" -gt "$MAX_LOG_LINES" ]; then
    tail -n "$MAX_LOG_LINES" "$LOG_FILE" > "${LOG_FILE}.tmp" && mv "${LOG_FILE}.tmp" "$LOG_FILE"
fi
