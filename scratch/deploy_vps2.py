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
        while time.time() - start < 60: # 1 min timeout
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

print("--- FIXING PERMISSIONS AND DEPLOYING AI TO VPS ---")
run_step("cd kecamatanSAE && sudo chown -R ubuntu:ubuntu . && git pull origin main && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan optimize:clear")
