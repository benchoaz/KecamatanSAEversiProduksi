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
        while time.time() - start < 600: # 10 minutes
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower():
                    os.write(fd, (pw + "\n").encode())
            except:
                break
        return output.decode(errors='ignore')

f = 'app/app/Console/Commands/SyncTatadesaDemografi.php'
local_path = f"/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/{f}"
with open(local_path, 'r') as file:
    content = file.read()
b64_content = base64.b64encode(content.encode()).decode()
remote_path = f"/home/ubuntu/KecamatanSAEversiProduksi/{f}"
container_path = f"/var/www/{f.replace('app/', '', 1)}"

print(f"Syncing {f}...")
run_ssh_cmd(f"mkdir -p $(dirname {remote_path}) && echo '{b64_content}' | base64 -d > {remote_path}")
run_ssh_cmd(f"sudo -S docker cp {remote_path} kecamatan-app:{container_path}")

print("--- Running desa:sync-demografi on VPS ---")
result = run_ssh_cmd("sudo -S docker exec kecamatan-app php artisan desa:sync-demografi")
print(result)

print("--- Scrape Completed! ---")
