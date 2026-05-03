import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_ssh_cmd(cmd):
    print(f"Executing: {cmd}")
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        start = time.time()
        while time.time() - start < 120: # 2 minutes timeout
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

# 1. Pull changes
print("--- Pulling ---")
print(run_ssh_cmd("cd /home/ubuntu/kecamatanSAE && git pull origin main"))

# 2. Migrate
print("--- Migrating ---")
print(run_ssh_cmd("echo 'nebula-57@-ocean' | sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan migrate --force"))

# 3. Optimize
print("--- Optimizing ---")
print(run_ssh_cmd("echo 'nebula-57@-ocean' | sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan optimize:clear"))

# 4. Final check: verify AiMemory class exists on VPS
print("--- Verification ---")
print(run_ssh_cmd("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan tinker --execute=\"echo 'AiMemory exists: ' . class_exists('App\Models\AiMemory');\""))
