#!/bin/bash

# Configuration
BACKUP_DIR="/home/ubuntu/kecamatanSAE/backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_NAME="db_backup_$TIMESTAMP.sql"
RETENTION_DAYS=7

# Create backup directory if not exists
mkdir -p $BACKUP_NAME

echo "Starting backup of dashboard_kecamatan..."

# Perform backup using docker exec
cd /home/ubuntu/kecamatanSAE && docker compose exec -T db pg_dump -U user dashboard_kecamatan > $BACKUP_DIR/$BACKUP_NAME

if [ $? -eq 0 ]; then
    echo "✅ Backup successful: $BACKUP_NAME"
    # Delete backups older than RETENTION_DAYS
    find $BACKUP_DIR -type f -name "db_backup_*.sql" -mtime +$RETENTION_DAYS -delete
    echo "清理 (Cleanup) older backups done."
else
    echo "❌ Backup failed!"
fi
