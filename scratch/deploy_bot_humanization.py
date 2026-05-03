import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

files_to_sync = [
    'app/app/Http/Controllers/Api/WhatsappController.php',
    'app/app/Services/WhatsApp/IntentHandler.php',
    'app/app/Services/WhatsApp/ComplaintHandler.php',
    'app/app/Services/WhatsApp/AiHandler.php'
]

def run_ssh_cmd(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        start = time.time()
        while time.time() - start < 30:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

def upload_file(local_path, remote_path):
    print(f"Uploading {local_path}...")
    os.system(f"sshpass -p '{pw}' scp -o StrictHostKeyChecking=no {local_path} {user}@{host}:{remote_path}")

print("--- Starting Bot Humanization Sync ---")

for f in files_to_sync:
    local = f"/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/{f}"
    remote = f"/home/ubuntu/KecamatanSAEversiProduksi/{f}"
    upload_file(local, remote)
    
    # Copy from host to container
    container_path = f"/var/www/{f.replace('app/', '', 1)}"
    print(f"Syncing to container: {container_path}")
    run_ssh_cmd(f"sudo -S docker cp /home/ubuntu/KecamatanSAEversiProduksi/{f} kecamatan-app:{container_path}")

# Clear cache
print("Clearing Laravel cache...")
run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan cache:clear")
run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan config:clear")

print("--- Sync Completed! ---")
