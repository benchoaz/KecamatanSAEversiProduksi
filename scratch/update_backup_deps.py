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

# Read local composer.json
with open('/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/app/composer.json', 'r') as f:
    composer_content = f.read()

b64_composer = base64.b64encode(composer_content.encode()).decode()

print("Updating composer.json on VPS...")
run_ssh_cmd(f"echo '{b64_composer}' | base64 -d > /home/ubuntu/KecamatanSAEversiProduksi/app/composer.json")
run_ssh_cmd(f"echo '{pw}' | sudo -S docker cp /home/ubuntu/KecamatanSAEversiProduksi/app/composer.json kecamatan-app:/var/www/composer.json")

print("Installing backup dependencies on VPS (this may take a few minutes)...")
# Using --no-dev to save memory
run_ssh_cmd(f"echo '{pw}' | sudo -S docker exec kecamatan-app composer update spatie/laravel-backup masbug/flysystem-google-drive-ext google/apiclient --no-dev")

print("Discovering packages...")
run_ssh_cmd(f"echo '{pw}' | sudo -S docker exec kecamatan-app php artisan package:discover")

print("--- Dependency Update Completed! ---")
