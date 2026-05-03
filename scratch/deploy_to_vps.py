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
        while time.time() - start < 60:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

# 1. Pull changes from GitHub on VPS
print("Pulling changes from GitHub on VPS...")
pull_output = run_ssh_cmd("cd /home/ubuntu/kecamatanSAE && git pull origin main")
print(pull_output)

# 2. Run migrations on VPS
print("Running migrations on VPS...")
migrate_output = run_ssh_cmd("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan migrate --force")
print(migrate_output)

# 3. Clear cache on VPS
print("Clearing cache on VPS...")
cache_output = run_ssh_cmd("sudo -S docker compose -f /home/ubuntu/kecamatanSAE/docker-compose.vps.yml exec -T app php artisan optimize:clear")
print(cache_output)
