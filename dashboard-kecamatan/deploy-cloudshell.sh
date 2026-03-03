#!/bin/bash
set -e

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== Dashboard Kecamatan - Cloud Shell Deployment ===${NC}\n"

# Check if git is available
if ! command -v git &> /dev/null; then
    echo -e "${RED}Git is not installed. Installing...${NC}"
    sudo apt-get update && sudo apt-get install -y git
fi

# Check if docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Docker is not running!${NC}"
    echo "Starting Docker..."
    sudo service docker start || sudo systemctl start docker
    sleep 5
fi

# Define project directory
PROJECT_DIR="${HOME}/dashboard-kecamatan"
REPO_URL="${1:-https://github.com/YOUR_USERNAME/dashboard-kecamatan.git}"

echo -e "${YELLOW}Step 1: Cloning Repository${NC}"
if [ ! -d "$PROJECT_DIR" ]; then
    echo "Cloning from: $REPO_URL"
    git clone "$REPO_URL" "$PROJECT_DIR"
else
    echo "Directory exists, pulling latest changes..."
    cd "$PROJECT_DIR"
    git pull origin main || git pull origin master
fi

cd "$PROJECT_DIR"

echo -e "\n${YELLOW}Step 2: Setting Up Environment${NC}"
if [ ! -f ".env" ]; then
    echo "Creating .env from example..."
    if [ -f ".env.example" ]; then
        cp .env.example .env
    else
        echo -e "${YELLOW}Warning: No .env.example found, using defaults${NC}"
        cat > .env << 'ENVFILE'
APP_NAME="Kecamatan SAE"
APP_ENV=local
APP_KEY=base64:W5WQ9EUDLKZCRaMX33HayaMsn7KCGMRkR6YP89Q8sWk=
APP_DEBUG=true
APP_URL=http://localhost:8000
PUBLIC_BASE_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=dashboard_kecamatan
DB_USERNAME=root
DB_PASSWORD=root

LOG_CHANNEL=stderr
LOG_LEVEL=debug
CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=cookie
SESSION_LIFETIME=15

BROADCAST_DRIVER=log
FILESYSTEM_DISK=local
MEMCACHED_HOST=127.0.0.1
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

WHATSAPP_API_TOKEN=62a72516dd1b418499d9dd22075ccfa0
WAHA_API_URL=http://waha-kecamatan:3000
WAHA_API_KEY=waha_secret_key_2024
WAHA_SESSION=default
DASHBOARD_API_TOKEN=fJJCz33U8jkHIKXEhTpv91GZJz97VGPHmItYlvxPNUi8obg05BYsZCh5TmfAznma
N8N_REPLY_WEBHOOK_URL=http://dashboard-n8n:5678/webhook/whatsapp-primary
ENVFILE
    fi
else
    echo "Using existing .env file"
fi

echo -e "\n${YELLOW}Step 3: Building and Starting Services${NC}"
echo "This may take 5-10 minutes on first run..."
docker-compose down -v 2>/dev/null || true
sleep 3
docker-compose pull
sleep 3
docker-compose up -d

echo -e "\n${YELLOW}Step 4: Waiting for Services to Be Ready${NC}"
MAX_ATTEMPTS=60
ATTEMPT=0

# Wait for MySQL
echo "Waiting for MySQL..."
while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    if docker-compose exec -T db mysqladmin ping -h localhost -u root -proot &> /dev/null; then
        echo -e "${GREEN}MySQL is ready!${NC}"
        break
    fi
    ATTEMPT=$((ATTEMPT+1))
    echo "Attempt $ATTEMPT/$MAX_ATTEMPTS..."
    sleep 2
done

if [ $ATTEMPT -eq $MAX_ATTEMPTS ]; then
    echo -e "${RED}MySQL failed to start. Check logs with: docker-compose logs db${NC}"
fi

# Wait for PHP-FPM
echo "Waiting for PHP-FPM..."
ATTEMPT=0
while [ $ATTEMPT -lt $MAX_ATTEMPTS ]; do
    if docker-compose exec -T app php-fpm -t &> /dev/null; then
        echo -e "${GREEN}PHP-FPM is ready!${NC}"
        break
    fi
    ATTEMPT=$((ATTEMPT+1))
    echo "Attempt $ATTEMPT/$MAX_ATTEMPTS..."
    sleep 2
done

if [ $ATTEMPT -eq $MAX_ATTEMPTS ]; then
    echo -e "${RED}PHP-FPM failed to start. Check logs with: docker-compose logs app${NC}"
fi

echo -e "\n${YELLOW}Step 5: Running Database Migrations${NC}"
docker-compose exec -T app php artisan config:cache || true
docker-compose exec -T app php artisan migrate --force || true
docker-compose exec -T app php artisan db:seed || true

echo -e "\n${YELLOW}Step 6: Verifying Services${NC}"
docker-compose ps

echo -e "\n${GREEN}=== Deployment Complete! ===${NC}\n"

echo -e "${BLUE}Access Your Application:${NC}"
echo -e "  Main App:   http://localhost:8000"
echo -e "  n8n:        http://localhost:5679"
echo -e "  WAHA:       http://localhost:3000"
echo -e "  Database:   localhost:3307\n"

echo -e "${BLUE}Useful Commands:${NC}"
echo -e "  View logs:     docker-compose logs -f"
echo -e "  Restart:       docker-compose restart"
echo -e "  Stop services: docker-compose stop"
echo -e "  Stop all:      docker-compose down\n"

echo -e "${YELLOW}For Cloud Shell Web Preview:${NC}"
echo -e "  1. Click 'Web Preview' button (top-right)"
echo -e "  2. Select 'Preview on port 8000'"
echo -e "  3. Your app opens in new tab\n"

echo -e "${BLUE}Project Location:${NC} $PROJECT_DIR"
echo -e "${BLUE}Documentation:${NC} $PROJECT_DIR/CLOUD_SHELL_DEPLOY.md"
