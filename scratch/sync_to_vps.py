import os
import pty
import time
import sys

host = '43.134.166.153'
user = 'ubuntu'
pw = 'nebula-57@-ocean'

files_to_sync = [
    'app/app/Services/WhatsApp/AiHandler.php',
    'app/app/Http/Controllers/Api/AiAssistantController.php'
]

def run_scp(local_path, remote_path):
    print(f"Syncing {local_path} -> {remote_path}...")
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/scp', ['scp', '-o', 'StrictHostKeyChecking=no', local_path, f'{user}@{host}:{remote_path}'])
    else:
        output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < 30:
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

def run_ssh(cmd):
    print(f"Executing: {cmd}...")
    pid, fd = pty.fork()
    if pid == 0:
        os.execv('/usr/bin/ssh', ['ssh', '-o', 'StrictHostKeyChecking=no', f'{user}@{host}', cmd])
    else:
        output = b""
        password_sent = False
        start = time.time()
        while time.time() - start < 30:
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

for f in files_to_sync:
    remote_path = f"~/kecamatanSAE/{f}"
    run_scp(f, remote_path)

# Clear cache on VPS
run_ssh("cd kecamatanSAE && sudo docker compose -f docker-compose.vps.yml exec -T app php artisan optimize:clear")

print("--- SYNC COMPLETED ---")
