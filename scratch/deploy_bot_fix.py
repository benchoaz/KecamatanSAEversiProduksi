import os
import pty
import time
import sys

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
        while time.time() - start < 300: # 5 min timeout
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
    # Use temporary file to avoid pipe issues
    temp_vps_path = "/tmp/deploy_temp.php"
    
    # We use a trick to send content: cat with EOF
    # But for safety with large files, we'll do it in chunks or use a single heredoc
    # Let's use heredoc but escaped
    cmd = f"cat << 'EOF' > {vps_path}\n{content}\nEOF"
    run_step(cmd)

# Files to deploy
files = [
    ('/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/app/app/Services/WhatsApp/AiHandler.php', '~/kecamatanSAE/app/app/Services/WhatsApp/AiHandler.php'),
    ('/home/beni/ProjectkuKecamatanSAEKab/KecamatanSAE/KecamatanSAEversiProduksi/app/app/Http/Controllers/Api/AiAssistantController.php', '~/kecamatanSAE/app/app/Http/Controllers/Api/AiAssistantController.php')
]

for local, vps in files:
    deploy_file(local, vps)

print("--- CLEARING CACHE ---")
run_step("cd ~/kecamatanSAE && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan optimize:clear")

print("--- DONE ---")
