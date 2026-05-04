import os
import pty
import time

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'
project_dir = 'kecamatanSAE'
compose_file = 'docker-compose.vps.yml'

def run_step(cmd):
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-t', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < 300: # 5 min timeout
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

print("--- DEPLOYING SILTAP UPDATES TO VPS ---")

print("\n1. Pulling latest code from GitHub...")
run_step(f"cd {project_dir} && git pull origin main")

print("\n2. Running database migrations...")
run_step(f"cd {project_dir} && sudo docker compose -f {compose_file} exec -T app php artisan migrate --force")

print("\n3. Clearing application cache...")
run_step(f"cd {project_dir} && sudo docker compose -f {compose_file} exec -T app php artisan optimize:clear")

print("\n--- DEPLOYMENT COMPLETED ---")
