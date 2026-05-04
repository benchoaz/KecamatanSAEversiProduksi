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
        while time.time() - start < 300: # 5 minutes for git pull
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                print(chunk.decode(errors='ignore'), end='', flush=True)
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

print("--- Updating VPS via Git Pull ---")
run_ssh_cmd("cd /home/ubuntu/KecamatanSAEversiProduksi && git pull origin main")

print("\n--- Clearing Cache on Container ---")
run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan cache:clear")
run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan view:clear")
run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan config:clear")

print("\n--- Syncing files to Container (since they might be outside 'app' mount or to ensure parity) ---")
# Actually if the docker-compose mounts 'app' directory, git pull is enough.
# But looking at deploy_full_sync.py, it does 'docker cp'.
# This suggests the 'app' directory might NOT be a live mount or needs a push.

files_to_sync = [
    'app/app/Http/Controllers/Kecamatan/UserManagementController.php',
    'app/resources/views/kecamatan/users/create.blade.php',
    'app/resources/views/kecamatan/users/edit.blade.php'
]

import base64
for f in files_to_sync:
    local_path = f"/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/{f}"
    if not os.path.exists(local_path):
        continue
    with open(local_path, 'r') as file:
        content = file.read()
    b64_content = base64.b64encode(content.encode()).decode()
    remote_path = f"/home/ubuntu/KecamatanSAEversiProduksi/{f}"
    container_path = f"/var/www/{f.replace('app/', '', 1)}"
    run_ssh_cmd(f"mkdir -p $(dirname {remote_path}) && echo '{b64_content}' | base64 -d > {remote_path}")
    run_ssh_cmd(f"echo '{pw}' | sudo -S docker cp {remote_path} kecamatan-app:{container_path}")

print("\n--- Deployment Completed! ---")
