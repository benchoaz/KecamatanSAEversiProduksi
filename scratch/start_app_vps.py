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
        while time.time() - start < 600: # 10 min timeout for build
            try:
                chunk = os.read(fd, 4096)
                if not chunk: break
                output += chunk
                print(chunk.decode(errors='ignore'), end='', flush=True)
                if b"password:" in chunk.lower() and not password_sent:
                    os.write(fd, (pw + "\n").encode())
                    password_sent = True
            except:
                break
        return output.decode(errors='ignore')

print("--- STEP 6: Add user to docker group ---")
run_step("sudo usermod -aG docker ubuntu")

print("--- STEP 7: Build & Start Docker ---")
# Using sudo docker for now because group change needs relogin
run_step("cd kecamatanSAE && sudo docker compose -f docker-compose.vps.yml up -d --build")

print("--- STEP 8: Run Artisan Commands ---")
artisan_cmds = " && ".join([
    "sudo docker compose -f docker-compose.vps.yml exec -T app php artisan migrate --force",
    "sudo docker compose -f docker-compose.vps.yml exec -T app php artisan db:seed --class=NavigationSeeder --force",
    "sudo docker compose -f docker-compose.vps.yml exec -T app php artisan desa:sync-demografi",
    "sudo docker compose -f docker-compose.vps.yml exec -T app php artisan optimize:clear"
])
run_step(f"cd kecamatanSAE && {artisan_cmds}")
