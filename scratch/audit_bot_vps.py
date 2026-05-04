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

print("--- AUDITING BOT AI STATUS ON VPS ---")

print("\n1. Checking database for AI status...")
# Using tinker to check AppProfile
run_step(f"cd {project_dir} && sudo docker compose -f {compose_file} exec -T app php artisan tinker --execute='print_r(App\Models\AppProfile::first([\"is_ai_active\", \"ai_provider\", \"google_api_key\", \"openai_api_key\"])?->toArray())'")

print("\n2. Checking latest error logs on VPS...")
run_step(f"cd {project_dir} && sudo docker compose -f {compose_file} exec -T app tail -n 50 storage/logs/laravel.log")

print("\n--- AUDIT COMPLETED ---")
