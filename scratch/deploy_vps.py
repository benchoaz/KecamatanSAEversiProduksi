import os
import pty
import subprocess
import time

def run_ssh_command(host, user, password, command):
    pid, fd = pty.fork()
    if pid == 0:
        # Child process
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', command])
    else:
        # Parent process
        output = b""
        password_sent = False
        start_time = time.time()
        
        while time.time() - start_time < 60:
            try:
                data = os.read(fd, 1024)
                if not data:
                    break
                output += data
                print(data.decode(errors='ignore'), end='', flush=True)
                
                if b"password:" in data.lower() and not password_sent:
                    os.write(fd, (password + "\n").encode())
                    password_sent = True
            except OSError:
                break
        
        return output.decode(errors='ignore')

# Deployment steps
host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

print("--- STEP 0: Locating project directory ---")
search_res = run_ssh_command(host, user, pw, "find / -maxdepth 4 -name 'KecamatanSAEversiProduksi' 2>/dev/null | head -n 1")
project_dir = search_res.strip().split('\n')[-1].strip()

if not project_dir:
    print("Project directory not found! Trying home...")
    project_dir = "/home/ubuntu/KecamatanSAEversiProduksi"

print(f"Targeting: {project_dir}")

print("\n--- STEP 2: Pulling changes and Deploying ---")
deploy_cmd = f"cd {project_dir} && git pull origin main && sudo docker compose -f docker-compose.vps.yml up -d --build"
run_ssh_command(host, user, pw, deploy_cmd)

print("\n--- STEP 3: Running Migrations and Sync ---")
sync_cmd = f"cd {project_dir} && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan migrate --force && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan db:seed --class=NavigationSeeder --force && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan desa:sync-demografi && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan optimize:clear"
run_ssh_command(host, user, pw, sync_cmd)
