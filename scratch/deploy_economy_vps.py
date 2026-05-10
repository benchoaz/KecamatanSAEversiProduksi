import os
import pty
import time

# VPS Configuration
host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_step(cmd, title):
    print(f"\n>>> RUNNING: {title}...")
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < 120: # 2 min timeout
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                print(chunk.decode(errors='ignore'), end='', flush=True)
                if b"password:" in chunk.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

print("="*50)
print("   KECAMATAN SAE - ECONOMY & UMKM DEPLOYER   ")
print("="*50)

# Step 1: Git Pull
run_step("cd kecamatanSAE && git pull origin main", "Pulling Latest Code")

# Step 2: Database Migration
run_step("cd kecamatanSAE && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan migrate --force", "Updating Database Schema")

# Step 3: Storage Link
run_step("cd kecamatanSAE && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan storage:link", "Ensuring Storage Link")

# Step 4: Clear Cache & Optimize
run_step("cd kecamatanSAE && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan optimize:clear", "Clearing Caches")

# Step 5: Fix Permissions
run_step("cd kecamatanSAE && sudo docker compose -f docker-compose.vps.yml exec -T app chmod -R 775 storage bootstrap/cache && sudo docker compose -f docker-compose.vps.yml exec -T app chown -R www-data:www-data storage", "Setting Permissions")

print("\n" + "="*50)
print("   DEPLOYMENT COMPLETED SUCCESSFULLY!   ")
print("="*50)
