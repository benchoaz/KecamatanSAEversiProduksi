import os
import pty
import time
import base64

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
        while time.time() - start < 300: 
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

print("--- Step 1: Git Pull on VPS ---")
run_ssh_cmd("cd /home/ubuntu/KecamatanSAEversiProduksi && git pull origin main")

print("\n--- Step 2: Syncing Specific Files to Container ---")
files_to_sync = [
    'app/app/Http/Controllers/Kecamatan/PelayananController.php',
    'app/app/Models/PublicService.php',
    'app/resources/views/kecamatan/pelayanan/inbox.blade.php',
    'app/resources/views/kecamatan/pelayanan/show.blade.php',
    'app/app/Http/Controllers/PublicServiceController.php',
    'app/resources/views/landing.blade.php',
    'app/routes/kecamatan.php',
    'app/resources/views/kecamatan/pelayanan/visitor/index.blade.php',
    'app/resources/views/kecamatan/settings/profile.blade.php'
]

for f in files_to_sync:
    local_path = f"/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/{f}"
    if not os.path.exists(local_path):
        print(f"File not found locally: {local_path}")
        continue
    
    with open(local_path, 'r') as file:
        content = file.read()
    
    b64_content = base64.b64encode(content.encode()).decode()
    remote_path = f"/home/ubuntu/KecamatanSAEversiProduksi/{f}"
    # Container internal path (assuming /var/www/ is root)
    container_path = f"/var/www/{f.replace('app/', '', 1)}"
    
    print(f"Deploying: {f} -> {container_path}")
    run_ssh_cmd(f"mkdir -p $(dirname {remote_path}) && echo '{b64_content}' | base64 -d > {remote_path}")
    run_ssh_cmd(f"echo '{pw}' | sudo -S docker cp {remote_path} kecamatan-app:{container_path}")

print("\n--- Step 3: Clearing Cache ---")
run_ssh_cmd(f"echo '{pw}' | sudo -S docker exec -i kecamatan-app php artisan cache:clear")
run_ssh_cmd(f"echo '{pw}' | sudo -S docker exec -i kecamatan-app php artisan view:clear")

print("\n--- Deployment Completed Successfully! ---")
