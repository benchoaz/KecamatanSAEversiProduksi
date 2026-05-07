import os
import pty
import time
import base64

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

files_to_sync = [
    'app/app/Models/AppProfile.php',
    'app/app/Http/Controllers/ApplicationProfileController.php',
    'app/app/Providers/AppServiceProvider.php',
    'app/resources/views/kecamatan/settings/profile.blade.php',
    'app/database/migrations/2026_05_07_184145_add_backup_settings_to_app_profiles_table.php',
    'app/config/backup.php',
    'app/config/filesystems.php'
]

def run_ssh_cmd(cmd):
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

print("--- Syncing Backup Feature to VPS ---")

for f in files_to_sync:
    local_path = f"/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/{f}"
    if not os.path.exists(local_path):
        print(f"File {f} not found locally. Skipping.")
        continue
        
    with open(local_path, 'r') as file:
        content = file.read()
    
    b64_content = base64.b64encode(content.encode()).decode()
    
    remote_path = f"/home/ubuntu/KecamatanSAEversiProduksi/{f}"
    # Target path inside container (removing 'app/' prefix)
    container_path = f"/var/www/{f.replace('app/', '', 1)}"
    
    print(f"Syncing {f}...")
    # 1. Create remote dir and write file to VPS host
    run_ssh_cmd(f"mkdir -p $(dirname {remote_path}) && echo '{b64_content}' | base64 -d > {remote_path}")
    # 2. Copy to container
    run_ssh_cmd(f"echo '{pw}' | sudo -S docker cp {remote_path} kecamatan-app:{container_path}")
    # 3. Ensure permissions inside container
    run_ssh_cmd(f"echo '{pw}' | sudo -S docker exec -u root kecamatan-app chown www-data:www-data {container_path}")

# Run migrations
print("Running migrations on VPS...")
run_ssh_cmd("echo 'nebula-57@-ocean' | sudo -S docker exec kecamatan-app php artisan migrate --force")

# Clear cache
print("Clearing Laravel cache...")
run_ssh_cmd("echo 'nebula-57@-ocean' | sudo -S docker exec kecamatan-app php artisan cache:clear")
run_ssh_cmd("echo 'nebula-57@-ocean' | sudo -S docker exec kecamatan-app php artisan view:clear")
run_ssh_cmd("echo 'nebula-57@-ocean' | sudo -S docker exec kecamatan-app php artisan config:clear")

print("--- Sync Completed! ---")
