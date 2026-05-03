import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_ssh_cmd(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        start = time.time()
        while time.time() - start < 120:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

# 1. Copy Files into Container
print("--- Copying Files into Container ---")
files = [
    ('app/database/migrations/2026_05_03_174500_create_ai_memories_table.php', '/var/www/database/migrations/'),
    ('app/app/Models/AiMemory.php', '/var/www/app/Models/'),
    ('app/config/app.php', '/var/www/config/app.php')
]

for src, dest in files:
    full_src = f"/home/ubuntu/kecamatanSAE/{src}"
    cmd = f"sudo -S docker cp {full_src} kecamatan-app:{dest}"
    print(f"Copying {src}...")
    print(run_ssh_cmd(cmd))

# 2. Run Migration
print("--- Running Migration ---")
print(run_ssh_cmd("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan migrate --force"))

# 3. Optimize
print("--- Optimizing ---")
print(run_ssh_cmd("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan optimize:clear"))

# 4. Final Verification
print("--- Verification ---")
print(run_ssh_cmd("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan tinker --execute=\"echo 'Table: ' . Schema::hasTable('ai_memories') . ' | Model: ' . class_exists('App\\\\Models\\\\AiMemory');\""))
