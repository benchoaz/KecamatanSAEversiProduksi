import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

def run_step(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < 300:
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                if b"password:" in chunk.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

def deploy_file(local_path, vps_path):
    with open(local_path, 'r') as f:
        content = f.read()
    print(f"--- DEPLOYING {os.path.basename(local_path)} ---")
    cmd = f"cat << 'EOF' > {vps_path}\n{content}\nEOF"
    run_step(cmd)

files = [
    ('/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/app/resources/views/desa/administrasi/personil/index.blade.php', '~/kecamatanSAE/app/resources/views/desa/administrasi/personil/index.blade.php'),
    ('/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/app/resources/views/desa/administrasi/lembaga/index.blade.php', '~/kecamatanSAE/app/resources/views/desa/administrasi/lembaga/index.blade.php')
]

for local, vps in files:
    deploy_file(local, vps)

print("--- CLEARING CACHE ---")
run_step("cd ~/kecamatanSAE && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan view:clear")

print("--- DONE ---")
