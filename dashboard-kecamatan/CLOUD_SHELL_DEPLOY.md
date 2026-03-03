# Google Cloud Shell Deployment Guide

## Prerequisites
- Google Cloud account with a project
- Git configured (gcloud handles this automatically)
- Docker and docker-compose available in Cloud Shell

## Quick Start

### Step 1: Clone Your Repository
```bash
cd ~
git clone https://github.com/YOUR_USERNAME/dashboard-kecamatan.git
cd dashboard-kecamatan
```

### Step 2: Set Up Environment
```bash
# Copy and customize the environment file
cp .env.example .env

# OR use the existing .env (already configured)
# Note: Adjust database credentials if needed
```

### Step 3: Start Services with Simplified Compose
```bash
# For Cloud Shell: use the cloud-shell-compose.yml (lighter version)
docker-compose -f docker-compose.yml up -d
```

### Step 4: View Running Services
```bash
docker-compose ps
```

### Step 5: Access Your Application
- **Main App**: http://localhost:8000 (via Cloud Shell's web preview)
- **n8n**: http://localhost:5679
- **WAHA**: http://localhost:3000
- **Database**: localhost:3307

### Step 6: Database Migration (First Time Only)
```bash
# Wait for MySQL to be ready (30-60 seconds)
docker-compose exec -T app php artisan migrate
docker-compose exec -T app php artisan db:seed
```

### Step 7: View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f db
```

## Useful Commands

### Stop Services
```bash
docker-compose stop
```

### Restart Services
```bash
docker-compose restart
```

### Stop and Remove Everything
```bash
docker-compose down
```

### Rebuild Images
```bash
docker-compose build --no-cache
```

### Access Application Shell
```bash
docker-compose exec app bash
```

### Run Artisan Commands
```bash
docker-compose exec app php artisan tinker
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
```

## Troubleshooting

### Port Already in Use
```bash
# Find process using port 8000
sudo lsof -i :8000

# Kill the process
sudo kill -9 <PID>
```

### Database Connection Issues
```bash
# Check MySQL service status
docker-compose logs db

# Verify connectivity
docker-compose exec app ping db
```

### PHP-FPM Not Responding
```bash
# Check app logs
docker-compose logs app

# Restart app service
docker-compose restart app
```

### Out of Memory
```bash
# Check resource usage
docker stats

# Increase swap (if needed)
docker-compose down
# Adjust Docker Desktop memory settings
```

## Web Preview in Cloud Shell

1. Click the **Web Preview** button (top-right corner)
2. Select **Preview on port 8000**
3. Your Laravel application will open in a new tab

For n8n and WAHA dashboards, use the full URL:
- `http://localhost:5679` (n8n)
- `http://localhost:3000` (WAHA)

## Environment Variables

Key variables in `.env`:
- `DB_HOST=db` (use container name in network)
- `DB_USERNAME=root`
- `DB_PASSWORD=root`
- `WAHA_API_URL=http://waha-kecamatan:3000`
- `APP_URL=https://babette-nonslanderous-randi.ngrok-free.dev` (update as needed)

## Persistent Storage

Cloud Shell provides:
- **5 GB** persistent home directory (~)
- **Data persists** between sessions (30 days of inactivity auto-cleanup)

Data locations:
- Source code: `~/dashboard-kecamatan/`
- Docker volumes: `/tmp/docker-volumes/` (limited - not persistent)

## Production Deployment

For production, consider:
1. **Google Cloud Run** - Serverless container deployment
2. **Google Compute Engine** - Full VM with persistent storage
3. **Google Kubernetes Engine (GKE)** - For scalable deployments

Contact DevOps team for production setup.

## Support

For issues:
1. Check logs: `docker-compose logs`
2. Verify connectivity: `docker network inspect dashboard-kecamatan_app-network`
3. Restart services: `docker-compose restart`
